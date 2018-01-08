<?php

namespace Polustrovo\Command;

use Polustrovo\Service\ScreenshotSendService;

class ScreenshotSendCommand
{
    /** @var ScreenshotSendService */
    private $screenshotSendService;

    public function __construct(
        ScreenshotSendService $screenshotSendService
    ) {
        $this->screenshotSendService = $screenshotSendService;
    }

    public function __invoke()
    {
        $this->screenshotSendService->sendAll();
    }
}