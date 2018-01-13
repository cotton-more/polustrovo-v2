<?php

namespace Polustrovo\Service\Publisher;

use Polustrovo\Entity\ScreenshotSend;
use Polustrovo\Exception\PublisherException;

interface Publishable
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param ScreenshotSend $screenshotPublish
     * @throws PublisherException
     */
    public function send(ScreenshotSend $screenshotPublish);
}