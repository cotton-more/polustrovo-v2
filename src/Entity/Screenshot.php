<?php

namespace Polustrovo\Entity;

final class Screenshot
{
    use Entity;

    const IDS = [
        'screenshotId' => ScreenshotId::class,
    ];

    const DATES = ['createdAt'];

    /** @var ScreenshotId */
    protected $screenshotId;

    /** @var int */
    protected $browshotId;

    /** @var int */
    protected $browshotInstance;

    /** @var string */
    protected $url;

    /** @var int */
    protected $status;

    /** @var string */
    protected $errorMessage = '';

    /** @var string  */
    protected $filename;

    /** @var int */
    protected $fileSize = 0;

    /** @var \DateTimeImmutable */
    protected $createdAt;

    /**
     * @return ScreenshotId
     */
    public function screenshotId(): ScreenshotId
    {
        return $this->screenshotId;
    }

    /**
     * @return int
     */
    public function browshotId(): int
    {
        return $this->browshotId;
    }

    /**
     * @return int
     */
    public function browshotInstance(): int
    {
        return $this->browshotInstance;
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function errorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return string
     */
    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * @return int
     */
    public function fileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}