<?php

namespace Polustrovo\Exception;

use Polustrovo\Service\Publisher\Publishable;
use Polustrovo\Service\Publisher\TelegramPublisher;
use Throwable;

class PublisherException extends \RuntimeException implements PublisherThrowable
{
    /** @var TelegramPublisher */
    private $publisher;

    public function __construct(
        string $message = "",
        int $code = 0,
        Publishable $publisher,
        Throwable $previous = null
    ) {
        $this->publisher = $publisher;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getPublisherName(): string
    {
        return $this->publisher->getName();
    }
}