<?php

namespace Tests\Services;

use Grafite\Cms\Services\Normalizer;
use Tests\TestCase;

class NormalizerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new Normalizer('<p>sample</p>');
    }

    public function testToString()
    {
        $result = $this->service->__toString();

        $this->assertContains('sample', $result);
    }

    public function testPlain()
    {
        $result = $this->service->plain();

        $this->assertEquals('sample', $result);
    }
}
