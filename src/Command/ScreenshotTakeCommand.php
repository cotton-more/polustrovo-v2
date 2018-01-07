<?php

declare(strict_types=1);

namespace Polustrovo\Command;

use BrowshotAPI\Message\ScreenshotCreateRequest;
use Polustrovo\Service\ScreenshotTakeService;
use Psr\Log\LoggerInterface;

class ScreenshotTakeCommand
{
    /** @var ScreenshotCreateRequest */
    private $request;

    /** @var ScreenshotTakeService */
    private $service;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ScreenshotCreateRequest $request,
        ScreenshotTakeService $service,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->service = $service;
        $this->logger = $logger;
    }

    public function __invoke(string $url, int $instanceId)
    {
        $this->logger->debug('Take screenshot', compact(['url', 'instanceId']));

        $this->request->clear();

        $this->request
            ->setUrl($url)
            ->setInstanceId($instanceId)
            ->setDelay(60)
            ->setFlashDelay(30)
            ->setScreenWidth(800)
            ->setScreenHeight(600)
        ;

        $this->service->execute($this->request);

        $this->logger->debug('end');
    }
}