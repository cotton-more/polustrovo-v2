<?php

declare(strict_types=1);

namespace Polustrovo\Service;

use BrowshotAPI\BrowshotAPI;
use BrowshotAPI\Message\ScreenshotInfoRequest;
use BrowshotAPI\Message\ScreenshotResponse;
use BrowshotAPI\Message\ScreenshotStatus;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\StreamWrapper;
use GuzzleHttp\RequestOptions;
use Polustrovo\Exception\ScreenshotDownloadException;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Repository\ScreenshotRepository;
use Psr\Log\LoggerInterface;

class ScreenshotDownloadService
{
    /** @var ScreenshotRepository */
    private $screenshotRepository;

    /** @var BrowshotAPI */
    private $browshotClient;

    /** @var ClientInterface */
    private $client;

    /** @var ScreenshotSendService */
    private $screenshotSendService;

    /** @var LoggerInterface */
    private $logger;

    /**
     * ScreenshotDownloadService constructor.
     * @param ScreenshotRepository $screenshotRepository
     * @param BrowshotAPI $browshotClient
     * @param ClientInterface $client
     * @param ScreenshotSendService $screenshotSendService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScreenshotRepository $screenshotRepository,
        BrowshotAPI $browshotClient,
        ClientInterface $client,
        ScreenshotSendService $screenshotSendService,
        LoggerInterface $logger
    ) {
        $this->screenshotRepository = $screenshotRepository;
        $this->browshotClient = $browshotClient;
        $this->client = $client;
        $this->screenshotSendService = $screenshotSendService;
        $this->logger = $logger;
    }

    public function execute()
    {
        $screenshotList = $this->screenshotRepository->getQueued();

        $this->logger->debug('screenshots to download', [
            'count' => count($screenshotList),
        ]);

        foreach ($screenshotList as $screenshot) {
            $this->logger->debug('downloading screenshot', [
                'browshotId' => $screenshot->browshotId(),
            ]);

            $request = new ScreenshotInfoRequest();
            $request->setId($screenshot->browshotId());

            $screenshotInfo = $this->browshotClient->getScreenshotInfo($request);
            $this->logger->debug('screenshot info', json_decode($screenshotInfo->serializeToJsonString(), true));

            if ($screenshotInfo->getStatus() !== ScreenshotStatus::FINISHED) {
                /** @var Screenshot $screenshot */
                $screenshot = $screenshot->with([
                    'status' => $screenshotInfo->getStatus(),
                ]);

                $errorMessage = $screenshotInfo->getError() ?? $screenshot->errorMessage();

                $this->screenshotRepository->updateStatus($screenshot, $errorMessage);
                continue;
            }

            try {
                $this->logger->debug('try to download screenshot');

                $result = $this->download($screenshotInfo);
            } catch (ScreenshotDownloadException $exception) {
                /** @var Screenshot $screenshot */
                $screenshot = $screenshot->with([
                    'status' => ScreenshotStatus::ERROR,
                ]);
                $this->screenshotRepository->updateStatus($screenshot, $exception->getMessage());

                continue;
            }

            if ($result->getStatusCode() === 200) {
                $filename = $screenshot->browshotId().'.png';

                $filePath = getenv('SCREENSHOTS_DIR').'/'.$filename;
                $fileSize = stream_copy_to_stream(
                    StreamWrapper::getResource($result->getBody()),
                    fopen($filePath, 'w')
                );

                $this->logger->debug('saving screenshot to file', [
                    'filePath' => $filePath,
                    'result' => $fileSize,
                ]);

                /** @var Screenshot $screenshot */
                $screenshot = $screenshot->with([
                    'status' => $screenshotInfo->getStatus(),
                    'filename' => $filename,
                    'fileSize' => $fileSize,
                ]);

                $this->screenshotRepository->saveFileAndStatus($screenshot);
                $this->screenshotSendService->publish($screenshot);
            }
        }
    }

    /**
     * @param ScreenshotResponse $screenshotInfo
     * @return \Psr\Http\Message\ResponseInterface
     * @throws ScreenshotDownloadException
     */
    private function download(ScreenshotResponse $screenshotInfo)
    {
        $screenshotUrl = $screenshotInfo->getScreenshotUrl();

        $this->logger->debug('downloading', [
            'screenshotUrl' => $screenshotUrl,
        ]);

        try {
            $response = $this->client->request('GET', $screenshotUrl, [
                RequestOptions::SINK => tmpfile(),
            ]);

            return $response;
        } catch (GuzzleException $e) {
            /** @noinspection PhpUndefinedMethodInspection */
            $message = 'Failed to download.';

            $this->logger->warning($message.' '.$e->getMessage(), [
                'responseCode' => $e->getCode(),
            ]);

            throw new ScreenshotDownloadException($message, ScreenshotDownloadException::DOWNLOAD_FAILED, $e);
        }
    }
}