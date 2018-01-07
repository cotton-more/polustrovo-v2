<?php

namespace Polustrovo\Command;

use Polustrovo\Service\ScreenshotDownloadService;

class ScreenshotDownloadCommand
{
    /** @var ScreenshotDownloadService */
    private $service;

    /**
     * ScreenshotDownloadCommand constructor.
     * @param ScreenshotDownloadService $service
     */
    public function __construct(
        ScreenshotDownloadService $service
    ) {
        $this->service = $service;
    }

    public function __invoke()
    {
        $this->service->execute();
    }
}