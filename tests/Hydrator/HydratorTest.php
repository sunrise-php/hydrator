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
            'array' => [
                'foo',
                'bar',
                100,
            ],
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

        $object = new Fixture\FooDto();
        $hydrator = new Hydrator();
        $hydrator->hydrate($object, $data);

        $this->assertSame('default value', $object::$static);
        $this->assertSame('default value', $object->valuable);
        $this->assertSame($data['nullable'], $object->nullable);
        $this->assertSame($data['bool'], $object->bool);
        $this->assertSame($data['int'], $object->int);
        $this->assertSame($data['float'], $object->float);
        $this->assertSame($data['string'], $object->string);
        $this->assertSame($data['array'], $object->array->getArrayCopy());
        $this->assertSame($data['dateTime'], $object->dateTime->format(DateTimeInterface::RFC3339_EXTENDED));
        $this->assertSame($data['barDto']['value'], $object->barDto->value);
        $this->assertSame($data['barDtoCollection'][0]['value'], $object->barDtoCollection->getIterator()[0]->value);
        $this->assertSame($data['barDtoCollection'][1]['value'], $object->barDtoCollection->getIterator()[1]->value);
        $this->assertSame($data['simpleArray'], $object->simpleArray);
        $this->assertSame($data['alias'], $object->hidden);
    }

    /**
     * @return void
     */
    public function testJsonTypeArrayAccess() : void
    {
        $object = (new Hydrator)->hydrate(new Fixture\TestJsonTypeDto(), ['json' => '[]']);

        $this->assertFalse(isset($object->json['foo']));
        $this->assertNull($object->json['foo']);

        $object->json['foo'] = 1;
        $this->assertTrue(isset($object->json['foo']));
        $this->assertSame(1, $object->json['foo']);

        unset($object->json['foo']);
        $this->assertFalse(isset($object->json['foo']));
        $this->assertNull($object->json['foo']);
    }

    /**
     * @return void
     */
    public function testJsonTypeJsonSerialize() : void
    {
        $object = (new Hydrator)->hydrate(new Fixture\TestJsonTypeDto(), ['json' => '[]']);

        $object->json['foo'] = 1;
        $object->json['bar'] = 2;

        $this->assertSame([
            'foo' => 1,
            'bar' => 2,
        ], $object->json->jsonSerialize());
    }

    /**
     * @return void
     */
    public function testJsonTypeDeserializeJson() : void
    {
        $json = '{"foo":1,"bar":2,"baz":{"qux":3}}';

        $object = (new Hydrator)->hydrate(new Fixture\TestJsonTypeDto(), ['json' => $json]);

        $this->assertSame(1, $object->json['foo']);
        $this->assertSame(2, $object->json['bar']);
        $this->assertSame(3, $object->json['baz']['qux']);
    }

    /**
     * @return void
     */
    public function testJsonTypeInvalidJsonSyntax() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage(
            'The <TestJsonTypeDto.json> property only accepts valid JSON data (Syntax error).'
        );

        (new Hydrator)->hydrate(new Fixture\TestJsonTypeDto(), ['json' => '{']);
    }

    /**
     * @return void
     */
    public function testJsonTypeInvalidJsonType() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <TestJsonTypeDto.json> property only accepts a string.');

        (new Hydrator)->hydrate(new Fixture\TestJsonTypeDto(), ['json' => []]);
    }

    /**
     * @return void
     */
    public function testJsonableObjectDeserializeJson() : void
    {
        $json = '{"foo":"foo:value","bar":"bar:value"}';

        $object = (new Hydrator)->hydrate(new Fixture\TestJsonDto(), ['json' => $json]);

        $this->assertSame('foo:value', $object->json->foo);
        $this->assertSame('bar:value', $object->json->bar);
    }

    /**
     * @return void
     */
    public function testJsonableObjectInvalidJsonSyntax() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <TestJsonDto.json> property only accepts valid JSON data (Syntax error).');

        (new Hydrator)->hydrate(new Fixture\TestJsonDto(), ['json' => '{']);
    }

    /**
     * @return void
     */
    public function testJsonableObjectInvalidJsonType() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <TestJsonDto.json> property only accepts a string.');

        (new Hydrator)->hydrate(new Fixture\TestJsonDto(), ['json' => []]);
    }

    /**
     * @return void
     */
    public function testMissingRequiredValueException() : void
    {
        $object = new Fixture\BarDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\MissingRequiredValueException::class);
        $this->expectExceptionMessage('The <BarDto.value> property is required.');

        $hydrator->hydrate($object, [
        ]);
    }

    /**
     * @return void
     */
    public function testUntypedObjectPropertyException() : void
    {
        $object = new Fixture\WithUntypedPropertyDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\UntypedObjectPropertyException::class);
        $this->expectExceptionMessage('The <WithUntypedPropertyDto.value> property is not typed.');

        $hydrator->hydrate($object, [
            'value' => 'foo',
        ]);
    }

    /**
     * @return void
     */
    public function testUnsupportedObjectPropertyTypeException() : void
    {
        $object = new Fixture\WithUnsupportedPropertyTypeDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\UnsupportedObjectPropertyTypeException::class);
        $this->expectExceptionMessage('The <WithUnsupportedPropertyTypeDto.value> property ' .
                                      'contains the <Traversable> unhydrable type.');

        $hydrator->hydrate($object, [
            'value' => 'foo',
        ]);
    }

    /**
     * @return void
     */
    public function testInvalidValueExceptionForNonNullableProperty() : void
    {
        $object = new Fixture\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.nonNullable> property does not support null.');

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
        $object = new Fixture\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.scalar> property only accepts a scalar value.');

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
        $object = new Fixture\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.array> property only accepts an array.');

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
        $object = new Fixture\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.dateTime> property only accepts a valid date-time string.');

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
        $object = new Fixture\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.oneToOne> property only accepts an array.');

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
        $object = new Fixture\BazDto();
        $hydrator = new Hydrator();

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <BazDto.oneToMany> property only accepts an array.');

        $hydrator->hydrate($object, [
            'oneToMany' => $nonArrayValue,
        ]);
    }

    public function testEnumerableValue() : void
    {
        $object = (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => 'A']);
        $this->assertSame('A:value', $object->foo->getValue());

        $object = (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => 'B']);
        $this->assertSame('B:value', $object->foo->getValue());

        $object = (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => 'C']);
        $this->assertSame('C:value', $object->foo->getValue());

        $object = (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => '0']);
        $this->assertSame('0:value', $object->foo->getValue());

        $object = (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => '1']);
        $this->assertSame('1:value', $object->foo->getValue());

        $object = (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => '2']);
        $this->assertSame('2:value', $object->foo->getValue());

        $object = (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => 0]);
        $this->assertSame('0:value', $object->foo->getValue());

        $object = (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => 1]);
        $this->assertSame('1:value', $object->foo->getValue());

        $object = (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => 2]);
        $this->assertSame('2:value', $object->foo->getValue());
    }

    public function testUnknownEnumerableValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <TestEnumDto.foo> property only accepts one of the <TestEnum> enum values.');

        (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => 'D']);
    }

    public function testInvalidEnumerableValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The <TestEnumDto.foo> property only accepts an integer or a string.');

        (new Hydrator)->hydrate(new Fixture\TestEnumDto(), ['foo' => []]);
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
            [1],
            [1.1],
            [''],
            ['0'],
            ['non-date-time-string'],
            [new \stdClass],
            [function () {
            }],
            [\STDOUT],
        ];
    }
}
