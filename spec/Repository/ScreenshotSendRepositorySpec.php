<?php

namespace spec\Polustrovo\Repository;

use ParagonIE\EasyDB\EasyDB;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotSend;
use Polustrovo\Repository\ScreenshotSendRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ScreenshotSendRepositorySpec extends ObjectBehavior
{
    const PUBLISHER = 'some_publisher';

    public function let(EasyDB $easyDB)
    {
        $this->beConstructedWith($easyDB);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ScreenshotSendRepository::class);
    }

    public function it_adds_to_send_screenshot(
        EasyDB $easyDB
    ) {
        $screenshot = Screenshot::create([
            'screenshotId' => 'uuid-1',
            'filename' => 'filename',
        ]);

        /** @noinspection PhpParamsInspection */
        $easyDB->insert('screenshot_send',
            Argument::allOf(
                Argument::withKey('screenshot_send_id'),
                Argument::withEntry('screenshot_id', 'uuid-1'),
                Argument::withEntry('publisher', self::PUBLISHER),
                Argument::withEntry('sent_at', null)
            )
        )->shouldBeCalled();

        $this->addToSend($screenshot, self::PUBLISHER);
    }

    public function it_sets_as_sent(
        EasyDB $easyDB
    ) {
        $screenshotSend = ScreenshotSend::create([
            'screenshotSendId' => 'uuid',
            'errorMessage' => 'error',
        ]);

        $screenshotSend = $screenshotSend->with([
            'sentAt' => '2018-01-01 00:00:00',
        ]);

        $easyDB->update('screenshot_send', [
            'sent_at' => '2018-01-01 00:00:00',
            'error_message' => 'error',
        ], [
            'screenshot_send_id' => 'uuid',
        ])->shouldBeCalled();

        $this->setAsSent($screenshotSend);
    }

    public function it_returns_unsent(EasyDB $easyDB)
    {
        $sql = 'SELECT * FROM screenshot_send WHERE sent_at IS NULL';
        $easyDB->run($sql)->shouldBeCalled()->willReturn([
            [ 'screenshot_send_id' => 'uuid-1', 'screenshot_id' => 'uuid-11', 'publisher' => 'publisher', 'error_message' => '', 'created_at' => '', ],
            [ 'screenshot_send_id' => 'uuid-2', 'screenshot_id' => 'uuid-22', 'publisher' => 'publisher', 'error_message' => '', 'created_at' => '',],
        ]);

        $this->getUnsent()->shouldBeArray();
        $this->getUnsent()->shouldHaveCount(2);
    }

    public function it_throws_exception_if_entity_without_changed_sent_at_property_is_setting_as_sent()
    {
        $screenshotSend = ScreenshotSend::create([
            'screenshotPublishId' => 'uuid',
            'publishedAt' => '2018-01-01 00:00:00',
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('setAsSent', [$screenshotSend]);
    }
}
