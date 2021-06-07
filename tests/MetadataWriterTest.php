<?php
declare(strict_types=1);

namespace OpenTracingInterceptor;

use PHPUnit\Framework\TestCase;

class MetadataWriterTest extends TestCase
{
    public function testOffsetSet()
    {
        $key = 'someKey';
        $carrier = [];
        $mv = new MetadataWriter($carrier);
        $mv[$key] = 1;
        $this->assertArrayHasKey($key, $carrier);
        $this->assertEquals([1], $mv[$key]);

        $mv[$key] = 2;
        $this->assertEquals([1, 2], $mv[$key]);
    }


    public function testOffsetUnset()
    {
        $key = 'someKey';
        $carrier = [];
        $mv = new MetadataWriter($carrier);
        $mv[$key] = 1;

        $mv->offsetUnset($key);
        $this->assertArrayNotHasKey($key, $carrier);
    }


    public function testOffsetExists()
    {
        $key = 'someKey';
        $carrier = [];
        $mv = new MetadataWriter($carrier);
        $mv[$key] = 1;

        $this->assertTrue($mv->offsetExists($key));
    }

    public function testOffsetGet()
    {
        $key = 'someKey';
        $carrier = [];
        $mv = new MetadataWriter($carrier);
        $mv[$key] = 1;
        $mv[$key] = 2;
        $this->assertEquals([1, 2], $mv->offsetGet($key));
        $this->assertEquals(null, $mv->offsetGet("unknownKey"));
    }
}
