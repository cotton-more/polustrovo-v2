<?php

namespace spec\Polustrovo\Entity;

use Polustrovo\Entity\ScreenshotId;
use Polustrovo\Entity\ScreenshotPublish;
use PhpSpec\ObjectBehavior;
use Polustrovo\Entity\ScreenshotPublishId;

class ScreenshotPublishSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', [
            [
                'screenshotPublishId' => 'uuid-1',
                'screenshotId' => 'uuid-2',
                'publisher' => 'some-publisher',
                'publishedAt' => 'now',
                'createdAt' => '2018-01-02',
            ]
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ScreenshotPublish::class);
    }

    public function it_gets_id()
    {
        $this->screenshotPublishId()->shouldBeAnInstanceOf(ScreenshotPublishId::class);
        $this->screenshotPublishId()->id()->shouldBe('uuid-1');
    }

    public function it_gets_screenshot_id()
    {
        $this->screenshotId()->shouldBeAnInstanceOf(ScreenshotId::class);
        $this->screenshotId()->id()->shouldBe('uuid-2');
    }

    public function it_gets_publisher()
    {
        $this->publisher()->shouldBe('some-publisher');
    }

    public function it_gets_published_at()
    {
        $this->publishedAt()->shouldBeAnInstanceOf(\DateTimeImmutable::class);
    }

    public function it_gets_created_at()
    {
        $this->createdAt()->shouldBeAnInstanceOf(\DateTimeImmutable::class);
    }
}
