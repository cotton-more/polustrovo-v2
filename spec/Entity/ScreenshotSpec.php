<?php

namespace spec\Polustrovo\Entity;

use PhpSpec\ObjectBehavior;
use Polustrovo\Entity\ScreenshotId;

class ScreenshotSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', [
            [
                'screenshotId' => 'uuid',
                'browshotId' => 123,
                'status' => 3,
                'errorMessage' => '',
                'isDownloaded' => false,
                'filename' => '',
                'createdAt' => '2017-12-29',
            ]
        ]);
    }

    public function it_gets_screenshot_id()
    {
        $this->screenshotId()->shouldBeAnInstanceOf(ScreenshotId::class);
        $this->screenshotId()->id()->shouldBe('uuid');
    }

    public function it_gets_browshot_id()
    {
        $this->browshotId()->shouldBe(123);
    }

    public function it_gets_status()
    {
        $this->status()->shouldBe(3);
    }

    public function it_gets_error_message()
    {
        $this->errorMessage()->shouldBe('');
    }

    public function it_gets_filename()
    {
        $this->filename()->shouldBe('');
    }

    public function it_gets_file_size()
    {
        $this->fileSize()->shouldBe(0);
    }

    public function it_gets_created_at()
    {
        $this->createdAt()->shouldBeAnInstanceOf(\DateTimeImmutable::class);
    }

    public function it_creates_new_object_when_calls_with_method()
    {
        $downloadedScreenshot = $this->getWrappedObject()->with([
            'fileSize' => 500,
        ]);

        $this->screenshotId()->id()->shouldBe($downloadedScreenshot->screenshotId()->id());

        $this->changes()->shouldHaveCount(0);
        $this->changes()->shouldNotBe($downloadedScreenshot->changes());
    }
}
