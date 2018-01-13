<?php

declare(strict_types=1);

namespace Polustrovo\Entity;

final class ScreenshotSend
{
    use Entity;

    const IDS = [
        'screenshotSendId' => ScreenshotSendId::class,
        'screenshotId' => ScreenshotId::class,
    ];

    const DATES = ['sentAt', 'createdAt'];

    /** @var ScreenshotSendId */
    protected $screenshotSendId;

    /** @var ScreenshotId */
    protected $screenshotId;

    /** @var string */
    protected $publisher;

    /** @var \DateTimeImmutable */
    protected $sentAt;

    /** @var string */
    protected $errorMessage = '';

    /** @var \DateTimeImmutable */
    protected $createdAt;

    /**
     * @return ScreenshotSendId
     */
    public function screenshotSendId(): ScreenshotSendId
    {
        return $this->screenshotSendId;
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
    public function sentAt(): \DateTimeImmutable
    {
        return $this->sentAt;
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