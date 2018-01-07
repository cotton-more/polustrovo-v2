<?php

namespace Polustrovo\Exception;


interface PublisherThrowable extends \Throwable
{
    public function getPublisherName(): string;
}