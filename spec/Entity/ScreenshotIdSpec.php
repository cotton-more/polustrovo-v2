<?php

namespace spec\Polustrovo\Entity;

use Polustrovo\Entity\ScreenshotId;
use PhpSpec\ObjectBehavior;

class ScreenshotIdSpec extends ObjectBehavior
{
    const UUID = 'uuid';

    function it_is_initializable()
    {
        $this->shouldHaveType(ScreenshotId::class);
    }

    public function it_should_set_id_from_constructor()
    {
        $this->beConstructedWith(self::UUID);
        
        $this->id()->shouldBe(self::UUID);
    }

    public function it_generates_unique_uuid()
    {
        $this->beConstructedWith(null);

        $this->id()->shouldNotBe(self::UUID);
    }
}
