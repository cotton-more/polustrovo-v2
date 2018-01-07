<?php

declare(strict_types=1);

namespace spec\Polustrovo\Repository;

use BrowshotAPI\Message\ScreenshotStatus;
use ParagonIE\EasyDB\EasyDB;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotId;
use Polustrovo\Repository\ScreenshotRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ScreenshotRepositorySpec extends ObjectBehavior
{
    public function let(EasyDB $easyDB) {
        $this->beConstructedWith($easyDB);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ScreenshotRepository::class);
    }

    public function it_return_next_identity()
    {
        $this->nextIdentity()->shouldBeAnInstanceOf(ScreenshotId::class);
    }

    public function it_takes_screenshot_sets_screenshot_id_and_inserts_it_into_database(
        EasyDB $easyDB
    ) {
        $screenshot = (new Screenshot)->with([
            'browshotId' => 1000,
            'browshotInstance' => 10,
            'url' => '',
            'status' => ScreenshotStatus::IN_QUEUE
        ]);

        /** @noinspection PhpParamsInspection */
        $easyDB->insert(
            'screenshot',
            Argument::allOf(
                Argument::withKey('screenshot_id'),
                Argument::withKey('browshot_id'),
                Argument::withKey('browshot_instance'),
                Argument::withKey('url'),
                Argument::withKey('status'),
                Argument::withKey('error_message')
            )
        )->shouldBeCalled()->willReturn(1);

        $this->create($screenshot)->shouldBeAnInstanceOf(ScreenshotId::class);
    }

    public function it_returns_queued_screenshots(EasyDB $easyDB)
    {
        $sql = <<<SQL
SELECT 
  screenshot_id,
  browshot_id,
  browshot_instance,
  url,
  status,
  error_message,
  filename,
  file_size,
  created_at
FROM screenshot
WHERE status NOT IN (?) AND filename IS NULL
SQL;
        $params = [ScreenshotStatus::ERROR];
        $easyDB->run($sql, ...$params)->shouldBeCalled()->willReturn([
            [ 'screenshot_id' => 'uuid-1', 'browshot_id' => 11, 'browshot_instance' => 1, 'url' => '', 'status' => 0, 'error_message' => '', 'filename' => '', 'file_size' => 0, 'created_at' => '2018-01-01 00:00:00'],
            [ 'screenshot_id' => 'uuid-2', 'browshot_id' => 11, 'browshot_instance' => 1, 'url' => '', 'status' => 0, 'error_message' => '', 'filename' => '', 'file_size' => 0, 'created_at' => '2018-01-01 00:00:00'],
        ]);

        $this->getQueued()->shouldBeArray();
        $this->getQueued()->shouldHaveCount(2);
    }

    public function it_saves_file_and_status_properties(EasyDB $easyDB)
    {
        $screenshot = Screenshot::create([
            'screenshotId' => 'uuid-1',
        ]);

        $screenshot = $screenshot->with([
            'filename' => 'file',
            'fileSize' => 500,
            'status' => ScreenshotStatus::FINISHED,
        ]);

        $easyDB->update('screenshot', [
            'status' => ScreenshotStatus::FINISHED,
            'filename' => 'file',
            'file_size' => 500,
        ], ['screenshot_id' => 'uuid-1'])->shouldBeCalled()->willReturn(1);

        $this->saveFileAndStatus($screenshot)->shouldBe(1);
    }

    public function it_gets_screenshot_by_id(
        EasyDB $easyDB
    ) {
        $sql = <<<SQL
SELECT * FROM screenshot WHERE screenshot_id = ?
SQL;

        $easyDB->row($sql, 'uuid')->shouldBeCalled()->willReturn([]);

        $this->getById(new ScreenshotId('uuid'))->shouldBeAnInstanceOf(Screenshot::class);
    }

    public function it_updates_status(EasyDB $easyDB)
    {
        $easyDB->update('screenshot', [
            'status' => ScreenshotStatus::ERROR,
            'error_message' => 'error',
        ], [
            'screenshot_id' => 'uuid',
        ])->shouldBeCalled()->willReturn(1);

        $screenshot = (new Screenshot())->with([
            'screenshotId' => 'uuid',
            'status' => ScreenshotStatus::ERROR,
        ]);

        $this->updateStatus($screenshot, 'error')->shouldBe(1);
    }
}
