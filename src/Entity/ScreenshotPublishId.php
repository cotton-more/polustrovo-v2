<?php

declare(strict_types=1);

namespace Polustrovo\Entity;

use Ramsey\Uuid\Uuid;

final class ScreenshotPublishId
{
    /** @var string */
    private $id;

    public function __construct(string $id = null)
    {
        $this->id = $id ?? Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }
}