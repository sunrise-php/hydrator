<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\ObjectCollectionInterface;
use InvalidArgumentException;
use JsonSerializable;
use RuntimeException;

class ObjectCollectionTest extends TestCase
{
    public function testContracts() : void
    {
        $collection = new Fixtures\ObjectWithStringCollection();

        $this->assertInstanceOf(ObjectCollectionInterface::class, $collection);
        $this->assertInstanceOf(JsonSerializable::class, $collection);
    }

    public function testAdd() : void
    {
        $store = [
            new Fixtures\ObjectWithString(),
            new Fixtures\ObjectWithString(),
            new Fixtures\ObjectWithString(),
        ];

        $collection = new Fixtures\ObjectWithStringCollection();

        $this->assertTrue($collection->isEmpty());
        $this->assertSame([], $collection->all());

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

        $collection->add(0, new Fixtures\ObjectWithString());
    }

    public function testUnexpectedObject() : void
    {
        $collection = new Fixtures\ObjectWithStringCollection();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The ' . Fixtures\ObjectWithStringCollection::class . ' collection ' .
                                      'can contain the ' . Fixtures\ObjectWithString::class . ' objects only.');

        $collection->add(0, new Fixtures\ObjectWithInt());
    }

    public function testJsonSerialize() : void
    {
        $collection = new Fixtures\ObjectWithStringCollection();

        $this->assertSame($collection->all(), $collection->jsonSerialize());
    }
}
