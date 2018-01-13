<?php

namespace spec\Polustrovo\Service;

use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotSend;
use Polustrovo\Exception\PublisherException;
use Polustrovo\Repository\ScreenshotSendRepository;
use Polustrovo\Service\Publisher\Publishable;
use PhpSpec\ObjectBehavior;
use Polustrovo\Service\ScreenshotSendService;
use Prophecy\Argument;

class ScreenshotSendServiceSpec extends ObjectBehavior
{
    const ENABLED_PUBLISHERS = ['publisher'];

    public function let(
        ScreenshotSendRepository $screenshotSendRepository
    ) {
        $this->beConstructedWith($screenshotSendRepository, self::ENABLED_PUBLISHERS);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ScreenshotSendService::class);
    }

    public function it_adds_publisher(Publishable $publishable) {
        $publishable->getName()->shouldBeCalled()->willReturn('publisher');

        $this->publishers()->shouldHaveCount(0);

        $this->addPublisher($publishable);

        $this->publishers()->shouldHaveCount(1);
    }

    public function it_checks_if_could_be_send_with_a_publisher(Publishable $publishable)
    {
        $screenshotSendEntity = ScreenshotSend::create([
            'publisher' => 'publisher',
        ]);

        $publishable->getName()->shouldBeCalled()->willReturn('publisher');

        $this->canSendWithPublisher($screenshotSendEntity, $publishable)->shouldBe(true);

        $screenshotSendEntity = ScreenshotSend::create([
            'publisher' => 'other-publisher',
        ]);

        $this->canSendWithPublisher($screenshotSendEntity, $publishable)->shouldBe(false);
    }

    public function it_adds_screenshot_to_publish_if_size_is_more_than_minimum(
        ScreenshotSendRepository $screenshotSendRepository,
        Publishable $publishable
    ) {
        $screenshot = Screenshot::create([
            'fileSize' => 500000,
        ]);

        $publishable->getName()->willReturn('publisher');
        $this->addPublisher($publishable);

        $screenshotSendRepository->addToSend($screenshot, 'publisher')
            ->shouldBeCalled()
        ;

        $this->publish($screenshot);
    }

    public function it_does_not_publish_screenshot_with_file_size_less_than_minimum(
        ScreenshotSendRepository $screenshotSendRepository,
        Publishable $publishable
    ) {
        $screenshot = Screenshot::create([
            'fileSize' => ScreenshotSendService::MINIMUM_FILE_SIZE - 1,
        ]);

        $publishable->getName()->willReturn('publisher');
        $this->addPublisher($publishable);

        $screenshotSendRepository->addToSend($screenshot, 'publisher')
            ->shouldNotBeCalled()
        ;

        $this->publish($screenshot);
    }

    public function it_sends_unpublished_screenshots_with_its_publisher_and_handle_publisher_exception(
        Publishable $fooPublisher,
        Publishable $barPublisher,
        ScreenshotSendRepository $screenshotSendRepository
    ) {
        $this->beConstructedWith($screenshotSendRepository, ['foo', 'bar']);

        $fooPublisher->getName()->willReturn('foo');

        /** @noinspection PhpParamsInspection */
        $fooPublisher->send(
            Argument::which('publisher', 'foo')
        )->shouldBeCalled();

        $this->addPublisher($fooPublisher);

        $barPublisher->getName()->shouldBeCalled()->willReturn('bar');

        /** @noinspection PhpParamsInspection */
        $barPublisher->send(
            Argument::which('publisher', 'bar')
        )->willThrow(new PublisherException('some error', 0, $barPublisher->getWrappedObject()));

        $this->addPublisher($barPublisher);

        $screenshotSendRepository->getUnsent()->willReturn([
            ScreenshotSend::create(['publisher' => 'foo']),
            ScreenshotSend::create(['publisher' => 'bar']),
        ]);

        /** @noinspection PhpParamsInspection */
        $screenshotSendRepository->setAsSent(
            Argument::allOf(
                Argument::which('publisher', 'foo')
            )
        )->shouldBeCalled();

        /** @noinspection PhpParamsInspection */
        $screenshotSendRepository->setAsSent(
            Argument::allOf(
                Argument::which('publisher', 'bar'),
                Argument::which('errorMessage', 'bar: some error')
            )
        )->shouldBeCalled();

        $this->sendAll();
    }
}
