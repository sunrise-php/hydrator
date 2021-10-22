<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\ObjectCollection;
use Sunrise\Hydrator\ObjectCollectionInterface;
use InvalidArgumentException;

/**
 * ObjectCollectionTest
 */
class ObjectCollectionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $collection = new Fixtures\BarDtoCollection();

        $this->assertInstanceOf(ObjectCollectionInterface::class, $collection);
    }

    /**
     * @return void
     */
    public function testAdd() : void
    {
        $store = [
            new Fixtures\BarDto(),
            new Fixtures\BarDto(),
            new Fixtures\BarDto(),
        ];

        $collection = new Fixtures\BarDtoCollection();

        $collection->add(0, $store[0]);
        $collection->add(1, $store[1]);
        $collection->add(2, $store[2]);

        $this->assertSame($store, $collection->all());
    }

    /**
     * @return void
     */
    public function testUnexpectedObject() : void
    {
        $collection = new Fixtures\BarDtoCollection();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The <' . Fixtures\BarDtoCollection::class . '> collection ' .
                                      'must contain the <' . Fixtures\BarDto::class . '> objects only.');

        $collection->add(0, new Fixtures\BazDto());
    }
}
