<?php

namespace spec\Polustrovo\Service\Publisher;

use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotPublish;
use Polustrovo\Exception\PublisherException;
use Polustrovo\Repository\ScreenshotRepository;
use Polustrovo\Service\Publisher\TelegramPublisher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Message;

class TelegramPublisherSpec extends ObjectBehavior
{
    public function let(
        LoggerInterface $logger,
        ScreenshotRepository $screenshotRepository,
        BotApi $telegramBotApi
    ) {
        $this->beConstructedWith(...\func_get_args());
        putenv('TELEGRAM_CHAT_ID=@chat_id');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TelegramPublisher::class);
    }

    public function it_gets_name()
    {
        $this->getName()->shouldBe('telegram');
    }

    public function it_sends_photo_with_telegram_bot_api(
        LoggerInterface $logger,
        ScreenshotRepository $screenshotRepository,
        BotApi $telegramBotApi
    ) {
        $this->beConstructedWith($logger, $screenshotRepository, $telegramBotApi);

        $screenshotPublish = ScreenshotPublish::create([
            'screenshotId' => 'uuid',
        ]);

        /** @noinspection PhpParamsInspection */
        $screenshotRepository->getById(Argument::which('id', 'uuid'))
            ->willReturn(
                Screenshot::create([
                    'filename' => 'filename',
                ])
            )
        ;

        /** @noinspection PhpUnhandledExceptionInspection */
        $telegramBotApi->sendPhoto('@chat_id', Argument::type(\CURLFile::class), Argument::type('string'))
            ->willReturn(new Message)
        ;

        $this->send($screenshotPublish);
    }

    public function it_throws_publisher_exception(
        LoggerInterface $logger,
        ScreenshotRepository $screenshotRepository,
        BotApi $telegramBotApi
    ) {
        $this->beConstructedWith($logger, $screenshotRepository, $telegramBotApi);

        $screenshotPublish = ScreenshotPublish::create([
            'screenshotId' => 'uuid',
        ]);

        /** @noinspection PhpParamsInspection */
        $screenshotRepository->getById(Argument::which('id', 'uuid'))
            ->willReturn(
                Screenshot::create([
                    'filename' => 'filename',
                ])
            )
        ;

        /** @noinspection PhpUnhandledExceptionInspection */
        $telegramBotApi->sendPhoto('@chat_id', Argument::type(\CURLFile::class), Argument::type('string'))
            ->willThrow(InvalidArgumentException::class)
        ;

        $this->shouldThrow(PublisherException::class)->during('send', [
            $screenshotPublish,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $telegramBotApi->sendPhoto('@chat_id', Argument::type(\CURLFile::class), Argument::type('string'))
            ->willThrow(Exception::class)
        ;

        $this->shouldThrow(PublisherException::class)->during('send', [
            $screenshotPublish,
        ]);
    }
}
