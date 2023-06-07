<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

use Doctrine\Common\Annotations\Reader;
use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\Annotation\Format;
use Sunrise\Hydrator\Dictionary\ErrorCode;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\UninitializableObjectException;
use Sunrise\Hydrator\Exception\UnsupportedPropertyTypeException;
use Sunrise\Hydrator\Exception\UntypedPropertyException;
use Sunrise\Hydrator\Hydrator;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Tests\Fixtures\IntegerEnum;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithAnnotatedAlias;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithArray;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithAttributedAlias;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithBoolean;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithInteger;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithIntegerEnum;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableArray;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableBoolean;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableInteger;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableIntegerEnum;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableNumber;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableRelationship;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableRelationships;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableString;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableStringEnum;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableTimestamp;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNullableUnixTimeStamp;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithNumber;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalArray;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalBoolean;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalInteger;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalIntegerEnum;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalNumber;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalRelationship;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalRelationships;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalString;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalStringEnum;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalTimestamp;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithOptionalUnixTimeStamp;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithRelationship;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithRelationshipWithNullableString;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithRelationshipWithOptionalString;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithRelationshipWithString;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithRelationships;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithRelationshipsWithLimit;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithRelationshipsWithUnstantiableObject;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithStaticalProperty;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithString;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithStringEnum;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithTimestamp;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithUnformattedTimestampProperty;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithUnixTimeStamp;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithUnstantiableRelationship;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithUnsupportedProperty;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithUnsupportedPropertyNotation;
use Sunrise\Hydrator\Tests\Fixtures\ObjectWithUntypedProperty;
use Sunrise\Hydrator\Tests\Fixtures\Store\Product;
use Sunrise\Hydrator\Tests\Fixtures\Store\Status;
use Sunrise\Hydrator\Tests\Fixtures\StringEnum;
use Sunrise\Hydrator\Tests\Fixtures\UnstantiableObject;

use function sprintf;
use function version_compare;

use const PHP_VERSION;
use const PHP_VERSION_ID;

class HydratorTest extends TestCase
{
    private ?int $invalidValueExceptionCount = null;
    private array $invalidValueExceptionMessage = [];
    private array $invalidValueExceptionPropertyPath = [];
    private array $invalidValueExceptionErrorCode = [];

    /**
     * @group boolean
     * @dataProvider strictBooleanDataProvider
     * @dataProvider nonStrictBooleanDataProvider
     */
    public function testHydrateBooleanProperty(array $data, bool $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithBoolean::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group boolean
     * @dataProvider strictBooleanDataProvider
     * @dataProvider nonStrictBooleanDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNullableBooleanProperty(array $data, ?bool $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableBoolean::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group boolean
     * @dataProvider strictBooleanDataProvider
     * @dataProvider nonStrictBooleanDataProvider
     * @dataProvider emptyDataProvider
     */
    public function testHydrateOptionalBooleanProperty(array $data, bool $expected = true): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalBoolean::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group boolean
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNonNullableBooleanPropertyWithNull(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithBoolean::class, $data);
    }

    /**
     * @group boolean
     * @dataProvider notBooleanDataProvider
     */
    public function testHydrateBooleanPropertyWithNonBooleanValue(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type boolean.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_BOOLEAN);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithBoolean::class, $data);
    }

    /**
     * @group boolean
     */
    public function testHydrateRequiredBooleanPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithBoolean::class, []);
    }

    /**
     * @group integer
     * @dataProvider strictIntegerDataProvider
     * @dataProvider nonStrictIntegerDataProvider
     */
    public function testHydrateIntegerProperty(array $data, int $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithInteger::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group integer
     * @dataProvider strictIntegerDataProvider
     * @dataProvider nonStrictIntegerDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNullableIntegerProperty(array $data, ?int $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableInteger::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group integer
     * @dataProvider strictIntegerDataProvider
     * @dataProvider nonStrictIntegerDataProvider
     * @dataProvider emptyDataProvider
     */
    public function testHydrateOptionalIntegerProperty(array $data, int $expected = 42): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalInteger::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group integer
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNonNullableIntegerPropertyWithNull(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithInteger::class, $data);
    }

    /**
     * @group integer
     * @dataProvider notIntegerDataProvider
     */
    public function testHydrateIntegerPropertyWithNotInteger(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type integer.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_INTEGER);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithInteger::class, $data);
    }

    /**
     * @group integer
     */
    public function testHydrateRequiredIntegerPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithInteger::class, []);
    }

    /**
     * @group number
     * @dataProvider strictNumberDataProvider
     * @dataProvider nonStrictNumberDataProvider
     */
    public function testHydrateNumericProperty(array $data, float $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNumber::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group number
     * @dataProvider strictNumberDataProvider
     * @dataProvider nonStrictNumberDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNullableNumericProperty(array $data, ?float $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableNumber::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group number
     * @dataProvider strictNumberDataProvider
     * @dataProvider nonStrictNumberDataProvider
     * @dataProvider emptyDataProvider
     */
    public function testHydrateOptionalNumericProperty(array $data, float $expected = 3.14159): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalNumber::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group number
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNonNullableNumericPropertyWithNull(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithNumber::class, $data);
    }

    /**
     * @group number
     * @dataProvider notNumberDataProvider
     */
    public function testHydrateNumericPropertyWithNotNumber(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type number.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_NUMBER);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithNumber::class, $data);
    }

    /**
     * @group number
     */
    public function testHydrateRequiredNumericPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithNumber::class, []);
    }

    /**
     * @group string
     * @dataProvider stringDataProvider
     */
    public function testHydrateStringProperty(array $data, string $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithString::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group string
     * @dataProvider stringDataProvider
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNullableStringProperty(array $data, ?string $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableString::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group string
     * @dataProvider stringDataProvider
     * @dataProvider emptyDataProvider
     */
    public function testHydrateOptionalStringProperty(array $data, string $expected = 'default'): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalString::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group string
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNonNullableStringPropertyWithNull(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithString::class, $data);
    }

    /**
     * @group string
     * @dataProvider notStringDataProvider
     */
    public function testHydrateStringPropertyWithNotString(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type string.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithString::class, $data);
    }

    /**
     * @group string
     */
    public function testHydrateRequiredStringPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithString::class, []);
    }

    /**
     * @group array
     * @dataProvider arrayDataProvider
     */
    public function testHydrateArrayProperty(array $data, array $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithArray::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group array
     * @dataProvider arrayDataProvider
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNullableArrayProperty(array $data, ?array $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableArray::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group array
     * @dataProvider arrayDataProvider
     * @dataProvider emptyDataProvider
     */
    public function testHydrateOptionalArrayProperty(array $data, array $expected = []): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalArray::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group array
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNonNullableArrayPropertyWithNull(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithArray::class, $data);
    }

    /**
     * @group array
     * @dataProvider notArrayDataProvider
     */
    public function testHydrateArrayPropertyWithNotArray(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithArray::class, $data);
    }

    /**
     * @group array
     */
    public function testHydrateRequiredArrayPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithArray::class, []);
    }

    /**
     * @group datetimeTimestamp
     * @dataProvider timestampDataProvider
     */
    public function testHydrateTimestampProperty(array $data, string $expected, string $format): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithTimestamp::class, $data);
        $this->assertSame($expected, $object->value->format($format));
    }

    /**
     * @group datetimeTimestamp
     * @dataProvider timestampDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateNullableTimestampProperty(array $data, ?string $expected = null, ?string $format = null): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableTimestamp::class, $data);
        $this->assertSame($expected, isset($object->value, $format) ? $object->value->format($format) : null);
    }

    /**
     * @group datetimeTimestamp
     * @dataProvider timestampDataProvider
     * @dataProvider emptyDataProvider
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateOptionalTimestampProperty(array $data, ?string $expected = null, ?string $format = null): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalTimestamp::class, $data);
        $this->assertSame($expected, isset($object->value, $format) ? $object->value->format($format) : null);
    }

    /**
     * @group datetimeTimestamp
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNonNullableTimestampPropertyWithNull(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithTimestamp::class, $data);
    }

    /**
     * @group datetimeTimestamp
     * @dataProvider notStringDataProvider
     */
    public function testHydrateTimestampPropertyWithNotString(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type string.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithTimestamp::class, $data);
    }

    /**
     * @group datetimeTimestamp
     * @dataProvider invalidTimestampDataProvider
     */
    public function testHydrateTimestampPropertyWithInvalidTimestamp(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $message = sprintf('This value is not a valid timestamp, expected format: %s.', ObjectWithTimestamp::FORMAT);
        $this->assertInvalidValueExceptionMessage(0, $message);
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::INVALID_TIMESTAMP);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithTimestamp::class, $data);
    }

    /**
     * @group datetimeTimestamp
     */
    public function testHydrateRequiredTimestampPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithTimestamp::class, []);
    }

    /**
     * @group datetimeTimestamp
     * @dataProvider timestampDataProvider
     */
    public function testHydrateUnformattedTimestampProperty(array $data): void
    {
        $this->expectException(UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'The property %1$s.%2$s must contain the attribute %3$s, ' .
            'for example: #[\%3$s(\DateTimeInterface::DATE_RFC3339)].',
            ObjectWithUnformattedTimestampProperty::class,
            'value',
            Format::class,
        ));

        $this->createHydrator()->hydrate(ObjectWithUnformattedTimestampProperty::class, $data);
    }

    /**
     * @group unixTimestamp
     * @dataProvider strictUnixTimeStampDataProvider
     * @dataProvider nonStrictUnixTimeStampDataProvider
     */
    public function testHydrateUnixTimeStampProperty(array $data, string $expected, string $format): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithUnixTimeStamp::class, $data);
        $this->assertSame($expected, $object->value->format($format));
    }

    /**
     * @group unixTimestamp
     * @dataProvider strictUnixTimeStampDataProvider
     * @dataProvider nonStrictUnixTimeStampDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateNullableUnixTimeStampProperty(array $data, ?string $expected = null, ?string $format = null): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableUnixTimeStamp::class, $data);
        $this->assertSame($expected, isset($object->value, $format) ? $object->value->format($format) : null);
    }

    /**
     * @group unixTimestamp
     * @dataProvider strictUnixTimeStampDataProvider
     * @dataProvider nonStrictUnixTimeStampDataProvider
     * @dataProvider emptyDataProvider
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateOptionalUnixTimeStampProperty(array $data, ?string $expected = null, ?string $format = null): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalUnixTimeStamp::class, $data);
        $this->assertSame($expected, isset($object->value, $format) ? $object->value->format($format) : null);
    }

    /**
     * @group unixTimestamp
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNonNullableUnixTimeStampPropertyWithNull(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithUnixTimeStamp::class, $data);
    }

    /**
     * @group unixTimestamp
     * @dataProvider notIntegerDataProvider
     */
    public function testHydrateUnixTimeStampPropertyWithNotInteger(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type integer.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_INTEGER);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithUnixTimeStamp::class, $data);
    }

    /**
     * @group unixTimestamp
     */
    public function testHydrateRequiredUnixTimeStampPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithUnixTimeStamp::class, []);
    }

    /**
     * @group integerEnumeration
     * @dataProvider strictIntegerEnumerationDataProvider
     * @dataProvider nonStrictIntegerEnumerationDataProvider
     * @param IntegerEnum $expected
     */
    public function testHydrateIntegerEnumerationProperty(array $data, $expected): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithIntegerEnum::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group integerEnumeration
     * @dataProvider strictIntegerEnumerationDataProvider
     * @dataProvider nonStrictIntegerEnumerationDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     * @param IntegerEnum|null $expected
     */
    public function testHydrateNullableIntegerEnumerationProperty(array $data, $expected): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableIntegerEnum::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group integerEnumeration
     * @dataProvider strictIntegerEnumerationDataProvider
     * @dataProvider nonStrictIntegerEnumerationDataProvider
     * @dataProvider emptyDataProvider
     * @param IntegerEnum|null $expected
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateOptionalIntegerEnumerationProperty(array $data, $expected = null): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalIntegerEnum::class, $data);
        $this->assertSame($expected ?? IntegerEnum::FOO, $object->value);
    }

    /**
     * @group integerEnumeration
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNonNullableIntegerEnumerationPropertyWithNull(array $data): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithIntegerEnum::class, $data);
    }

    /**
     * @group integerEnumeration
     * @dataProvider notIntegerDataProvider
     */
    public function testHydrateIntegerEnumerationPropertyWithNotInteger(array $data): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type integer.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_INTEGER);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithIntegerEnum::class, $data);
    }

    /**
     * @group integerEnumeration
     */
    public function testHydrateRequiredIntegerEnumerationPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithIntegerEnum::class, []);
    }

    /**
     * @group stringEnumeration
     */
    public function testHydrateIntegerEnumerationPropertyWithInvalidChoice(): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(1);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertInvalidValueExceptionMessage(0, 'This value is not a valid choice, expected choices: 1, 2, 3.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::INVALID_CHOICE);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithIntegerEnum::class, ['value' => 42]);
    }

    /**
     * @group stringEnumeration
     * @dataProvider stringEnumerationDataProvider
     * @param StringEnum $expected
     */
    public function testHydrateStringEnumerationProperty(array $data, $expected): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithStringEnum::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group stringEnumeration
     * @dataProvider stringEnumerationDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     * @param StringEnum|null $expected
     */
    public function testHydrateNullableStringEnumerationProperty(array $data, $expected): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableStringEnum::class, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group stringEnumeration
     * @dataProvider stringEnumerationDataProvider
     * @dataProvider emptyDataProvider
     * @param StringEnum|null $expected
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateOptionalStringEnumerationProperty(array $data, $expected = null): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalStringEnum::class, $data);
        $this->assertSame($expected ?? StringEnum::FOO, $object->value);
    }

    /**
     * @group stringEnumeration
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNonNullableStringEnumerationPropertyWithNull(array $data): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithStringEnum::class, $data);
    }

    /**
     * @group stringEnumeration
     * @dataProvider notStringDataProvider
     */
    public function testHydrateStringEnumerationPropertyWithNotInteger(array $data): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type string.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithStringEnum::class, $data);
    }

    /**
     * @group stringEnumeration
     */
    public function testHydrateStringEnumerationPropertyWithInvalidChoice(): void
    {
        $this->phpRequired('8.1');
        $this->assertInvalidValueExceptionCount(1);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertInvalidValueExceptionMessage(0, 'This value is not a valid choice, expected choices: foo, bar, baz.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::INVALID_CHOICE);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithStringEnum::class, ['value' => 'unknown']);
    }

    /**
     * @group stringEnumeration
     */
    public function testHydrateRequiredStringEnumerationPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithStringEnum::class, []);
    }

    /**
     * @group relationship
     * @dataProvider stringDataProvider
     */
    public function testHydrateRelationshipProperty(array $data, string $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithRelationship::class, ['value' => $data]);
        $this->assertSame($expected, $object->value->value);
    }

    /**
     * @group relationship
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNullableRelationshipProperty(array $data): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableRelationship::class, $data);
        $this->assertNull($object->value);
    }

    /**
     * @group relationship
     * @dataProvider emptyDataProvider
     */
    public function testHydrateOptionalRelationshipProperty(array $data): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalRelationship::class, $data);
        $this->assertNull($object->value);
    }

    /**
     * @group relationship
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNonNullableRelationshipPropertyWithNull(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithRelationship::class, $data);
    }

    /**
     * @group relationship
     * @dataProvider notArrayDataProvider
     */
    public function testHydrateRelationshipPropertyWithNotArray(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithRelationship::class, $data);
    }

    /**
     * @group relationship
     */
    public function testHydrateRequiredRelationshipPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithRelationship::class, []);
    }

    /**
     * @group relationship
     * @dataProvider stringDataProvider
     */
    public function testHydrateRelationshipWithStringProperty(array $data, string $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithRelationshipWithString::class, ['value' => $data]);
        $this->assertSame($expected, $object->value->value);
    }

    /**
     * @group relationship
     * @dataProvider stringDataProvider
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateRelationshipWithNullableStringProperty(array $data, ?string $expected): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithRelationshipWithNullableString::class, ['value' => $data]);
        $this->assertSame($expected, $object->value->value);
    }

    /**
     * @group relationship
     * @dataProvider stringDataProvider
     * @dataProvider emptyDataProvider
     */
    public function testHydrateRelationshipWithOptionalStringProperty(array $data, string $expected = 'default'): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithRelationshipWithOptionalString::class, ['value' => $data]);
        $this->assertSame($expected, $object->value->value);
    }

    /**
     * @group relationship
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateRelationshipWithNonNullablePropertyWithNull(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.value');
        $this->createHydrator()->hydrate(ObjectWithRelationshipWithString::class, ['value' => $data]);
    }

    /**
     * @group relationship
     * @dataProvider notStringDataProvider
     */
    public function testHydrateRelationshipWithStringPropertyWithNotString(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type string.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.value');
        $this->createHydrator()->hydrate(ObjectWithRelationshipWithString::class, ['value' => $data]);
    }

    /**
     * @group relationship
     */
    public function testHydrateRelationshipWithRequiredStringPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.value');
        $this->createHydrator()->hydrate(ObjectWithRelationshipWithString::class, ['value' => []]);
    }

    /**
     * @group relationship
     */
    public function testHydrateUnstantiableRelationshipProperty(): void
    {
        $this->expectException(UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'The property %s.%s refers to a non-instantiable class %s.',
            ObjectWithUnstantiableRelationship::class,
            'value',
            UnstantiableObject::class,
        ));

        $this->createHydrator()->hydrate(ObjectWithUnstantiableRelationship::class, ['value' => []]);
    }

    /**
     * @group relationships
     */
    public function testHydrateRelationshipsProperty(): void
    {
        $data = ['value' => [['value' => ['value' => 'foo']], ['value' => ['value' => 'bar']]]];

        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithRelationships::class, $data);
        $this->assertCount(2, $object->value);
        $this->assertArrayHasKey(0, $object->value);
        $this->assertSame($data['value'][0]['value']['value'], $object->value[0]->value->value);
        $this->assertArrayHasKey(1, $object->value);
        $this->assertSame($data['value'][1]['value']['value'], $object->value[1]->value->value);
    }

    /**
     * @group relationships
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNullableRelationshipsProperty(array $data): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithNullableRelationships::class, $data);
        $this->assertNull($object->value);
    }

    /**
     * @group relationships
     * @dataProvider emptyDataProvider
     */
    public function testHydrateOptionalRelationshipsProperty(array $data): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(ObjectWithOptionalRelationships::class, $data);
        $this->assertSame([], $object->value);
    }

    /**
     * @group relationships
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNonNullableRelationshipsPropertyWithNull(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithRelationships::class, $data);
    }

    /**
     * @group relationships
     * @dataProvider notArrayDataProvider
     */
    public function testHydrateRelationshipsPropertyWithNotArray(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithRelationships::class, $data);
    }

    /**
     * @group relationships
     */
    public function testHydrateRequiredRelationshipsPropertyWithoutValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate(ObjectWithRelationships::class, []);
    }

    /**
     * @group relationships
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateRelationshipsPropertyWithNullsForNonNullableValues(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0.value.value');
        $this->createHydrator()->hydrate(ObjectWithRelationships::class, ['value' => [['value' => $data]]]);
    }

    /**
     * @group relationships
     * @dataProvider notStringDataProvider
     */
    public function testHydrateRelationshipsPropertyWithNotStringForStringValue(array $data): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type string.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0.value.value');
        $this->createHydrator()->hydrate(ObjectWithRelationships::class, ['value' => [['value' => $data]]]);
    }

    /**
     * @group relationships
     */
    public function testHydrateRelationshipsPropertyWithoutValueForRequiredValue(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0.value.value');
        $this->createHydrator()->hydrate(ObjectWithRelationships::class, ['value' => [['value' => []]]]);
    }

    /**
     * @group relationships
     */
    public function testHydrateRelationshipsPropertyWithNotArrayForRelation(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0');
        $this->createHydrator()->hydrate(ObjectWithRelationships::class, ['value' => [null]]);
    }

    /**
     * @group relationships
     */
    public function testSeveralErrorsWhenHydratingRelationshipsProperty(): void
    {
        $this->assertInvalidValueExceptionCount(3);
        $this->assertInvalidValueExceptionMessage(0, 'This value should not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0.value.value');
        $this->assertInvalidValueExceptionMessage(1, 'This value should be of type string.');
        $this->assertInvalidValueExceptionErrorCode(1, ErrorCode::VALUE_SHOULD_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(1, 'value.1.value.value');
        $this->assertInvalidValueExceptionMessage(2, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(2, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(2, 'value.2.value.value');
        $this->createHydrator()->hydrate(ObjectWithRelationships::class, ['value' => [
            ['value' => ['value' => null]],
            ['value' => ['value' => []]],
            ['value' => []]
        ]]);
    }

    /**
     * @group relationships
     */
    public function testHydrateLimitedRelationshipsProperty(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This element is redundant, limit: 1.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::REDUNDANT_ELEMENT);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.1');
        $this->createHydrator()->hydrate(ObjectWithRelationshipsWithLimit::class, ['value' => [
            ['value' => 'foo'],
            ['value' => 'bar'],
        ]]);
    }

    /**
     * @group relationships
     */
    public function testHydrateRelationshipsPropertyWithUnstantiableObject(): void
    {
        $this->expectException(UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'The property %s.%s refers to a non-instantiable class %s.',
            ObjectWithRelationshipsWithUnstantiableObject::class,
            'value',
            UnstantiableObject::class,
        ));

        $this->createHydrator()->hydrate(
            ObjectWithRelationshipsWithUnstantiableObject::class,
            ['value' => [['value' => []]]],
        );
    }

    /**
     * @group json
     */
    public function testHydrateObjectWithJson(): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrateWithJson(ObjectWithString::class, '{"value": "foo"}');
        $this->assertSame('foo', $object->value);
    }

    /**
     * @group json
     */
    public function testHydrateObjectWithInvalidJson(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage('Invalid JSON: Maximum stack depth exceeded');
        $this->createHydrator()->hydrateWithJson(ObjectWithString::class, '[]', 0, 1);
    }

    /**
     * @group json
     */
    public function testHydrateObjectWithNonObjectableJson(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage('JSON must be an object.');
        $this->createHydrator()->hydrateWithJson(ObjectWithString::class, 'null');
    }

    public function testInstantedObject(): void
    {
        $object = new class {
            public string $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, ['value' => 'foo']);
        $this->assertSame('foo', $object->value);
    }

    public function testUnstantiableObject(): void
    {
        $this->expectException(UninitializableObjectException::class);
        $this->expectExceptionMessage(sprintf(
            'The class %s cannot be hydrated because it is an uninstantiable class.',
            UnstantiableObject::class,
        ));

        $this->createHydrator()->hydrate(UnstantiableObject::class, []);
    }

    public function testStaticalProperty(): void
    {
        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate(ObjectWithStaticalProperty::class, ['value' => 'foo']);
        $this->assertNotSame('foo', ObjectWithStaticalProperty::$value);
    }

    public function testUnsupportedPropertyType(): void
    {
        $this->expectException(UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'The property %s.%s contains an unsupported type %s.',
            ObjectWithUnsupportedProperty::class,
            'value',
            'iterable',
        ));

        $this->createHydrator()->hydrate(ObjectWithUnsupportedProperty::class, ['value' => []]);
    }

    public function testUnsupportedPropertyTypeNotation(): void
    {
        $this->phpRequired('8.0');

        $this->expectException(UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'The property %s.%s contains an unsupported type %s.',
            ObjectWithUnsupportedPropertyNotation::class,
            'value',
            'int|float',
        ));

        $this->createHydrator()->hydrate(ObjectWithUnsupportedPropertyNotation::class, ['value' => []]);
    }

    public function testUntypedProperty(): void
    {
        $this->expectException(UntypedPropertyException::class);
        $this->expectExceptionMessage(sprintf(
            'The property %s.%s is not typed.',
            ObjectWithUntypedProperty::class,
            'value',
        ));

        $this->createHydrator()->hydrate(ObjectWithUntypedProperty::class, ['value' => []]);
    }

    public function testAnnotatedAlias(): void
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->createHydrator();
        $hydrator->useDefaultAnnotationReader();

        $this->assertInvalidValueExceptionCount(0);
        $object = $hydrator->hydrate(ObjectWithAnnotatedAlias::class, ['non-normalized-value' => 'foo']);
        $this->assertSame('foo', $object->value);

        $this->assertInvalidValueExceptionCount(1);
        $hydrator->hydrate(ObjectWithAnnotatedAlias::class, ['value' => 'foo']);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
    }

    public function testAttributedAlias(): void
    {
        $this->phpRequired('8.0');

        /** @var Hydrator $hydrator */
        $hydrator = $this->createHydrator();

        $this->assertInvalidValueExceptionCount(0);
        $object = $hydrator->hydrate(ObjectWithAttributedAlias::class, ['non-normalized-value' => 'foo']);
        $this->assertSame('foo', $object->value);

        $this->assertInvalidValueExceptionCount(1);
        $hydrator->hydrate(ObjectWithAttributedAlias::class, ['value' => 'foo']);
        $this->assertInvalidValueExceptionMessage(0, 'This value should be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::VALUE_SHOULD_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
    }

    public function testAnnotationReader(): void
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->createHydrator();

        $hydrator->setAnnotationReader(null);
        $this->assertNull($hydrator->getAnnotationReader());

        $reader = $this->createMock(Reader::class);
        $hydrator->setAnnotationReader($reader);
        $this->assertSame($reader, $hydrator->getAnnotationReader());

        $hydrator->setAnnotationReader(null);
        $this->assertNull($hydrator->getAnnotationReader());
    }

    public function testHydrateStore(): void
    {
        $this->phpRequired('8.1');

        $sold = Status::SOLD;

        $data = [];
        $data['name'] = 'Some product';
        $data['category']['name'] = 'Some category';
        $data['tags'][]['name'] = 'foo';
        $data['tags'][]['name'] = 'bar';
        $data['status'] = $sold->value;
        $data['createdAt'] = '2000-01-01 00:00:00';

        $this->assertInvalidValueExceptionCount(0);
        $product = $this->createHydrator()->hydrate(Product::class, $data);
        $this->assertSame('Some product', $product->name);
        $this->assertSame('Some category', $product->category->name);
        $this->assertCount(2, $product->tags);
        $this->assertArrayHasKey(0, $product->tags);
        $this->assertSame('foo', $product->tags[0]->name);
        $this->assertArrayHasKey(1, $product->tags);
        $this->assertSame('bar', $product->tags[1]->name);
        $this->assertSame(Status::SOLD, $product->status);
        $this->assertSame('2000-01-01 00:00:00', $product->createdAt->format('Y-m-d H:i:s'));

        unset($data['createdAt']);
        $this->assertInvalidValueExceptionCount(0);
        $product = $this->createHydrator()->hydrate(Product::class, $data);
        $this->assertSame('2020-01-01 12:00:00', $product->createdAt->format('Y-m-d H:i:s'));
    }

    public function testSymfonyViolations(): void
    {
        $violations = null;
        try {
            $this->createHydrator()->hydrate(ObjectWithString::class, []);
        } catch (InvalidDataException $e) {
            $violations = $e->getViolations();
        }

        $this->assertNotNull($violations);
        $this->assertCount(1, $violations);
        $this->assertTrue($violations->has(0));
        $this->assertSame(ErrorCode::VALUE_SHOULD_BE_PROVIDED, $violations->get(0)->getCode());
        $this->assertSame('This value should be provided.', $violations->get(0)->getMessage());
        $this->assertSame('value', $violations->get(0)->getPropertyPath());
    }

    public function emptyDataProvider(): array
    {
        return [
            [[]],
        ];
    }

    public function strictNullDataProvider(): array
    {
        return [
            [['value' => null], null],
        ];
    }

    public function nonStrictNullDataProvider(): array
    {
        return [
            [['value' => ''], null],
            [['value' => ' '], null],
        ];
    }

    public function strictBooleanDataProvider(): array
    {
        return [
            [['value' => true], true],
            [['value' => false], false],
        ];
    }

    public function nonStrictBooleanDataProvider(): array
    {
        return [
            [['value' => '1'], true],
            [['value' => '0'], false],
            [['value' => 'true'], true],
            [['value' => 'false'], false],
            [['value' => 'yes'], true],
            [['value' => 'no'], false],
            [['value' => 'on'], true],
            [['value' => 'off'], false],
        ];
    }

    public function notBooleanDataProvider(): array
    {
        return [
            [['value' => 0]],
            [['value' => 1]],
            [['value' => 42]],
            [['value' => 3.14159]],
            [['value' => 'foo']],
            [['value' => []]],
        ];
    }

    public function strictIntegerDataProvider(): array
    {
        return [
            [['value' => -1], -1],
            [['value' => 0], 0],
            [['value' => 1], 1],
        ];
    }

    public function nonStrictIntegerDataProvider(): array
    {
        return [
            [['value' => '-1'], -1],
            [['value' => '0'], 0],
            [['value' => '+1'], 1],
        ];
    }

    public function notIntegerDataProvider(): array
    {
        return [
            [['value' => true]],
            [['value' => false]],
            [['value' => 3.14159]],
            [['value' => 'foo']],
            [['value' => []]],
        ];
    }

    public function strictNumberDataProvider(): array
    {
        return [
            [['value' => -1], -1.],
            [['value' => 0], 0.],
            [['value' => 1], 1.],
            [['value' => -1.], -1.],
            [['value' => 0.], 0.],
            [['value' => 1.], 1.],
            [['value' => -.1], -.1],
            [['value' => .0], .0],
            [['value' => .1], .1],
        ];
    }

    public function nonStrictNumberDataProvider(): array
    {
        return [
            [['value' => '-1'], -1.],
            [['value' => '0'], 0.],
            [['value' => '+1'], 1.],
            [['value' => '-1.'], -1.],
            [['value' => '0.'], 0.],
            [['value' => '+1.'], 1.],
            [['value' => '-.1'], -.1],
            [['value' => '.0'], .0],
            [['value' => '+.1'], .1],
            [['value' => '-1.0'], -1.],
            [['value' => '0.0'], 0.],
            [['value' => '+1.0'], 1.],
            [['value' => '1e-1'], .1],
            [['value' => '1e1'], 10.],
            [['value' => '1e+1'], 10.],
            [['value' => '1.e-1'], .1],
            [['value' => '1.e1'], 10.],
            [['value' => '1.e+1'], 10.],
            [['value' => '.1e-1'], .01],
            [['value' => '.1e1'], 1.],
            [['value' => '.1e+1'], 1.],
            [['value' => '1.0e-1'], .1],
            [['value' => '1.0e1'], 10.],
            [['value' => '1.0e+1'], 10.],
        ];
    }

    public function notNumberDataProvider(): array
    {
        return [
            [['value' => true]],
            [['value' => false]],
            [['value' => 'foo']],
            [['value' => []]],
        ];
    }

    public function stringDataProvider(): array
    {
        return [
            [['value' => 'foo'], 'foo'],

            // Should not be cast to a null
            [['value' => ''], ''],
            [['value' => ' '], ' '],

            // Should not be cast to a boolean type
            [['value' => '1'], '1'],
            [['value' => '0'], '0'],
            [['value' => 'true'], 'true'],
            [['value' => 'false'], 'false'],
            [['value' => 'yes'], 'yes'],
            [['value' => 'no'], 'no'],
            [['value' => 'on'], 'on'],
            [['value' => 'off'], 'off'],

            // Should not be cast to a number
            [['value' => '42'], '42'],
            [['value' => '3.14159'], '3.14159'],
        ];
    }

    public function notStringDataProvider(): array
    {
        return [
            [['value' => true]],
            [['value' => false]],
            [['value' => 42]],
            [['value' => 3.14159]],
            [['value' => []]],
        ];
    }

    public function arrayDataProvider(): array
    {
        return [
            [['value' => ['foo']], ['foo']]
        ];
    }

    public function notArrayDataProvider(): array
    {
        return [
            [['value' => true]],
            [['value' => false]],
            [['value' => 42]],
            [['value' => 3.14159]],
            [['value' => 'foo']],
        ];
    }

    public function timestampDataProvider(): array
    {
        return [
            [['value' => '1970-01-01 00:00:00'], '1970-01-01 00:00:00', 'Y-m-d H:i:s'],
        ];
    }

    public function invalidTimestampDataProvider(): array
    {
        return [
            [['value' => 'Tue, 06 Jun 23 16:50:23']],
        ];
    }

    public function notTimestampDataProvider(): array
    {
        return [
            [['value' => true]],
            [['value' => false]],
            [['value' => 42]],
            [['value' => 3.14159]],
            [['value' => []]],
        ];
    }

    public function strictUnixTimeStampDataProvider(): array
    {
        return [
            [['value' => -1], '1969-12-31 23:59:59', 'Y-m-d H:i:s'],
            [['value' => 0], '1970-01-01 00:00:00', 'Y-m-d H:i:s'],
            [['value' => 1], '1970-01-01 00:00:01', 'Y-m-d H:i:s'],
        ];
    }

    public function nonStrictUnixTimeStampDataProvider(): array
    {
        return [
            [['value' => '-1'], '1969-12-31 23:59:59', 'Y-m-d H:i:s'],
            [['value' => '0'], '1970-01-01 00:00:00', 'Y-m-d H:i:s'],
            [['value' => '1'], '1970-01-01 00:00:01', 'Y-m-d H:i:s'],
        ];
    }

    public function notUnixTimeStampDataProvider(): array
    {
        return [
            [['value' => true]],
            [['value' => false]],
            [['value' => 'foo']],
            [['value' => []]],
        ];
    }

    public function strictIntegerEnumerationDataProvider(): array
    {
        if (PHP_VERSION_ID < 80000) {
            return [[[], null]];
        }

        $foo = IntegerEnum::FOO;
        $bar = IntegerEnum::BAR;
        $baz = IntegerEnum::BAZ;

        return [
            [['value' => $foo->value], $foo],
            [['value' => $bar->value], $bar],
            [['value' => $baz->value], $baz],
        ];
    }

    public function nonStrictIntegerEnumerationDataProvider(): array
    {
        if (PHP_VERSION_ID < 80000) {
            return [[[], null]];
        }

        $foo = IntegerEnum::FOO;
        $bar = IntegerEnum::BAR;
        $baz = IntegerEnum::BAZ;

        return [
            [['value' => (string) $foo->value], $foo],
            [['value' => (string) $bar->value], $bar],
            [['value' => (string) $baz->value], $baz],
        ];
    }

    public function stringEnumerationDataProvider(): array
    {
        if (PHP_VERSION_ID < 80000) {
            return [[[], null]];
        }

        $foo = StringEnum::FOO;
        $bar = StringEnum::BAR;
        $baz = StringEnum::BAZ;

        return [
            [['value' => $foo->value], $foo],
            [['value' => $bar->value], $bar],
            [['value' => $baz->value], $baz],
        ];
    }

    private function createHydrator(): HydratorInterface
    {
        $hydrator = new Hydrator();
        if (PHP_VERSION_ID < 80000) {
            $hydrator->useDefaultAnnotationReader();
        }

        return $hydrator;
    }

    private function phpRequired(string $version): void
    {
        if (version_compare(PHP_VERSION, $version, '<')) {
            $this->markTestSkipped(sprintf('PHP %s is required.', $version));
        }
    }

    private function assertInvalidValueExceptionCount(int $expectedCount): void
    {
        $this->invalidValueExceptionCount = $expectedCount;
    }

    private function assertInvalidValueExceptionMessage(int $exceptionIndex, string $expectedMessage): void
    {
        $this->invalidValueExceptionMessage[] = [$exceptionIndex, $expectedMessage];
    }

    private function assertInvalidValueExceptionPropertyPath(int $exceptionIndex, string $expectedPropertyPath): void
    {
        $this->invalidValueExceptionPropertyPath[] = [$exceptionIndex, $expectedPropertyPath];
    }

    private function assertInvalidValueExceptionErrorCode(int $exceptionIndex, string $expectedErrorCode): void
    {
        $this->invalidValueExceptionErrorCode[] = [$exceptionIndex, $expectedErrorCode];
    }

    protected function runTest(): void
    {
        $invalidDataExceptionHandled = false;

        try {
            parent::runTest();
        } catch (InvalidDataException $invalidDataException) {
            $invalidDataExceptionMessages = [];
            foreach ($invalidDataException->getExceptions() as $invalidValueException) {
                $invalidDataExceptionMessages[] = sprintf(
                    '[%s] %s',
                    $invalidValueException->getPropertyPath(),
                    $invalidValueException->getMessage(),
                );
            }

            if (isset($this->invalidValueExceptionCount)) {
                $invalidDataExceptionHandled = true;
                $this->assertCount(
                    $this->invalidValueExceptionCount,
                    $invalidDataException->getExceptions(),
                    \join(\PHP_EOL, $invalidDataExceptionMessages),
                );
            }

            foreach ($this->invalidValueExceptionMessage as [$index, $invalidValueExceptionMessage]) {
                $invalidDataExceptionHandled = true;
                $this->assertArrayHasKey($index, $invalidDataException->getExceptions());
                $this->assertSame(
                    $invalidValueExceptionMessage,
                    $invalidDataException->getExceptions()[$index]->getMessage(),
                );
            }

            foreach ($this->invalidValueExceptionPropertyPath as [$index, $invalidValueExceptionPropertyPath]) {
                $invalidDataExceptionHandled = true;
                $this->assertArrayHasKey($index, $invalidDataException->getExceptions());
                $this->assertSame(
                    $invalidValueExceptionPropertyPath,
                    $invalidDataException->getExceptions()[$index]->getPropertyPath(),
                );
            }

            foreach ($this->invalidValueExceptionErrorCode as [$index, $invalidValueExceptionErrorCode]) {
                $invalidDataExceptionHandled = true;
                $this->assertArrayHasKey($index, $invalidDataException->getExceptions());
                $this->assertSame(
                    $invalidValueExceptionErrorCode,
                    $invalidDataException->getExceptions()[$index]->getErrorCode(),
                );
            }

            if (!$invalidDataExceptionHandled) {
                throw $invalidDataException;
            }
        } finally {
            $this->invalidValueExceptionCount = null;
            $this->invalidValueExceptionMessage = [];
            $this->invalidValueExceptionPropertyPath = [];
            $this->invalidValueExceptionErrorCode = [];

            if ($invalidDataExceptionHandled) {
                $this->assertTrue(true);
            }
        }
    }
}
