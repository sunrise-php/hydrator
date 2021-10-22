<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\Exception;
use Sunrise\Hydrator\Hydrator;
use Sunrise\Hydrator\HydratorInterface;
use DateTimeInterface;

/**
 * HydratorTest
 */
class HydratorTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $hydrator = new Hydrator();

        $this->assertInstanceOf(HydratorInterface::class, $hydrator);
    }

    /**
     * @return void
     */
    public function testHydrate() : void
    {
        $data = [
            'static' => 'foo',
            'nullable' => null,
            'bool' => false,
            'int' => 0,
            'float' => 0.0,
            'string' => 'foo',
            'dateTime' => '2005-08-15T15:52:01.000+00:00',
            'barDto' => [
                'value' => 'foo',
            ],
            'barDtoCollection' => [
                [
                    'value' => 'foo',
                ],
                [
                    'value' => 'bar',
                ],
            ],
            'simpleArray' => [
                'foo',
                'bar',
            ],
            'alias' => 'value',
        ];

        $object = new Fixtures\FooDto();
        $hydrator = new Hydrator();
        $hydrator->hydrate($object, $data);

        $this->assertSame('default value', $object::$static);
        $this->assertSame('default value', $object->valuable);
        $this->assertSame($data['nullable'], $object->nullable);
        $this->assertSame($data['bool'], $object->bool);
        $this->assertSame($data['int'], $object->int);
        $this->assertSame($data['float'], $object->float);
        $this->assertSame($data['string'], $object->string);
        $this->assertSame($data['dateTime'], $object->dateTime->format(DateTimeInterface::RFC3339_EXTENDED));
        $this->assertSame($data['barDto']['value'], $object->barDto->value);
        $this->assertSame($data['barDtoCollection'][0]['value'], $object->barDtoCollection->get(0)->value);
        $this->assertSame($data['barDtoCollection'][1]['value'], $object->barDtoCollection->get(1)->value);
        $this->assertSame($data['simpleArray'], $object->simpleArray);
        $this->assertSame($data['alias'], $object->hidden);
    }

    /**
     * @return void
     */
    public function testMissingRequiredValueException() : void
    {
        $object = new Fixtures\BarDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\MissingRequiredValueException::class);
        $this->expectExceptionMessage('The <BarDto.value> property is required.');

        $hydrator->hydrate($object, [
        ]);
    }

    /**
     * @return void
     */
    public function testUntypedPropertyException() : void
    {
        $object = new Fixtures\WithUntypedPropertyDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\UntypedPropertyException::class);
        $this->expectExceptionMessage('The <WithUntypedPropertyDto.value> property is not typed.');

        $hydrator->hydrate($object, [
            'value' => 'foo',
        ]);
    }

    /**
     * @return void
     */
    public function testUnsupportedPropertyTypeException() : void
    {
        $object = new Fixtures\WithUnsupportedPropertyTypeDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage('The <WithUnsupportedPropertyTypeDto.value> property ' .
                                      'contains an unsupported type <Traversable>.');

        $hydrator->hydrate($object, [
            'value' => 'foo',
        ]);
    }

    /**
     * @return void
     */
    public function testInvalidValueExceptionForNonNullableProperty() : void
    {
        $object = new Fixtures\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.nonNullable> property cannot accept null.');

        $hydrator->hydrate($object, [
            'nonNullable' => null,
        ]);
    }

    /**
     * @param mixed $nonScalarValue
     *
     * @return void
     *
     * @dataProvider nonScalarDataProvider
     */
    public function testInvalidValueExceptionForScalarProperty($nonScalarValue) : void
    {
        $object = new Fixtures\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.scalar> property accepts a string only.');

        $hydrator->hydrate($object, [
            'scalar' => $nonScalarValue,
        ]);
    }

    /**
     * @param mixed $nonArrayValue
     *
     * @return void
     *
     * @dataProvider nonArrayDataProvider
     */
    public function testInvalidValueExceptionForArrayProperty($nonArrayValue) : void
    {
        $object = new Fixtures\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.array> property accepts an array only.');

        $hydrator->hydrate($object, [
            'array' => $nonArrayValue,
        ]);
    }

    /**
     * @param mixed $nonDateTimeValue
     *
     * @return void
     *
     * @dataProvider nonDateTimeDataProvider
     */
    public function testInvalidValueExceptionForDateTimeProperty($nonDateTimeValue) : void
    {
        $object = new Fixtures\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage(
            'The <BazDto.dateTime> property accepts a valid date-time string or a timestamp only.'
        );

        $hydrator->hydrate($object, [
            'dateTime' => $nonDateTimeValue,
        ]);
    }

    /**
     * @param mixed $nonArrayValue
     *
     * @return void
     *
     * @dataProvider nonArrayDataProvider
     */
    public function testInvalidValueExceptionForOneToOneProperty($nonArrayValue) : void
    {
        $object = new Fixtures\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.oneToOne> property accepts an array only.');

        $hydrator->hydrate($object, [
            'oneToOne' => $nonArrayValue,
        ]);
    }

    /**
     * @param mixed $nonArrayValue
     *
     * @return void
     *
     * @dataProvider nonArrayDataProvider
     */
    public function testInvalidValueExceptionForOneToManyProperty($nonArrayValue) : void
    {
        $object = new Fixtures\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.oneToMany> property accepts an array only.');

        $hydrator->hydrate($object, [
            'oneToMany' => $nonArrayValue,
        ]);
    }

    /**
     * @return array<array>
     */
    public function nonScalarDataProvider() : array
    {
        return [
            [[]],
            [new \stdClass],
            [function () {
            }],
            [\STDOUT],
        ];
    }

    /**
     * @return array<array>
     */
    public function nonArrayDataProvider() : array
    {
        return [
            [true],
            [1],
            [1.1],
            [''],
            [new \stdClass],
            [function () {
            }],
            [\STDOUT],
        ];
    }

    /**
     * @return array<array>
     */
    public function nonDateTimeDataProvider() : array
    {
        return [
            [true],
            [1.1],
            [''],
            ['non-date-time-string'],
            [new \stdClass],
            [function () {
            }],
            [\STDOUT],
        ];
    }
}
