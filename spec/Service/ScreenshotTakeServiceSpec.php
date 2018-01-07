<?php

namespace spec\Polustrovo\Service;

use BrowshotAPI\BrowshotAPI;
use BrowshotAPI\Message\ScreenshotCreateRequest;
use BrowshotAPI\Message\ScreenshotResponse;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotId;
use Polustrovo\Repository\ScreenshotRepository;
use Polustrovo\Service\ScreenshotTakeService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class ScreenshotTakeServiceSpec extends ObjectBehavior
{
    public function let(
        BrowshotAPI $browshotClient,
        ScreenshotRepository $screenshotRepository,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(...\func_get_args());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ScreenshotTakeService::class);
    }

    public function it_gets_response_from_screenshot_create_service_and_saves_it_to_database(
        BrowshotAPI $browshotClient,
        ScreenshotRepository $screenshotRepository
    ) {
        /** @noinspection PhpParamsInspection */
        $browshotClient->createScreenshot(Argument::type(ScreenshotCreateRequest::class))
            ->shouldBeCalled()
            ->willReturn(new ScreenshotResponse())
        ;

        /** @noinspection PhpParamsInspection */
        $screenshotRepository->create(Argument::type(Screenshot::class))->willReturn(new ScreenshotId());

        $this->execute(new ScreenshotCreateRequest());
    }
}
