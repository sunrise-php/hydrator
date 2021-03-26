<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\HydrableObjectCollection;
use Sunrise\Hydrator\HydrableObjectCollectionInterface;
use InvalidArgumentException;

/**
 * HydrableObjectCollectionTest
 */
class HydrableObjectCollectionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $collection = new HydrableObjectCollection();

        $this->assertInstanceOf(HydrableObjectCollectionInterface::class, $collection);
    }

    /**
     * @return void
     */
    public function testAdd() : void
    {
        $store = [
            new Fixture\BarDto(),
            new Fixture\BarDto(),
            new Fixture\BarDto(),
        ];

        $collection = new HydrableObjectCollection();

        $collection->add($store[0]);
        $collection->add($store[1]);
        $collection->add($store[2]);

        $this->assertSame($store, $collection->getIterator()->getArrayCopy());
    }

    /**
     * @return void
     */
    public function testUnexpectedObject() : void
    {
        $collection = new Fixture\BarDtoCollection();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The <' . Fixture\BarDtoCollection::class . '> collection ' .
                                      'must contain only the <' . Fixture\BarDto::class . '> objects.');

        $collection->add(new Fixture\BazDto());
    }
}
