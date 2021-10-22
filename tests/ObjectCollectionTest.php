<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\ObjectCollection;
use Sunrise\Hydrator\ObjectCollectionInterface;
use InvalidArgumentException;
use RuntimeException;

class ObjectCollectionTest extends TestCase
{
    public function testContracts() : void
    {
        $collection = new Fixtures\BarCollection();

        $this->assertInstanceOf(ObjectCollectionInterface::class, $collection);
    }

    public function testAdd() : void
    {
        $store = [
            new Fixtures\Bar(),
            new Fixtures\Bar(),
            new Fixtures\Bar(),
        ];

        $collection = new Fixtures\BarCollection();

        $collection->add(0, $store[0]);
        $collection->add(1, $store[1]);
        $collection->add(2, $store[2]);

        $this->assertSame($store, $collection->all());
    }

    public function testUnexpectedObject() : void
    {
        $collection = new Fixtures\BarCollection();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The <' . Fixtures\BarCollection::class . '> collection ' .
                                      'must contain the <' . Fixtures\Bar::class . '> objects only.');

        $collection->add(0, new Fixtures\Foo());
    }
}
