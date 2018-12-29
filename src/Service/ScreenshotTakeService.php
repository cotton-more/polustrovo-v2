<?php

namespace Polustrovo\Service;

use BrowshotAPI\BrowshotAPI;
use BrowshotAPI\Message\ScreenshotCreateRequest;
use BrowshotAPI\Message\ScreenshotResponse;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Repository\ScreenshotRepository;
use Psr\Log\LoggerInterface;

class ScreenshotTakeService
{
    /** @var BrowshotAPI */
    private $browshotApi;

    /** @var ScreenshotRepository */
    private $screenshotRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        BrowshotAPI $browshotApi,
        ScreenshotRepository $screenshotRepository,
        LoggerInterface $logger
    ) {
        $this->browshotApi = $browshotApi;
        $this->screenshotRepository = $screenshotRepository;
        $this->logger = $logger;
    }

    /**
     * @param ScreenshotCreateRequest $request
     */
    public function execute(ScreenshotCreateRequest $request)
    {
        $this->logger->debug('start');

        $response = $this->browshotApi->createScreenshot($request);

        $screenshotEntity = $this->getScreenshotEntity($response, $request);

        $result = $this->screenshotRepository->create($screenshotEntity);

        $this->logger->debug('end', [
            'result' => $result->id(),
        ]);
    }

    /**
     * @param ScreenshotResponse $response
     * @param ScreenshotCreateRequest $request
     * @return Screenshot
     */
    private function getScreenshotEntity(ScreenshotResponse $response, ScreenshotCreateRequest $request)
    {
        $screenshot = (new Screenshot())->with([
            'browshotId' => $response->getId() ?? 0,
            'status' => $response->getStatus(),
            'errorMessage' => $response->getError(),
            'url' => $request->getUrl(),
            'browshotInstance' => $request->getInstanceId(),
        ]);

        return $screenshot;
    }
}
