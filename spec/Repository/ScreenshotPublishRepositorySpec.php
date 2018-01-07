<?php

declare(strict_types=1);

namespace spec\Polustrovo\Repository;

use ParagonIE\EasyDB\EasyDB;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotId;
use Polustrovo\Entity\ScreenshotPublish;
use Polustrovo\Repository\ScreenshotPublishRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ScreenshotPublishRepositorySpec extends ObjectBehavior
{
    const PUBLISHER = 'some_publisher';

    public function let(EasyDB $easyDB)
    {
        $this->beConstructedWith($easyDB);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ScreenshotPublishRepository::class);
    }

    public function it_adds_to_publish_screenshot(
        EasyDB $easyDB
    ) {
        $screenshot = Screenshot::create([
            'screenshotId' => 'uuid-1',
            'filename' => 'filename',
        ]);

        /** @noinspection PhpParamsInspection */
        $easyDB->insert('screenshot_publish',
            Argument::allOf(
                Argument::withKey('screenshot_publish_id'),
                Argument::withEntry('publisher', self::PUBLISHER)
            )
        )->shouldBeCalled();

        $this->addToPublish($screenshot, self::PUBLISHER);
    }

    public function it_sets_as_published(
        EasyDB $easyDB
    ) {
        $screenshotPublish = ScreenshotPublish::create([
            'screenshotPublishId' => 'uuid',
            'errorMessage' => 'error',
        ]);

        $screenshotPublish = $screenshotPublish->with([
            'publishedAt' => '2018-01-01 00:00:00',
        ]);

        $easyDB->update('screenshot_publish', [
            'published_at' => '2018-01-01 00:00:00',
            'error_message' => 'error',
        ], [
            'screenshot_publish_id' => 'uuid',
        ])->shouldBeCalled();

        $this->setAsPublished($screenshotPublish);
    }

    public function it_returns_unpublished(EasyDB $easyDB)
    {
        $sql = 'SELECT sp.* FROM screenshot_publish sp WHERE published_at IS NULL';
        $easyDB->run($sql)->shouldBeCalled()->willReturn([
            [ 'screenshot_publish_id' => 'uuid-1', 'screenshot_id' => 'uuid-11', 'publisher' => 'publisher' ],
            [ 'screenshot_publish_id' => 'uuid-2', 'screenshot_id' => 'uuid-22', 'publisher' => 'publisher' ],
        ]);

        $this->getUnpublished()->shouldBeArray();
        $this->getUnpublished()->shouldHaveCount(2);
    }

    public function it_throws_exception_if_entity_without_changed_published_at_property_is_setting_as_published()
    {
        $screenshotPublish = ScreenshotPublish::create([
            'screenshotPublishId' => 'uuid',
            'publishedAt' => '2018-01-01 00:00:00',
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('setAsPublished', [$screenshotPublish]);
    }

    public function it_throws_exception_if_screenshot_id_list_is_empty()
    {
        $screenshotIdList = [];

        $this->shouldThrow(\InvalidArgumentException::class)->during('getListByScreenshotId', [
            $screenshotIdList,
        ]);
    }

    public function it_gets_screenshot_publish_list_by_their_id_list(
        EasyDB $easyDB
    ) {
        $screenshotIdList = [
            new ScreenshotId('uuid-1'),
            new ScreenshotId('uuid-2'),
        ];

        $sql = <<<SQL
SELECT sp.* FROM screenshot_publish sp WHERE screenshot_id IN (?, ?)
SQL;

        $easyDB->run($sql, 'uuid-1', 'uuid-2')->shouldBeCalled()->willReturn([
            [
                'screenshot_publish_id' => '',
                'publisher' => '',
            ],
            [
                'screenshot_publish_id' => '',
                'publisher' => '',
            ]
        ]);

        $this->getListByScreenshotId($screenshotIdList)->shouldHaveCount(2);
    }
}
