<?php

declare(strict_types=1);

namespace spec\Polustrovo\Service;

use BrowshotAPI\BrowshotAPI;
use BrowshotAPI\Message\ScreenshotInfoRequest;
use BrowshotAPI\Message\ScreenshotResponse;
use BrowshotAPI\Message\ScreenshotStatus;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Repository\ScreenshotRepository;
use Polustrovo\Service\ScreenshotSendService;
use Polustrovo\Service\ScreenshotDownloadService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class ScreenshotDownloadServiceSpec extends ObjectBehavior
{
    public function let(
        ScreenshotRepository $screenshotRepository,
        BrowshotAPI $browshotApi,
        ClientInterface $client,
        ScreenshotSendService $screenshotSendService,
        LoggerInterface $logger
    ) {
        putenv('SCREENSHOTS_DIR='.sys_get_temp_dir());

        $this->beConstructedWith(...\func_get_args());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ScreenshotDownloadService::class);
    }

    public function it_updates_status_for_unfinished_screenshot(
        ScreenshotRepository $screenshotRepository,
        BrowshotAPI $browshotClient,
        ClientInterface $client,
        ScreenshotSendService $screenshotSendService,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($screenshotRepository, $browshotClient, $client, $screenshotSendService, $logger);

        $screenshot = Screenshot::create([
            'screenshotId' => 'uuid-1',
            'browshotId' => 1234,
        ]);

        $screenshotRepository->getQueued()
            ->shouldBeCalled()
            ->willReturn([$screenshot])
        ;

        /** @noinspection PhpParamsInspection */
        $browshotClient->getScreenshotInfo(Argument::type(ScreenshotInfoRequest::class))
            ->shouldBeCalled()
            ->willReturn(
                (new ScreenshotResponse())->setStatus(ScreenshotStatus::IN_PROCESS)
            )
        ;

        /** @noinspection PhpParamsInspection */
        $screenshotRepository->updateStatus(Argument::type(Screenshot::class), '')->shouldBeCalled();

        $this->execute();
    }

    public function it_updates_status_for_screenshot_which_failed_to_download(
        ScreenshotRepository $screenshotRepository,
        BrowshotAPI $browshotClient,
        ClientInterface $client,
        ScreenshotSendService $screenshotSendService,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($screenshotRepository, $browshotClient, $client, $screenshotSendService, $logger);

        $screenshot = Screenshot::create([
            'screenshotId' => 'uuid-1',
            'browshotId' => 1234,
        ]);

        $screenshotRepository->getQueued()
            ->shouldBeCalled()
            ->willReturn([$screenshot])
        ;

        /** @noinspection PhpParamsInspection */
        $browshotClient->getScreenshotInfo(Argument::type(ScreenshotInfoRequest::class))
            ->shouldBeCalled()
            ->willReturn(
                (new ScreenshotResponse())
                    ->setStatus(ScreenshotStatus::FINISHED)
                    ->setScreenshotUrl('http://some-screenshot.url')
            )
        ;

        $client->request('GET', 'http://some-screenshot.url', Argument::any())
            ->willThrow(new TransferException)
        ;

        /** @noinspection PhpParamsInspection */
        $screenshotRepository->updateStatus(Argument::type(Screenshot::class), 'Failed to download.')
            ->shouldBeCalled()
        ;

        $this->execute();
    }

    public function it_saves_and_publish_downloaded_file(
        ScreenshotRepository $screenshotRepository,
        BrowshotAPI $browshotClient,
        ClientInterface $client,
        ScreenshotSendService $screenshotSendService,
        LoggerInterface $logger,
        ResponseInterface $downloadResult,
        StreamInterface $stream
    ) {
        $this->beConstructedWith($screenshotRepository, $browshotClient, $client, $screenshotSendService, $logger);

        $screenshot = Screenshot::create([
            'screenshotId' => 'uuid-1',
            'browshotId' => 1234,
        ]);

        $screenshotRepository->getQueued()
            ->shouldBeCalled()
            ->willReturn([$screenshot])
        ;

        /** @noinspection PhpParamsInspection */
        $browshotClient->getScreenshotInfo(Argument::type(ScreenshotInfoRequest::class))
            ->shouldBeCalled()
            ->willReturn(
                (new ScreenshotResponse())
                    ->setStatus(ScreenshotStatus::FINISHED)
                    ->setScreenshotUrl('http://some-screenshot.url')
            )
        ;

        $downloadResult->getStatusCode()->willReturn(200);

        $stream->isReadable()->willReturn(true);
        $stream->isWritable()->willReturn(false);
        $stream->getSize()->willReturn(100);
        $stream->read(8192)->shouldBeCalled();
        $stream->eof()->shouldBeCalled();

        $downloadResult->getBody()->willReturn($stream);

        $client->request('GET', 'http://some-screenshot.url', Argument::any())
            ->willReturn($downloadResult)
        ;

        /** @noinspection PhpParamsInspection */
        $screenshotRepository->saveFileAndStatus(Argument::type(Screenshot::class))
            ->shouldBeCalled()
        ;

        /** @noinspection PhpParamsInspection */
        $screenshotSendService->publish(Argument::type(Screenshot::class))
            ->shouldBeCalled()
        ;

        $this->execute();
    }
}
