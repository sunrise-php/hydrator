<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\ObjectCollection;
use Sunrise\Hydrator\ObjectCollectionInterface;
use InvalidArgumentException;
use JsonSerializable;
use RuntimeException;

class ObjectCollectionTest extends TestCase
{
    public function testContracts() : void
    {
        $collection = new Fixtures\BarCollection();

        $this->assertInstanceOf(ObjectCollectionInterface::class, $collection);
        $this->assertInstanceOf(JsonSerializable::class, $collection);
    }

    public function testAdd() : void
    {
        $store = [
            new Fixtures\Bar(),
            new Fixtures\Bar(),
            new Fixtures\Bar(),
        ];

        $collection = new Fixtures\BarCollection();

        $this->assertTrue($collection->isEmpty());

        $collection->add(0, $store[0]);
        $collection->add(1, $store[1]);
        $collection->add(2, $store[2]);

        $this->assertFalse($collection->isEmpty());

        $this->assertSame($store, $collection->all());
    }

    public function testUntypedCollection() : void
    {
        $collection = new Fixtures\UntypedCollection();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The ' . Fixtures\UntypedCollection::class . ' collection ' .
                                      'must contain the T constant.');

        $collection->add(0, new Fixtures\Bar());
    }

    public function testUnexpectedObject() : void
    {
        $collection = new Fixtures\BarCollection();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The ' . Fixtures\BarCollection::class . ' collection ' .
                                      'can contain the ' . Fixtures\Bar::class . ' objects only.');

        $collection->add(0, new Fixtures\Foo());
    }

    public function testJsonSerialize() : void
    {
        $collection = new Fixtures\BarCollection();

        $this->assertSame($collection->all(), $collection->jsonSerialize());
    }
}
