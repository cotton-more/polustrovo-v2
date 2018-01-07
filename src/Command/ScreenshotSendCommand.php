<?php

namespace Polustrovo\Command;

use Polustrovo\Service\Publisher\PublisherManager;

class ScreenshotSendCommand
{
    /** @var PublisherManager */
    private $publisherManager;

    public function __construct(
        PublisherManager $publisherManager
    ) {
        $this->publisherManager = $publisherManager;
    }

    public function __invoke()
    {
        $this->publisherManager->sendAll();
    }
}