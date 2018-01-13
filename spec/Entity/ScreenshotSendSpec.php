<?php

namespace spec\Polustrovo\Entity;

use Polustrovo\Entity\ScreenshotId;
use Polustrovo\Entity\ScreenshotSend;
use PhpSpec\ObjectBehavior;
use Polustrovo\Entity\ScreenshotSendId;

class ScreenshotSendSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', [
            [
                'screenshotSendId' => 'uuid-1',
                'screenshotId' => 'uuid-2',
                'publisher' => 'some-publisher',
                'sentAt' => 'now',
                'errorMessage' => 'error',
                'createdAt' => '2018-01-02',
            ]
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ScreenshotSend::class);
    }

    public function it_gets_id()
    {
        $this->screenshotSendId()->shouldBeAnInstanceOf(ScreenshotSendId::class);
        $this->screenshotSendId()->id()->shouldBe('uuid-1');
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

    public function it_gets_sent_at()
    {
        $this->sentAt()->shouldBeAnInstanceOf(\DateTimeImmutable::class);
    }

    public function it_gets_error_message()
    {
        $this->errorMessage()->shouldBe('error');
    }

    public function it_gets_created_at()
    {
        $this->createdAt()->shouldBeAnInstanceOf(\DateTimeImmutable::class);
    }
}
