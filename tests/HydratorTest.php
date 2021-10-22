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

    public function testHydrate() : void
    {
        $data = [];
        $data['statical'] = '813ea72c-6763-4596-a4d6-b478efed61bb';
        $data['nullable'] = null;
        $data['required'] = '9f5c273e-1dca-4c2d-ac81-7d6b03b169f4';
        $data['boolean'] = true;
        $data['integer'] = 42;
        $data['number'] = 123.45;
        $data['string'] = 'db7614d4-0a81-437b-b2cf-c536ad229c97';
        $data['array'] = ['foo' => 'bar'];
        $data['object'] = (object) ['foo' => 'bar'];
        $data['dateTime'] = '2038-01-19 03:14:08';
        $data['dateTimeImmutable'] = '2038-01-19 03:14:08';
        $data['bar'] = ['value' => '9898fb3b-ffb0-406c-bda6-b516423abde7'];
        $data['barCollection'][] = ['value' => 'd85c17b6-6e2c-4e2d-9eba-e1dd59b75fe3'];
        $data['barCollection'][] = ['value' => '5a8019aa-1c15-4c7c-8beb-1783c3d8996b'];
        $data['non-normalized'] = 'f76c4656-431a-4337-9ba9-5440611b37f1';

        $object = (new Hydrator)
            ->useAnnotations()
            ->useAnnotations() // CC
            ->hydrate(new Fixtures\Foo(), $data);

        $this->assertNotSame($data['statical'], $object::$statical);
        $this->assertSame($data['nullable'], $object->nullable);
        $this->assertSame($data['required'], $object->required);
        $this->assertSame($data['boolean'], $object->boolean);
        $this->assertSame($data['integer'], $object->integer);
        $this->assertSame($data['number'], $object->number);
        $this->assertSame($data['string'], $object->string);
        $this->assertSame($data['array'], $object->array);
        $this->assertSame($data['object'], $object->object);
        $this->assertSame($data['dateTime'], $object->dateTime->format('Y-m-d H:i:s'));
        $this->assertSame($data['dateTimeImmutable'], $object->dateTimeImmutable->format('Y-m-d H:i:s'));
        $this->assertSame($data['bar']['value'], $object->bar->value);
        $this->assertSame($data['barCollection'][0]['value'], $object->barCollection->get(0)->value);
        $this->assertSame($data['barCollection'][1]['value'], $object->barCollection->get(1)->value);
        $this->assertSame($data['non-normalized'], $object->normalized);
    }

    public function testHydrateUndefinedObject() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The method ' . Hydrator::class . '::hydrate() ' .
                                      'expects an object or name of an existing class.');

        (new Hydrator)->hydrate('Undefined', []);
    }

    public function testHydrateUninitializableObject() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The object ' . Fixtures\UninitializableObject::class . ' ' .
                                      'cannot be hydrated because its constructor has required parameters.');

        (new Hydrator)->hydrate(Fixtures\UninitializableObject::class, []);
    }

    public function testHydrateWithJson() : void
    {
        $json = '{"value": "4c1e3453-7b76-4d5d-b4b8-bc6b0afcd835"}';

        $object = (new Hydrator)->useAnnotations()->hydrateWithJson(Fixtures\Bar::class, $json);

        $this->assertSame($object->value, '4c1e3453-7b76-4d5d-b4b8-bc6b0afcd835');
    }

    public function testHydrateWithInvalidJson() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to decode JSON: Syntax error');

        (new Hydrator)->useAnnotations()->hydrateWithJson(Fixtures\Bar::class, '!');
    }

    public function testUntypedProperty() : void
    {
        $this->expectException(Exception\UntypedPropertyException::class);
        $this->expectExceptionMessage('The ObjectWithUntypedProperty.value property is not typed.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithUntypedProperty::class, []);
    }

    public function testUnsupportedPropertyType() : void
    {
        $this->expectException(Exception\UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage('The ObjectWithUnsupportedPropertyType.value property ' .
                                      'contains an unsupported type iterable.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithUnsupportedPropertyType::class, [
            'value' => 'b25c08e8-4771-42c0-b01b-fe7fd3689602',
        ]);
    }

    public function testUnionPropertyType() : void
    {
        if (8 > \PHP_MAJOR_VERSION) {
            $this->markTestSkipped('PHP 8 is required...');
            return;
        }

        $this->expectException(Exception\UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage('The ObjectWithUnionPropertyType.value property ' .
                                      'contains an union type that is not supported.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithUnionPropertyType::class, []);
    }

    public function testRequiredProperty() : void
    {
        $this->expectException(Exception\MissingRequiredValueException::class);
        $this->expectExceptionMessage('The ObjectWithRequiredProperty.value property ' .
                                      'is required.');

        try {
            (new Hydrator)->hydrate(Fixtures\ObjectWithRequiredProperty::class, []);
        } catch (Exception\MissingRequiredValueException $e) {
            $this->assertSame('value', $e->getProperty()->getName());

            throw $e;
        }
    }

    public function testAnnotatedProperty() : void
    {
        $data = [
            'non-normalized-value' => '87019bbd-643b-45b8-94f8-0abd56be9851',
        ];

        $object = (new Hydrator)->useAnnotations()->hydrate(Fixtures\ObjectWithAnnotatedProperty::class, $data);

        $this->assertSame($object->value, '87019bbd-643b-45b8-94f8-0abd56be9851');
    }

    public function testUnnullableProperty() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The Bar.value property cannot accept null.');

        (new Hydrator)->hydrate(Fixtures\Bar::class, [
            'value' => null,
        ]);
    }

    public function testHydratePropertyWithStringBooleanValue() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithBooleanProperty::class, [
            'value' => 'yes',
        ]);

        $this->assertSame(true, $object->value);
    }

    public function testHydratePropertyWithStringIntegerNumber() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithIntegerProperty::class, [
            'value' => '42',
        ]);

        $this->assertSame(42, $object->value);
    }

    public function testHydratePropertyWithStringableNumber() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithNumberProperty::class, [
            'value' => '123.45',
        ]);

        $this->assertSame(123.45, $object->value);
    }

    public function testHydratePropertyWithIntegerTimestamp() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithTimestampProperty::class, [
            'value' => 1262304000,
        ]);

        $this->assertSame('2010-01-01', $object->value->format('Y-m-d'));
    }

    public function testHydratePropertyWithStringIntegerTimestamp() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithTimestampProperty::class, [
            'value' => '1262304000',
        ]);

        $this->assertSame('2010-01-01', $object->value->format('Y-m-d'));
    }

    public function testHydratePropertyWithStringDateTime() : void
    {
        $object = (new Hydrator)->hydrate(Fixtures\ObjectWithTimestampProperty::class, [
            'value' => '2010-01-01',
        ]);

        $this->assertSame('2010-01-01', $object->value->format('Y-m-d'));
    }

    public function testHydratePropertyWithInvalidBooleanValue() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithBooleanProperty.value property accepts a boolean value only.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithBooleanProperty::class, [
            'value' => [],
        ]);
    }

    public function testHydratePropertyWithInvalidIntegerNumber() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithIntegerProperty.value property accepts an integer number only.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithIntegerProperty::class, [
            'value' => [],
        ]);
    }

    public function testHydratePropertyWithInvalidNumber() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithNumberProperty.value property accepts a number only.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithNumberProperty::class, [
            'value' => [],
        ]);
    }

    public function testHydratePropertyWithInvalidString() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithStringProperty.value property accepts a string only.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithStringProperty::class, [
            'value' => [],
        ]);
    }

    public function testHydratePropertyWithInvalidArray() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithArrayProperty.value property accepts an array only.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithArrayProperty::class, [
            'value' => 0,
        ]);
    }

    public function testHydratePropertyWithInvalidObject() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithObjectProperty.value property accepts an object only.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithObjectProperty::class, [
            'value' => 0,
        ]);
    }

    public function testHydratePropertyWithInvalidTimestamp() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithTimestampProperty.value property ' .
                                      'accepts a valid date-time string or a timestamp only.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithTimestampProperty::class, [
            'value' => [],
        ]);
    }

    public function testHydratePropertyWithInvalidAssociation() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithAssociation.value property accepts an array only.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithAssociation::class, [
            'value' => 0,
        ]);
    }

    public function testHydratePropertyWithInvalidAssociations() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithAssociations.value property accepts an array only.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithAssociations::class, [
            'value' => 0,
        ]);
    }

    public function testHydratePropertyWithInvalidOneOfAssociations() : void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithAssociations.value property accepts an array with arrays only.');

        (new Hydrator)->hydrate(Fixtures\ObjectWithAssociations::class, [
            'value' => [0],
        ]);
    }
}
