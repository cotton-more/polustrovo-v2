<?php

declare(strict_types=1);

namespace Polustrovo\Exception;

class ScreenshotDownloadException extends \RuntimeException
{
    const SCREENSHOT_INVALID_STATUS = 10001;
    const DOWNLOAD_FAILED = 10002;
}