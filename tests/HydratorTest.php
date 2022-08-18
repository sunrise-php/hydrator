<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\Exception;
use Sunrise\Hydrator\Hydrator;
use Sunrise\Hydrator\HydratorInterface;
use InvalidArgumentException;

class HydratorTest extends TestCase
{
    public function testContracts() : void
    {
        $hydrator = new Hydrator();

        $this->assertInstanceOf(HydratorInterface::class, $hydrator);
    }

    public function testInvalidObject() : void
    {
        $this->expectException(Exception\InvalidObjectException::class);
        $this->expectExceptionMessage('The ' . Hydrator::class . '::hydrate() method ' .
                                      'expects an object or name of an existing class.');

        (new Hydrator)->hydrate('Undefined', []);
    }

    public function testUninitializableObject() : void
    {
        $this->expectException(Exception\InvalidObjectException::class);
        $this->expectExceptionMessage('The ' . Fixtures\UninitializableObject::class . ' object ' .
                                      'cannot be hydrated because its constructor has required parameters.');

        (new Hydrator)->hydrate(Fixtures\UninitializableObject::class, []);
    }

    public function testInvalidData() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The ' . Hydrator::class . '::hydrate(data) parameter ' .
                                      'expects an associative array or object.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithString::class, null);
    }

    public function testInvalidJson() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to decode JSON: Syntax error');

        (new Hydrator)->hydrateWithJson(Fixtures\ObjectWithString::class, '!');
    }

    public function testIgnoreStaticalProperty() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithStaticalProperty::class, ['value' => 'foo']);

        $this->assertNotSame('foo', $object::$value);
    }

    public function testUntypedProperty() : void
    {
        $this->expectException(Exception\UntypedPropertyException::class);
        $this->expectExceptionMessage('The ObjectWithUntypedProperty.value property ' .
                                      'is not typed.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithUntypedProperty::class, []);
    }

    public function testUnionPropertyType() : void
    {
        if (\PHP_MAJOR_VERSION < 8) {
            $this->markTestSkipped('php >= 8 is required.');
        }

        $this->expectException(Exception\UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage('The ObjectWithIntOrFloat.value property ' .
                                      'contains an union type that is not supported.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithIntOrFloat::class, []);
    }

    public function testHydrateAnnotatedProperty() : void
    {
        $object = (new Hydrator)
            ->useAnnotations()
            ->hydrate(Fixtures\ObjectWithAnnotatedAlias::class, ['non-normalized-value' => 'foo']);

        $this->assertSame('foo', $object->value);
    }

    public function testHydrateAnnotatedPropertyUsingNormalizedKey() : void
    {
        $object = (new Hydrator)
            ->useAnnotations()
            ->hydrate(Fixtures\ObjectWithAnnotatedAlias::class, ['value' => 'foo']);

        $this->assertSame('foo', $object->value);
    }

    public function testHydrateAnnotatedPropertyWhenDisabledAliasSupport() : void
    {
        $this->expectException(Exception\MissingRequiredValueException::class);
        $this->expectExceptionMessage('The ObjectWithAnnotatedAlias.value property ' .
                                      'is required.');

        (new Hydrator)
            ->useAnnotations()
            ->aliasSupport(false)
            ->hydrate(Fixtures\ObjectWithAnnotatedAlias::class, ['non-normalized-value' => 'foo']);
    }

    public function testHydrateAnnotatedPropertyWhenDisabledAnnotations() : void
    {
        $this->expectException(Exception\MissingRequiredValueException::class);
        $this->expectExceptionMessage('The ObjectWithAnnotatedAlias.value property ' .
                                      'is required.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithAnnotatedAlias::class, ['non-normalized-value' => 'foo']);
    }

    public function testHydrateAttributedProperty() : void
    {
        if (\PHP_MAJOR_VERSION < 8) {
            $this->markTestSkipped('php >= 8 is required.');
        }

        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithAttributedAlias::class, ['non-normalized-value' => 'foo']);

        $this->assertSame('foo', $object->value);
    }

    public function testHydrateAttributedPropertyUsingNormalizedKey() : void
    {
        if (\PHP_MAJOR_VERSION < 8) {
            $this->markTestSkipped('php >= 8 is required.');
        }

        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithAttributedAlias::class, ['value' => 'foo']);

        $this->assertSame('foo', $object->value);
    }

    public function testHydrateAttributedPropertyWhenDisabledAliasSupport() : void
    {
        if (\PHP_MAJOR_VERSION < 8) {
            $this->markTestSkipped('php >= 8 is required.');
        }

        $this->expectException(Exception\MissingRequiredValueException::class);
        $this->expectExceptionMessage('The ObjectWithAttributedAlias.value property ' .
                                      'is required.');

        (new Hydrator)
            ->aliasSupport(false)
            ->hydrate(Fixtures\ObjectWithAttributedAlias::class, ['non-normalized-value' => 'foo']);
    }

    public function testRequiredProperty() : void
    {
        $this->expectException(Exception\MissingRequiredValueException::class);
        $this->expectExceptionMessage('The ObjectWithString.value property ' .
                                      'is required.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithString::class, []);
    }

    public function testUnsupportedPropertyType() : void
    {
        $this->expectException(Exception\UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage('The ObjectWithUnsupportedType.value property ' .
                                      'contains an unsupported type iterable.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithUnsupportedType::class, ['value' => false]);
    }

    public function testOptionalProperty() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithOptionalString::class, []);

        $this->assertSame('75c4c2a0-e352-4eda-b2ed-b7f713ffb9ff', $object->value);
    }

    public function testHydrateObject() : void
    {
        $source = new Fixtures\ObjectWithString();

        // should return the source object...
        $object = (new Hydrator)->hydrate($source, ['value' => 'foo']);

        $this->assertSame($source, $object);
        $this->assertSame('foo', $source->value);
    }

    public function testHydrateUsingDataObject() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithString::class, (object) ['value' => 'foo']);

        $this->assertSame('foo', $object->value);
    }

    public function testHydrateWithJson() : void
    {
        $object = (new Hydrator)->hydrateWithJson(Fixtures\ObjectWithString::class, '{"value": "foo"}');

        $this->assertSame('foo', $object->value);
    }

    public function testConvertEmptyStringToNullForNonStringType() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithNullableInt::class, ['value' => '']);

        $this->assertNull($object->value);
    }

    public function testHydrateNullablePropertyWithNull() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithNullableString::class, ['value' => null]);

        $this->assertNull($object->value);
    }

    public function testHydrateUnnullablePropertyWithNull() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithString.value property ' .
                                      'cannot accept null.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithString::class, ['value' => null]);
    }

    /**
     * @dataProvider booleanValueProvider
     */
    public function testHydrateBooleanProperty($value, $expected) : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithBool::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function booleanValueProvider() : array
    {
        return [
            [true, true],
            [1, true],
            ['1', true],
            ['on', true],
            ['yes', true],
            [false, false],
            [0, false],
            ['0', false],
            ['off', false],
            ['no', false],
        ];
    }

    public function testHydrateBooleanPropertyWithInvalidValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithBool.value property expects a boolean.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithBool::class, ['value' => 'foo']);
    }

    /**
     * @dataProvider integerValueProvider
     */
    public function testHydrateIntegerProperty($value, $expected) : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithInt::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function integerValueProvider() : array
    {
        return [
            [42, 42],
            ['42', 42],
        ];
    }

    public function testHydrateIntegerPropertyWithInvalidValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithInt.value property expects an integer.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithInt::class, ['value' => 'foo']);
    }

    /**
     * @dataProvider numberValueProvider
     */
    public function testHydrateNumberProperty($value, $expected) : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithFloat::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function numberValueProvider() : array
    {
        return [
            [42, 42.0],
            ['42', 42.0],
            [42.0, 42.0],
            ['42.0', 42.0],
        ];
    }

    public function testHydrateNumberPropertyWithInvalidValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithFloat.value property expects a number.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithFloat::class, ['value' => 'foo']);
    }

    public function testHydrateStringableProperty() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithString::class, ['value' => 'foo']);

        $this->assertSame('foo', $object->value);
    }

    public function testHydrateStringablePropertyWithEmptyString() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithString::class, ['value' => '']);

        $this->assertSame('', $object->value);
    }

    public function testHydrateStringablePropertyWithInvalidValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithString.value property expects a string.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithString::class, ['value' => 42]);
    }

    public function testHydrateArrayableProperty() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithArray::class, ['value' => ['foo']]);

        $this->assertSame(['foo'], $object->value);
    }

    public function testHydrateArrayablePropertyWithInvalidValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithArray.value property expects an array.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithArray::class, ['value' => 'foo']);
    }

    public function testHydrateObjectableProperty() : void
    {
        $value = (object) ['value' => 'foo'];

        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithObject::class, ['value' => $value]);

        $this->assertSame($value, $object->value);
    }

    public function testHydrateObjectablePropertyWithInvalidValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithObject.value property expects an object.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithObject::class, ['value' => 'foo']);
    }

    /**
     * @dataProvider timestampValueProvider
     */
    public function testHydrateDateTimeProperty($value, $expected) : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithDateTime::class, ['value' => $value]);

        $this->assertSame($expected, $object->value->format('Y-m-d'));
    }

    /**
     * @dataProvider timestampValueProvider
     */
    public function testHydrateDateTimeImmutableProperty($value, $expected) : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithDateTimeImmutable::class, ['value' => $value]);

        $this->assertSame($expected, $object->value->format('Y-m-d'));
    }

    public function timestampValueProvider() : array
    {
        return [
            [1262304000, '2010-01-01'],
            ['1262304000', '2010-01-01'],
            ['2010-01-01', '2010-01-01'],
        ];
    }

    public function testHydrateDateTimePropertyWithInvalidValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithDateTime.value property ' .
                                      'expects a valid date-time string or timestamp.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithDateTime::class, ['value' => 'foo']);
    }

    public function testHydrateDateTimeImmutablePropertyWithInvalidValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithDateTimeImmutable.value property ' .
                                      'expects a valid date-time string or timestamp.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithDateTimeImmutable::class, ['value' => 'foo']);
    }

    public function testHydrateDateIntervalProperty() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithDateInterval::class, ['value' => 'P33Y']);

        $this->assertSame(33, $object->value->y);
    }

    public function testHydrateDateIntervalPropertyWithInvalidValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithDateInterval.value property ' .
                                      'expects a string.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithDateInterval::class, ['value' => 42]);
    }

    public function testHydrateDateIntervalPropertyWithInvalidFormat() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithDateInterval.value property ' .
                                      'expects a valid date-interval string based on ISO 8601.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithDateInterval::class, ['value' => 'foo']);
    }

    /**
     * @dataProvider customEnumValueProvider
     */
    public function testHydrateCustomEnumContainedPropery($value, $expected) : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithCustomEnum::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function customEnumValueProvider() : array
    {
        return [
            [1, Fixtures\CustomEnum::Int1()],
            ['1', Fixtures\CustomEnum::Int1()],
            [2, Fixtures\CustomEnum::Int2()],
            ['2', Fixtures\CustomEnum::Int2()],
            ['foo', Fixtures\CustomEnum::String1()],
            ['bar', Fixtures\CustomEnum::String2()],
        ];
    }

    public function testHydrateCustomEnumContainedProperyWithUnknownValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithCustomEnum.value property ' .
                                      'expects one of the following values: ' .
                                      \implode(', ', Fixtures\CustomEnum::values()) . '.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithCustomEnum::class, ['value' => 'unknown']);
    }

    /**
     * @dataProvider stringableEnumValueProvider
     */
    public function testHydrateStringableEnumProperty($value, $expected) : void
    {
        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('php >= 8.1 is required.');
        }

        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithStringableEnum::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function stringableEnumValueProvider() : array
    {
        if (\PHP_VERSION_ID < 80100) {
            return [];
        }

        return [
            ['c1200a7e-136e-4a11-9bc3-cc937046e90f', Fixtures\StringableEnum::foo],
            ['a2b29b37-1c5a-4b36-9981-097ddd25c740', Fixtures\StringableEnum::bar],
            ['c1ea3762-9827-4c0c-808b-53be3febae6d', Fixtures\StringableEnum::baz],
        ];
    }

    public function testHydrateStringableEnumPropertyWithInvalidValue() : void
    {
        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('php >= 8.1 is required.');
        }

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithStringableEnum.value property ' .
                                      'expects the following type: string.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithStringableEnum::class, ['value' => 42]);
    }

    public function testHydrateStringableEnumPropertyWithInvalidUnknownCase() : void
    {
        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('php >= 8.1 is required.');
        }

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithStringableEnum.value property ' .
                                      'expects one of the following values: ' .
                                      \implode(', ', Fixtures\StringableEnum::values()) . '.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithStringableEnum::class, ['value' => 'foo']);
    }

    /**
     * @dataProvider numerableEnumValueProvider
     */
    public function testHydrateNumerableEnumProperty($value, $expected) : void
    {
        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('php >= 8.1 is required.');
        }

        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithNumerableEnum::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function numerableEnumValueProvider() : array
    {
        if (\PHP_VERSION_ID < 80100) {
            return [];
        }

        return [
            [1, Fixtures\NumerableEnum::foo],
            [2, Fixtures\NumerableEnum::bar],
            [3, Fixtures\NumerableEnum::baz],

            // should convert strings to integers...
            ['1', Fixtures\NumerableEnum::foo],
            ['2', Fixtures\NumerableEnum::bar],
            ['3', Fixtures\NumerableEnum::baz],
        ];
    }

    public function testHydrateNumerableEnumPropertyWithInvalidValue() : void
    {
        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('php >= 8.1 is required.');
        }

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithNumerableEnum.value property ' .
                                      'expects the following type: int.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithNumerableEnum::class, ['value' => 'foo']);
    }

    public function testHydrateNumerableEnumPropertyWithInvalidUnknownCase() : void
    {
        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('php >= 8.1 is required.');
        }

        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithNumerableEnum.value property ' .
                                      'expects one of the following values: ' .
                                      \implode(', ', Fixtures\NumerableEnum::values()) . '.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithNumerableEnum::class, ['value' => 42]);
    }

    public function testHydrateAssociatedProperty() : void
    {
        $o = (new Hydrator)->hydrate(Fixtures\ObjectWithAssociation::class, ['value' => ['value' => 'foo']]);

        $this->assertSame('foo', $o->value->value);
    }

    public function testHydrateAssociatedPropertyUsingDataObject() : void
    {
        $o = (new Hydrator)->hydrate(Fixtures\ObjectWithAssociation::class, ['value' => (object) ['value' => 'foo']]);

        $this->assertSame('foo', $o->value->value);
    }

    public function testHydrateAssociatedPropertyWithInvalidData() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithAssociation.value property ' .
                                      'expects an associative array or object.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithAssociation::class, ['value' => 'foo']);
    }

    public function testHydrateAssociationCollectionProperty() : void
    {
        $o = (new Hydrator)->hydrate(Fixtures\ObjectWithAssociations::class, ['value' => [
            'foo' => ['value' => 'foo'],
            'bar' => ['value' => 'bar'],
        ]]);

        $this->assertTrue($o->value->has('foo'));
        $this->assertSame('foo', $o->value->get('foo')->value);

        $this->assertTrue($o->value->has('bar'));
        $this->assertSame('bar', $o->value->get('bar')->value);
    }

    public function testHydrateAssociationCollectionPropertyUsingDataObject() : void
    {
        $o = (new Hydrator)->hydrate(Fixtures\ObjectWithAssociations::class, ['value' => (object) [
            'foo' => (object) ['value' => 'foo'],
            'bar' => (object) ['value' => 'bar'],
        ]]);

        $this->assertTrue($o->value->has('foo'));
        $this->assertSame('foo', $o->value->get('foo')->value);

        $this->assertTrue($o->value->has('bar'));
        $this->assertSame('bar', $o->value->get('bar')->value);
    }

    public function testHydrateAssociationCollectionPropertyWithInvalidData() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithAssociations.value property ' .
                                      'expects an associative array or object.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithAssociations::class, ['value' => 'foo']);
    }

    public function testHydrateAssociationCollectionPropertyWithInvalidChild() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithAssociations.value[0] property ' .
                                      'expects an associative array or object.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithAssociations::class, ['value' => ['foo']]);
    }

    public function testInvalidValueExceptionProperty() : void
    {
        try {
            (new Hydrator)->hydrate(Fixtures\ObjectWithString::class, ['value' => 42]);
        } catch (Exception\InvalidValueException $e) {
            $this->assertSame('value', $e->getProperty()->getName());
            $this->assertSame('ObjectWithString.value', $e->getPropertyPath());
        }
    }

    public function testHydrateProductWithJsonAsArray() : void
    {
        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('php >= 8.1 is required.');
        }

        $json = <<<JSON
        {
            "name": "ac7ce13e-9b2e-4b09-ae7a-973769ea43df",
            "category": {
                "name": "a0127d1b-28b6-40a9-9a62-cfb2e2b44abd"
            },
            "tags": [
                {
                    "name": "a9878435-506c-4757-92b0-69ea2bd15bc3"
                },
                {
                    "name": "73dc4db1-7965-41b6-88cb-4dc9df6fb3ea"
                }
            ],
            "status": 2
        }
        JSON;

        $product = (new Hydrator)->hydrateWithJson(Fixtures\Store\Product::class, $json);

        $this->assertSame('ac7ce13e-9b2e-4b09-ae7a-973769ea43df', $product->name);
        $this->assertSame('a0127d1b-28b6-40a9-9a62-cfb2e2b44abd', $product->category->name);
        $this->assertSame('a9878435-506c-4757-92b0-69ea2bd15bc3', $product->tags->get(0)->name);
        $this->assertSame('73dc4db1-7965-41b6-88cb-4dc9df6fb3ea', $product->tags->get(1)->name);
        $this->assertSame(2, $product->status->value);
    }

    public function testHydrateProductWithJsonAsObject() : void
    {
        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('php >= 8.1 is required.');
        }

        $json = <<<JSON
        {
            "name": "0f61ac0e-f732-4088-8082-cc396e7dcb80",
            "category": {
                "name": "d342d030-3c0c-431e-be54-2e933b722b7c"
            },
            "tags": [
                {
                    "name": "3635627a-e348-4ca4-8e62-4e5cd78043d2"
                },
                {
                    "name": "dccd816f-bb28-41f3-b1a9-ddaff1fdec5b"
                }
            ],
            "status": 2
        }
        JSON;

        $product = (new Hydrator)->hydrateWithJson(Fixtures\Store\Product::class, $json, 0);

        $this->assertSame('0f61ac0e-f732-4088-8082-cc396e7dcb80', $product->name);
        $this->assertSame('d342d030-3c0c-431e-be54-2e933b722b7c', $product->category->name);
        $this->assertSame('3635627a-e348-4ca4-8e62-4e5cd78043d2', $product->tags->get(0)->name);
        $this->assertSame('dccd816f-bb28-41f3-b1a9-ddaff1fdec5b', $product->tags->get(1)->name);
        $this->assertSame(2, $product->status->value);
    }
}
