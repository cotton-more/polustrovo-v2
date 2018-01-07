<?php

declare(strict_types=1);

namespace Polustrovo\Entity;

final class ScreenshotPublish
{
    use Entity;

    const IDS = [
        'screenshotPublishId' => ScreenshotPublishId::class,
        'screenshotId' => ScreenshotId::class,
    ];

    const DATES = ['createdAt', 'publishedAt'];

    /** @var ScreenshotPublishId */
    protected $screenshotPublishId;

    /** @var ScreenshotId */
    protected $screenshotId;

    /** @var string */
    protected $publisher;

    /** @var \DateTimeImmutable */
    protected $publishedAt;

    /** @var string */
    protected $errorMessage = '';

    /** @var \DateTimeImmutable */
    protected $createdAt;

    /**
     * @return ScreenshotPublishId
     */
    public function screenshotPublishId(): ScreenshotPublishId
    {
        return $this->screenshotPublishId;
    }

    /**
     * @return ScreenshotId
     */
    public function screenshotId(): ScreenshotId
    {
        return $this->screenshotId;
    }

    /**
     * @return string
     */
    public function publisher(): string
    {
        return $this->publisher;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function publishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    /**
     * @return string
     */
    public function errorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}