<?php

namespace Polustrovo\Service\Publisher;

use Polustrovo\Entity\ScreenshotSend;
use Polustrovo\Exception\PublisherException;
use Polustrovo\Repository\ScreenshotRepository;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TelegramPublisher implements Publishable
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ScreenshotRepository */
    private $screenshotRepository;

    /** @var BotApi */
    private $telegramBotApi;

    public function __construct(
        LoggerInterface $logger,
        ScreenshotRepository $screenshotRepository,
        BotApi $telegramBotApi
    ) {
        $this->logger = $logger;
        $this->screenshotRepository = $screenshotRepository;
        $this->telegramBotApi = $telegramBotApi;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'telegram';
    }

    /**
     * @param ScreenshotSend $screenshotPublish
     * @throws PublisherException
     */
    public function send(ScreenshotSend $screenshotPublish)
    {
        $screenshot = $this->screenshotRepository->getById($screenshotPublish->screenshotId());

        $photo = new \CURLFile(getenv('SCREENSHOTS_DIR').'/'.$screenshot->filename());

        $caption = (new \DateTime('now'))->format(DATE_RFC850);

        try {
            $message = $this->telegramBotApi->sendPhoto(getenv('TELEGRAM_CHAT_ID'), $photo, $caption);

            $this->logger->debug('Photo emitted', $message->toJson(true));
        } catch (InvalidArgumentException $e) {
            throw new PublisherException($e->getMessage(), $e->getCode(), $this, $e);
        } catch (Exception $e) {
            throw new PublisherException($e->getMessage(), $e->getCode(), $this, $e);
        }
    }
}
