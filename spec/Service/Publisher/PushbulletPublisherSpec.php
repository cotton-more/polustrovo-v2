<?php

namespace spec\Polustrovo\Service\Publisher;

use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotPublish;
use Polustrovo\Exception\PublisherException;
use Polustrovo\Repository\ScreenshotRepository;
use Polustrovo\Service\Publisher\PushbulletPublisher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Pushbullet\Channel;
use Pushbullet\Exceptions\ConnectionException;
use Pushbullet\Exceptions\FilePushException;
use Pushbullet\Exceptions\InvalidTokenException;
use Pushbullet\Exceptions\NotFoundException;
use Pushbullet\Exceptions\NotPushableException;
use Pushbullet\Pushbullet;

class PushbulletPublisherSpec extends ObjectBehavior
{
    public function let(
        LoggerInterface $logger,
        ScreenshotRepository $screenshotRepository,
        Pushbullet $telegramBotApi
    ) {
        $this->beConstructedWith(...\func_get_args());
        putenv('SCREENSHOTS_DIR=/tmp');
        putenv('PUSHBULLET_CHANNEL=channel');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PushbulletPublisher::class);
    }

    public function it_gets_name()
    {
        $this->getName()->shouldBe('pushbullet');
    }

    public function it_sends_file_to_a_pushbullet_channel(
        LoggerInterface $logger,
        ScreenshotRepository $screenshotRepository,
        Pushbullet $pushbullet,
        Channel $channel
    ) {
        $this->beConstructedWith($logger, $screenshotRepository, $pushbullet);

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
        $channel->pushFile('/tmp/filename')->shouldBeCalled();

        /** @noinspection PhpUnhandledExceptionInspection */
        $pushbullet->channel('channel')->willReturn($channel);

        $logger->debug('Photo pushed')->shouldBeCalled();

        $this->send($screenshotPublish);
    }

    public function it_throws_publisher_exception(
        LoggerInterface $logger,
        ScreenshotRepository $screenshotRepository,
        Pushbullet $pushbullet
    ) {
        $this->beConstructedWith($logger, $screenshotRepository, $pushbullet);

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
        $pushbullet->channel('channel')->willThrow(ConnectionException::class);

        $this->shouldThrow(PublisherException::class)->during('send', [
            $screenshotPublish,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $pushbullet->channel('channel')->willThrow(InvalidTokenException::class);

        $this->shouldThrow(PublisherException::class)->during('send', [
            $screenshotPublish,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $pushbullet->channel('channel')->willThrow(FilePushException::class);

        $this->shouldThrow(PublisherException::class)->during('send', [
            $screenshotPublish,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $pushbullet->channel('channel')->willThrow(NotFoundException::class);

        $this->shouldThrow(PublisherException::class)->during('send', [
            $screenshotPublish,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $pushbullet->channel('channel')->willThrow(NotPushableException::class);

        $this->shouldThrow(PublisherException::class)->during('send', [
            $screenshotPublish,
        ]);
    }
}
