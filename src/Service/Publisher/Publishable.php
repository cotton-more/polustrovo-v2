<?php

namespace Polustrovo\Service\Publisher;

use Polustrovo\Entity\ScreenshotPublish;
use Polustrovo\Exception\PublisherException;

interface Publishable
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param ScreenshotPublish $screenshotPublish
     * @throws PublisherException
     */
    public function send(ScreenshotPublish $screenshotPublish);
}