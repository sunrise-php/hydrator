<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests;

use DateTimeImmutable;
use DateTimeZone;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Sunrise\Hydrator\Annotation\Alias;
use Sunrise\Hydrator\Annotation\DefaultValue;
use Sunrise\Hydrator\Annotation\Ignore;
use Sunrise\Hydrator\Annotation\Subtype;
use Sunrise\Hydrator\Dictionary\BuiltinType;
use Sunrise\Hydrator\Dictionary\ContextKey;
use Sunrise\Hydrator\Dictionary\ErrorCode;
use Sunrise\Hydrator\Dictionary\TranslationDomain;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\Hydrator;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Tests\Fixture\BooleanArrayCollection;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverter\TimestampTypeConverter;
use Sunrise\Hydrator\TypeConverterInterface;

use function date;
use function get_class;
use function join;
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
    private array $invalidValueExceptionTranslationDomain = [];

    public function testIssue25(): void
    {
        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'foo');
        $this->createHydrator()->hydrate(Fixture\Issue25::class, []);
    }

    public function testStdClassWithArrayProperty(): void
    {
        $object = new class {
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, ['value' => (object) ['foo' => 'bar']]);
        $this->assertSame(['foo' => 'bar'], $object->value);
    }

    public function testStdClassWithArrayAccessProperty(): void
    {
        $object = new class {
            public Fixture\Collection $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, ['value' => (object) ['foo' => 'bar']]);
        $this->assertSame(['foo' => 'bar'], $object->value->elements);
    }

    public function testStdClassWithAssociationProperty(): void
    {
        $object = new class {
            public Fixture\StringAssociation $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, ['value' => (object) ['value' => 'foo']]);
        $this->assertSame('foo', $object->value->value);
    }

    public function testCreateHydratorWithTypeConverters(): void
    {
        $object = new class {
            public \Closure $foo;
        };

        $typeConverter = $this->createMock(TypeConverterInterface::class);

        $typeConverter->method('castValue')->willReturnCallback(
            static function ($value, Type $type, array $path, array $context): Generator {
                if ($type->getName() === \Closure::class) {
                    yield static function () use ($value) {
                        return $value;
                    };
                }
            }
        );

        $this->createHydrator([], [$typeConverter])->hydrate($object, [
            'foo' => 'bar',
        ]);

        $this->assertSame('bar', ($object->foo)());
    }

    /**
     * @group boolean
     * @dataProvider strictBooleanDataProvider
     * @dataProvider nonStrictBooleanDataProvider
     */
    public function testHydrateBooleanProperty(array $data, bool $expected): void
    {
        $object = new class {
            public bool $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
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
        $object = new class {
            public ?bool $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group boolean
     * @dataProvider strictBooleanDataProvider
     * @dataProvider nonStrictBooleanDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalBooleanProperty(array $data, bool $expected = true): void
    {
        $object = new class {
            public bool $value = true;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group boolean
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateBooleanPropertyWithEmptyValue(array $data): void
    {
        $object = new class {
            public bool $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group boolean
     * @dataProvider notBooleanDataProvider
     */
    public function testHydrateBooleanPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public bool $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type boolean.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_BOOLEAN);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group boolean
     */
    public function testHydrateBooleanPropertyWithoutValue(): void
    {
        $object = new class {
            public bool $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group integer
     * @dataProvider strictIntegerDataProvider
     * @dataProvider nonStrictIntegerDataProvider
     */
    public function testHydrateIntegerProperty(array $data, int $expected): void
    {
        $object = new class {
            public int $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
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
        $object = new class {
            public ?int $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group integer
     * @dataProvider strictIntegerDataProvider
     * @dataProvider nonStrictIntegerDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalIntegerProperty(array $data, int $expected = 42): void
    {
        $object = new class {
            public int $value = 42;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group integer
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateIntegerPropertyWithEmptyValue(array $data): void
    {
        $object = new class {
            public int $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group integer
     * @dataProvider notIntegerDataProvider
     */
    public function testHydrateIntegerPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public int $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type integer.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_INTEGER);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group integer
     */
    public function testHydrateIntegerPropertyWithoutValue(): void
    {
        $object = new class {
            public int $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group number
     * @dataProvider strictNumberDataProvider
     * @dataProvider nonStrictNumberDataProvider
     */
    public function testHydrateNumericProperty(array $data, float $expected): void
    {
        $object = new class {
            public float $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
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
        $object = new class {
            public ?float $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group number
     * @dataProvider strictNumberDataProvider
     * @dataProvider nonStrictNumberDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalNumericProperty(array $data, float $expected = 3.14159): void
    {
        $object = new class {
            public float $value = 3.14159;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group number
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNumericPropertyWithEmptyValue(array $data): void
    {
        $object = new class {
            public float $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group number
     * @dataProvider notNumberDataProvider
     */
    public function testHydrateNumericPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public float $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type number.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_NUMBER);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group number
     */
    public function testHydrateNumericPropertyWithoutValue(): void
    {
        $object = new class {
            public float $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group string
     * @dataProvider strictStringDataProvider
     * @dataProvider nonStrictStringDataProvider
     */
    public function testHydrateStringProperty(array $data, string $expected): void
    {
        $object = new class {
            public string $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group string
     * @dataProvider strictStringDataProvider
     * @dataProvider nonStrictStringDataProvider
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNullableStringProperty(array $data, ?string $expected): void
    {
        $object = new class {
            public ?string $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group string
     * @dataProvider strictStringDataProvider
     * @dataProvider nonStrictStringDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalStringProperty(array $data, string $expected = 'default'): void
    {
        $object = new class {
            public string $value = 'default';
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group string
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateStringPropertyWithNull(array $data): void
    {
        $object = new class {
            public string $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group string
     * @dataProvider nonStrictNotStringDataProvider
     */
    public function testHydrateStringPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public string $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type string.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group string
     */
    public function testHydrateStringPropertyWithoutValue(): void
    {
        $object = new class {
            public string $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->assertInvalidValueExceptionTranslationDomain(0, TranslationDomain::HYDRATOR);
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group enum
     * @group integer-enum
     * @dataProvider integerEnumDataProvider
     */
    public function testHydrateIntegerEnumProperty(array $data, $expected): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public Fixture\IntegerEnum $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group enum
     * @group integer-enum
     * @dataProvider integerEnumDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNullableIntegerEnumProperty(array $data, $expected): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public ?Fixture\IntegerEnum $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group enum
     * @group integer-enum
     * @dataProvider integerEnumDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalIntegerEnumProperty(array $data, $expected = null): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public ?Fixture\IntegerEnum $value = null;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group enum
     * @group integer-enum
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateIntegerEnumPropertyWithEmptyValue(array $data): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public Fixture\IntegerEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group enum
     * @group integer-enum
     * @dataProvider notIntegerDataProvider
     */
    public function testHydrateIntegerEnumPropertyWithInvalidValue(array $data): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public Fixture\IntegerEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type integer.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_INTEGER);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group enum
     * @group integer-enum
     */
    public function testHydrateIntegerEnumPropertyWithUnknownValue(): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public Fixture\IntegerEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertInvalidValueExceptionMessage(0, 'This value is not a valid choice; expected values: ' . join(', ', Fixture\IntegerEnum::values()) . '.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::INVALID_CHOICE);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => 42]);
    }

    /**
     * @group enum
     * @group integer-enum
     */
    public function testHydrateIntegerEnumPropertyWithoutValue(): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public Fixture\IntegerEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group enum
     * @group string-enum
     * @dataProvider stringEnumDataProvider
     */
    public function testHydrateStringEnumProperty(array $data, $expected): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public Fixture\StringEnum $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group enum
     * @group string-enum
     * @dataProvider stringEnumDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNullableStringEnumProperty(array $data, $expected): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public ?Fixture\StringEnum $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group enum
     * @group string-enum
     * @dataProvider stringEnumDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalStringEnumProperty(array $data, $expected = null): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public ?Fixture\StringEnum $value = null;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group enum
     * @group string-enum
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateStringEnumPropertyWithEmptyValue(array $data): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public Fixture\StringEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group enum
     * @group string-enum
     * @dataProvider strictNotStringDataProvider
     */
    public function testHydrateStringEnumPropertyWithInvalidValue(array $data): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public Fixture\StringEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type string.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group enum
     * @group string-enum
     */
    public function testHydrateStringEnumPropertyWithUnknownValue(): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public Fixture\StringEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertInvalidValueExceptionMessage(0, 'This value is not a valid choice; expected values: ' . join(', ', Fixture\StringEnum::values()) . '.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::INVALID_CHOICE);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => 'foo']);
    }

    /**
     * @group enum
     * @group string-enum
     */
    public function testHydrateStringEnumPropertyWithoutValue(): void
    {
        $this->phpRequired('8.1');

        $object = new class {
            public Fixture\StringEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group array
     * @dataProvider arrayDataProvider
     */
    public function testHydrateArrayProperty(array $data, array $expected): void
    {
        $object = new class {
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group array
     * @dataProvider arrayDataProvider
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNullableArrayProperty(array $data, ?array $expected): void
    {
        $object = new class {
            public ?array $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group array
     * @dataProvider arrayDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalArrayProperty(array $data, array $expected = []): void
    {
        $object = new class {
            public array $value = [];
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value);
    }

    /**
     * @group array
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateArrayPropertyWithNull(array $data): void
    {
        $object = new class {
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group array
     * @dataProvider notArrayDataProvider
     */
    public function testHydrateArrayPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group array
     */
    public function testHydrateArrayPropertyWithoutValue(): void
    {
        $object = new class {
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group array
     * @group boolean-array
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     */
    public function testHydrateBooleanArrayProperty($element, bool $expected): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL) */
            #[Subtype(BuiltinType::BOOL)]
            public array $value;
        };

        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
        $this->assertSame([$expected], $object->value);
    }

    /**
     * @group array
     * @group boolean-array
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     * @dataProvider strictNullProvider
     * @dataProvider nonStrictNullProvider
     */
    public function testHydrateNullableBooleanArrayProperty($element, ?bool $expected): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL, allowsNull=true) */
            #[Subtype(BuiltinType::BOOL, allowsNull: true)]
            public array $value;
        };

        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
        $this->assertSame([$expected], $object->value);
    }

    /**
     * @group array
     * @group boolean-array
     * @dataProvider strictNullProvider
     * @dataProvider nonStrictNullProvider
     */
    public function testHydrateBooleanArrayPropertyWithEmptyElement($element): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL) */
            #[Subtype(BuiltinType::BOOL)]
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0');
        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
    }

    /**
     * @group array
     * @group boolean-array
     * @dataProvider notBooleanProvider
     */
    public function testHydrateBooleanArrayPropertyWithInvalidElement($element): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL) */
            #[Subtype(BuiltinType::BOOL)]
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type boolean.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_BOOLEAN);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0');
        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
    }

    /**
     * @group array
     * @group boolean-array
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     */
    public function testHydrateLimitedBooleanArrayProperty($element): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL, limit=1) */
            #[Subtype(BuiltinType::BOOL, limit: 1)]
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value is limited to 1 elements.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::ARRAY_OVERFLOW);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => [true, $element]]);
    }

    /**
     * @group array
     * @group boolean-association-array
     * @dataProvider strictBooleanDataProvider
     * @dataProvider nonStrictBooleanDataProvider
     */
    public function testHydrateBooleanAssociationArrayProperty(array $data, bool $expected): void
    {
        $object = new class {
            /**
             * @Subtype(\Sunrise\Hydrator\Tests\Fixture\BooleanAssociation::class)
             * @var non-empty-list<Fixture\BooleanAssociation>
             */
            #[Subtype(Fixture\BooleanAssociation::class)]
            public array $value;
        };

        $this->createHydrator()->hydrate($object, ['value' => [$data]]);
        $this->assertSame($expected, $object->value[0]->value);
    }

    /**
     * @group array
     * @group boolean-association-array
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateBooleanAssociationArrayPropertyWithNull(array $data): void
    {
        $object = new class {
            /**
             * @Subtype(\Sunrise\Hydrator\Tests\Fixture\BooleanAssociation::class)
             * @var non-empty-list<Fixture\BooleanAssociation>
             */
            #[Subtype(Fixture\BooleanAssociation::class)]
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group array
     * @group boolean-association-array
     * @dataProvider notArrayDataProvider
     */
    public function testHydrateBooleanAssociationArrayPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            /**
             * @Subtype(\Sunrise\Hydrator\Tests\Fixture\BooleanAssociation::class)
             * @var non-empty-list<Fixture\BooleanAssociation>
             */
            #[Subtype(Fixture\BooleanAssociation::class)]
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group array
     * @group boolean-association-array
     */
    public function testHydrateBooleanAssociationArrayPropertyWithoutValue(): void
    {
        $object = new class {
            /**
             * @Subtype(\Sunrise\Hydrator\Tests\Fixture\BooleanAssociation::class)
             * @var non-empty-list<Fixture\BooleanAssociation>
             */
            #[Subtype(Fixture\BooleanAssociation::class)]
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group array
     * @group boolean-association-array
     * @dataProvider notArrayProvider
     */
    public function testHydrateBooleanAssociationArrayPropertyWithInvalidAssociation($element): void
    {
        $object = new class {
            /**
             * @Subtype(\Sunrise\Hydrator\Tests\Fixture\BooleanAssociation::class)
             * @var non-empty-list<Fixture\BooleanAssociation>
             */
            #[Subtype(Fixture\BooleanAssociation::class)]
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0');
        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
    }

    /**
     * @group array
     * @group boolean-association-array
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateBooleanAssociationArrayPropertyWithEmptyAssociationValue(array $data): void
    {
        $object = new class {
            /**
             * @Subtype(\Sunrise\Hydrator\Tests\Fixture\BooleanAssociation::class)
             * @var non-empty-list<Fixture\BooleanAssociation>
             */
            #[Subtype(Fixture\BooleanAssociation::class)]
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0.value');
        $this->createHydrator()->hydrate($object, ['value' => [$data]]);
    }

    /**
     * @group array
     * @group boolean-association-array
     * @dataProvider notBooleanDataProvider
     */
    public function testHydrateBooleanAssociationArrayPropertyWithInvalidAssociationValue(array $data): void
    {
        $object = new class {
            /**
             * @Subtype(\Sunrise\Hydrator\Tests\Fixture\BooleanAssociation::class)
             * @var non-empty-list<Fixture\BooleanAssociation>
             */
            #[Subtype(Fixture\BooleanAssociation::class)]
            public array $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type boolean.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_BOOLEAN);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0.value');
        $this->createHydrator()->hydrate($object, ['value' => [$data]]);
    }

    /**
     * @group array-access
     * @dataProvider arrayDataProvider
     */
    public function testHydrateArrayAccessProperty(array $data, array $expected): void
    {
        $object = new class {
            public Fixture\Collection $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value->elements);
    }

    /**
     * @group array-access
     * @dataProvider arrayDataProvider
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateNullableArrayAccessProperty(array $data, ?array $expected): void
    {
        $object = new class {
            public ?Fixture\Collection $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value->elements ?? null);
    }

    /**
     * @group array-access
     * @dataProvider arrayDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalArrayAccessProperty(array $data, array $expected = []): void
    {
        $object = new class {
            public ?Fixture\Collection $value = null;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value->elements ?? []);
    }

    /**
     * @group array-access
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateArrayAccessPropertyWithNull(array $data): void
    {
        $object = new class {
            public Fixture\Collection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group array-access
     * @dataProvider notArrayDataProvider
     */
    public function testHydrateArrayAccessPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public Fixture\Collection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group array-access
     */
    public function testHydrateArrayAccessPropertyWithoutValue(): void
    {
        $object = new class {
            public Fixture\Collection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group array-access
     */
    public function testHydrateOverflowedArrayAccessProperty(): void
    {
        $object = new class {
            public Fixture\LimitedCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value is limited to 1 elements.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::ARRAY_OVERFLOW);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => ['foo', 'bar']]);
    }

    /**
     * @group array-access
     */
    public function testHydrateUnstantiableArrayAccessProperty(): void
    {
        $object = new class {
            public Fixture\UnstantiableCollection $value;
        };

        $this->expectException(InvalidObjectException::class);
        $this->createHydrator()->hydrate($object, ['value' => []]);
    }

    /**
     * @group array-access
     * @group annotated-boolean-array-access
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     */
    public function testHydrateAnnotatedBooleanArrayAccessProperty($element, bool $expected): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL) */
            #[Subtype(BuiltinType::BOOL)]
            public Fixture\Collection $value;
        };

        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
        $this->assertSame([$expected], $object->value->elements);
    }

    /**
     * @group array-access
     * @group annotated-boolean-array-access
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     * @dataProvider strictNullProvider
     * @dataProvider nonStrictNullProvider
     */
    public function testHydrateAnnotatedNullableBooleanArrayAccessProperty($element, ?bool $expected): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL, allowsNull=true) */
            #[Subtype(BuiltinType::BOOL, allowsNull: true)]
            public Fixture\Collection $value;
        };

        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
        $this->assertSame([$expected], $object->value->elements);
    }

    /**
     * @group array-access
     * @group annotated-boolean-array-access
     * @dataProvider strictNullProvider
     * @dataProvider nonStrictNullProvider
     */
    public function testHydrateAnnotatedBooleanArrayAccessPropertyWithEmptyElement($element): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL) */
            #[Subtype(BuiltinType::BOOL)]
            public Fixture\Collection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0');
        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
    }

    /**
     * @group array-access
     * @group annotated-boolean-array-access
     * @dataProvider notBooleanProvider
     */
    public function testHydrateAnnotatedBooleanArrayAccessPropertyWithInvalidElement($element): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL) */
            #[Subtype(BuiltinType::BOOL)]
            public Fixture\Collection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type boolean.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_BOOLEAN);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0');
        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
    }

    /**
     * @group array-access
     * @group annotated-boolean-array-access
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     */
    public function testHydrateAnnotatedLimitedBooleanArrayAccessProperty($element): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL, limit=1) */
            #[Subtype(BuiltinType::BOOL, limit: 1)]
            public Fixture\Collection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value is limited to 1 elements.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::ARRAY_OVERFLOW);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => [true, $element]]);
    }

    /**
     * @group array-access
     * @group annotated-boolean-array-access
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     */
    public function testHydrateAnnotatedOverflowedBooleanArrayAccessProperty($element): void
    {
        $object = new class {
            /** @Subtype(BuiltinType::BOOL) */
            #[Subtype(BuiltinType::BOOL)]
            public Fixture\LimitedCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value is limited to 1 elements.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::ARRAY_OVERFLOW);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => [true, $element]]);
    }

    /**
     * @group array-access
     * @group typed-boolean-array-access
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     */
    public function testHydrateTypedBooleanArrayAccessProperty($element, bool $expected): void
    {
        $object = new class {
            public Fixture\BooleanCollection $value;
        };

        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
        $this->assertSame([$expected], $object->value->elements);
    }

    /**
     * @group array-access
     * @group typed-boolean-array-access
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     * @dataProvider strictNullProvider
     * @dataProvider nonStrictNullProvider
     */
    public function testHydrateTypedNullableBooleanArrayAccessProperty($element, ?bool $expected): void
    {
        $object = new class {
            public Fixture\NullableBooleanCollection $value;
        };

        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
        $this->assertSame([$expected], $object->value->elements);
    }

    /**
     * @group array-access
     * @group typed-boolean-array-access
     * @dataProvider strictNullProvider
     * @dataProvider nonStrictNullProvider
     */
    public function testHydrateTypedBooleanArrayAccessPropertyWithEmptyElement($element): void
    {
        $object = new class {
            public Fixture\BooleanCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0');
        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
    }

    /**
     * @group array-access
     * @group typed-boolean-array-access
     * @dataProvider notBooleanProvider
     */
    public function testHydrateTypedBooleanArrayAccessPropertyWithInvalidElement($element): void
    {
        $object = new class {
            public Fixture\BooleanCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type boolean.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_BOOLEAN);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0');
        $this->createHydrator()->hydrate($object, ['value' => [$element]]);
    }

    /**
     * @group array-access
     * @group typed-boolean-array-access
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     */
    public function testHydrateTypedLimitedBooleanArrayAccessProperty($element): void
    {
        $object = new class {
            public Fixture\LimitedCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value is limited to 1 elements.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::ARRAY_OVERFLOW);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => [true, $element]]);
    }

    /**
     * @group array-access
     * @group typed-boolean-array-access
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     */
    public function testHydrateTypedOverflowedBooleanArrayAccessProperty($element): void
    {
        $object = new class {
            public Fixture\LimitedBooleanCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value is limited to 1 elements.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::ARRAY_OVERFLOW);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => [true, $element]]);
    }

    /**
     * @group array-access
     * @group boolean-association-array-access
     * @dataProvider strictBooleanDataProvider
     * @dataProvider nonStrictBooleanDataProvider
     */
    public function testHydrateBooleanAssociationArrayAccessProperty(array $data, bool $expected): void
    {
        $object = new class {
            public Fixture\BooleanAssociationCollection $value;
        };

        $this->createHydrator()->hydrate($object, ['value' => [$data]]);
        $this->assertSame($expected, $object->value[0]->value);
    }

    /**
     * @group array-access
     * @group boolean-association-array-access
     * @dataProvider strictNullDataProvider
     */
    public function testHydrateBooleanAssociationArrayAccessPropertyWithNull(array $data): void
    {
        $object = new class {
            public Fixture\BooleanAssociationCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group array-access
     * @group boolean-association-array-access
     * @dataProvider notArrayDataProvider
     */
    public function testHydrateBooleanAssociationArrayAccessPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public Fixture\BooleanAssociationCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group array-access
     * @group boolean-association-array-access
     */
    public function testHydrateBooleanAssociationArrayAccessPropertyWithoutValue(): void
    {
        $object = new class {
            public Fixture\BooleanAssociationCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group array-access
     * @group boolean-association-array-access
     * @dataProvider notArrayProvider
     */
    public function testHydrateBooleanAssociationArrayAccessPropertyWithInvalidAssociation($actual): void
    {
        $object = new class {
            public Fixture\BooleanAssociationCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0');
        $this->createHydrator()->hydrate($object, ['value' => [$actual]]);
    }

    /**
     * @group array-access
     * @group boolean-association-array-access
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateBooleanAssociationArrayAccessPropertyWithEmptyAssociationValue(array $data): void
    {
        $object = new class {
            public Fixture\BooleanAssociationCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0.value');
        $this->createHydrator()->hydrate($object, ['value' => [$data]]);
    }

    /**
     * @group array-access
     * @group boolean-association-array-access
     * @dataProvider notBooleanDataProvider
     */
    public function testHydrateBooleanAssociationArrayAccessPropertyWithInvalidAssociationValue(array $data): void
    {
        $object = new class {
            public Fixture\BooleanAssociationCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type boolean.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_BOOLEAN);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0.value');
        $this->createHydrator()->hydrate($object, ['value' => [$data]]);
    }

    /**
     * @group array-access
     */
    public function testHydrateBooleanArrayCollectionParameterWithValidData(): void
    {
        $this->phpRequired('8.0');

        $object = new class {
            public BooleanArrayCollection $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, ['value' => [[true]]]);
        $this->assertSame([[true]], (array) $object->value);
    }

    /**
     * @group array-access
     */
    public function testHydrateBooleanArrayCollectionParameterWithInvalidData(): void
    {
        $this->phpRequired('8.0');

        $object = new class {
            public BooleanArrayCollection $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type boolean.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_BOOLEAN);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.0.0');
        $this->createHydrator()->hydrate($object, ['value' => [['foo']]]);
    }

    /**
     * @group association
     */
    public function testHydrateUnstantiableAssociationProperty(): void
    {
        $object = new class {
            public Fixture\UnstantiableObject $value;
        };

        $this->expectException(InvalidObjectException::class);
        $this->createHydrator()->hydrate($object, ['value' => 'foo']);
    }

    /**
     * @group association
     * @group boolean-association
     * @dataProvider strictBooleanDataProvider
     * @dataProvider nonStrictBooleanDataProvider
     */
    public function testHydrateBooleanAssociationProperty(array $data, bool $expected): void
    {
        $object = new class {
            public Fixture\BooleanAssociation $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, ['value' => $data]);
        $this->assertSame($expected, $object->value->value);
    }

    /**
     * @group association
     * @group boolean-association
     */
    public function testHydrateNullableBooleanAssociationProperty(): void
    {
        $object = new class {
            public ?Fixture\BooleanAssociation $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, ['value' => null]);
        $this->assertNull($object->value);
    }

    /**
     * @group association
     * @group boolean-association
     */
    public function testHydrateOptionalBooleanAssociationProperty(): void
    {
        $object = new class {
            public ?Fixture\BooleanAssociation $value = null;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, []);
        $this->assertNull($object->value);
    }

    /**
     * @group association
     * @group boolean-association
     */
    public function testHydrateBooleanAssociationPropertyWithNull(): void
    {
        $object = new class {
            public Fixture\BooleanAssociation $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => null]);
    }

    /**
     * @group association
     * @group boolean-association
     * @dataProvider notArrayDataProvider
     */
    public function testHydrateBooleanAssociationPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public Fixture\BooleanAssociation $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type array.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_ARRAY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group association
     * @group boolean-association
     */
    public function testHydrateBooleanAssociationPropertyWithoutValue(): void
    {
        $object = new class {
            public Fixture\BooleanAssociation $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group association
     * @group boolean-association
     */
    public function testHydrateBooleanAssociationPropertyWithEmptyAssociation(): void
    {
        $object = new class {
            public Fixture\BooleanAssociation $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.value');
        $this->createHydrator()->hydrate($object, ['value' => []]);
    }

    /**
     * @group association
     * @group boolean-association
     * @dataProvider notBooleanDataProvider
     */
    public function testHydrateBooleanAssociationPropertyWithInvalidAssociationValue(array $data): void
    {
        $object = new class {
            public Fixture\BooleanAssociation $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type boolean.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_BOOLEAN);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value.value');
        $this->createHydrator()->hydrate($object, ['value' => $data]);
    }

    /**
     * @group timestamp
     * @dataProvider timestampDataProvider
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateTimestampProperty(array $data, string $expected, ?string $format = null, ?string $timezone = null): void
    {
        $object = new class {
            public DateTimeImmutable $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        // phpcs:ignore Generic.Files.LineLength
        $this->createHydrator([ContextKey::TIMESTAMP_FORMAT => $format, ContextKey::TIMEZONE => $timezone])->hydrate($object, $data);
        $this->assertSame($expected, $object->value->format($format ?? TimestampTypeConverter::DEFAULT_FORMAT));
    }

    /**
     * @group timestamp
     * @dataProvider timestampDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateNullableTimestampProperty(array $data, ?string $expected, ?string $format = null, ?string $timezone = null): void
    {
        $object = new class {
            public ?DateTimeImmutable $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        // phpcs:ignore Generic.Files.LineLength
        $this->createHydrator([ContextKey::TIMESTAMP_FORMAT => $format, ContextKey::TIMEZONE => $timezone])->hydrate($object, $data);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertSame($expected, isset($object->value) ? $object->value->format($format ?? TimestampTypeConverter::DEFAULT_FORMAT) : null);
    }

    /**
     * @group timestamp
     * @dataProvider timestampDataProvider
     * @dataProvider emptyArrayProvider
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateOptionalTimestampProperty(array $data, ?string $expected = null, ?string $format = null, ?string $timezone = null): void
    {
        $object = new class {
            public ?DateTimeImmutable $value = null;
        };

        $this->assertInvalidValueExceptionCount(0);
        // phpcs:ignore Generic.Files.LineLength
        $this->createHydrator([ContextKey::TIMESTAMP_FORMAT => $format, ContextKey::TIMEZONE => $timezone])->hydrate($object, $data);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertSame($expected, isset($object->value) ? $object->value->format($format ?? TimestampTypeConverter::DEFAULT_FORMAT) : null);
    }

    /**
     * @group timestamp
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateTimestampPropertyWithEmptyValue(array $data): void
    {
        $object = new class {
            public DateTimeImmutable $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group timestamp
     * @dataProvider invalidTimestampDataProvider
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateTimestampPropertyWithInvalidValue(array $data, ?string $format, string $errorCode, string $errorMessage): void
    {
        $object = new class {
            public DateTimeImmutable $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, $errorMessage);
        $this->assertInvalidValueExceptionErrorCode(0, $errorCode);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator([ContextKey::TIMESTAMP_FORMAT => $format])->hydrate($object, $data);
    }

    /**
     * @group timestamp
     */
    public function testHydrateTimestampPropertyWithoutValue(): void
    {
        $object = new class {
            public DateTimeImmutable $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group timestamp
     * @dataProvider timestampDataProvider
     */
    // phpcs:ignore Generic.Files.LineLength
    public function testHydrateOverriddenDateTimeImmutable(array $data, string $expected, ?string $format = null, ?string $timezone = null): void
    {
        $this->phpRequired('8.0');

        $object = new class {
            public Fixture\OverriddenDateTimeImmutable $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        // phpcs:ignore Generic.Files.LineLength
        $this->createHydrator([ContextKey::TIMESTAMP_FORMAT => $format, ContextKey::TIMEZONE => $timezone])->hydrate($object, $data);
        $this->assertSame($expected, $object->value->format($format ?? TimestampTypeConverter::DEFAULT_FORMAT));
    }

    /**
     * @group timezone
     * @dataProvider timezoneDataProvider
     */
    public function testHydrateTimezoneProperty(array $data, string $expected): void
    {
        $object = new class {
            public DateTimeZone $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value->getName());
    }

    /**
     * @group timezone
     * @dataProvider timezoneDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNullableTimezoneProperty(array $data, ?string $expected): void
    {
        $object = new class {
            public ?DateTimeZone $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, isset($object->value) ? $object->value->getName() : null);
    }

    /**
     * @group timezone
     * @dataProvider timezoneDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalTimezoneProperty(array $data, ?string $expected = null): void
    {
        $object = new class {
            public ?DateTimeZone $value = null;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, isset($object->value) ? $object->value->getName() : null);
    }

    /**
     * @group timezone
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateTimezonePropertyWithEmptyValue(array $data): void
    {
        $object = new class {
            public DateTimeZone $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group timezone
     * @dataProvider strictNotStringDataProvider
     */
    public function testHydrateTimezonePropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public DateTimeZone $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type string.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group timezone
     */
    public function testHydrateTimezonePropertyWithUnknownValue(): void
    {
        $object = new class {
            public DateTimeZone $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value is not a valid timezone.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::INVALID_TIMEZONE);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => 'Jupiter/Europa']);
    }

    /**
     * @group timezone
     */
    public function testHydrateTimezonePropertyWithoutValue(): void
    {
        $object = new class {
            public DateTimeZone $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group myclabs-enum
     * @dataProvider myclabsEnumDataProvider
     */
    public function testHydrateMyclabsEnumProperty(array $data, $expected): void
    {
        $object = new class {
            public Fixture\MyclabsEnum $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertEquals($expected, $object->value);
    }

    /**
     * @group myclabs-enum
     * @dataProvider myclabsEnumDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNullableMyclabsEnumProperty(array $data, $expected): void
    {
        $object = new class {
            public ?Fixture\MyclabsEnum $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertEquals($expected, $object->value);
    }

    /**
     * @group myclabs-enum
     * @dataProvider myclabsEnumDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalMyclabsEnumProperty(array $data, $expected = null): void
    {
        $object = new class {
            public ?Fixture\MyclabsEnum $value = null;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertEquals($expected, $object->value);
    }

    /**
     * @group myclabs-enum
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateMyclabsEnumPropertyWithEmptyValue(array $data): void
    {
        $object = new class {
            public Fixture\MyclabsEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group myclabs-enum
     */
    public function testHydrateMyclabsEnumPropertyWithUnknownValue(): void
    {
        $object = new class {
            public Fixture\MyclabsEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertInvalidValueExceptionMessage(0, 'This value is not a valid choice; expected values: ' . join(', ', Fixture\MyclabsEnum::toArray()) . '.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::INVALID_CHOICE);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => 'foo']);
    }

    /**
     * @group myclabs-enum
     */
    public function testHydrateMyclabsEnumPropertyWithoutValue(): void
    {
        $object = new class {
            public Fixture\MyclabsEnum $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group ramsey-uuid
     * @dataProvider uuidDataProvider
     */
    public function testHydrateRamseyUuidProperty(array $data, string $expected): void
    {
        $object = new class {
            public \Ramsey\Uuid\UuidInterface $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value->toString());
    }

    /**
     * @group ramsey-uuid
     * @dataProvider uuidDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNullableRamseyUuidProperty(array $data, ?string $expected): void
    {
        $object = new class {
            public ?\Ramsey\Uuid\UuidInterface $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, isset($object->value) ? $object->value->toString() : null);
    }

    /**
     * @group ramsey-uuid
     * @dataProvider uuidDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalRamseyUuidProperty(array $data, ?string $expected = null): void
    {
        $object = new class {
            public ?\Ramsey\Uuid\UuidInterface $value = null;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, isset($object->value) ? $object->value->toString() : null);
    }

    /**
     * @group ramsey-uuid
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateRamseyUuidPropertyWithEmptyValue(array $data): void
    {
        $object = new class {
            public \Ramsey\Uuid\UuidInterface $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group ramsey-uuid
     * @dataProvider strictNotStringDataProvider
     */
    public function testHydrateRamseyUuidPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public \Ramsey\Uuid\UuidInterface $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type string.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group ramsey-uuid
     */
    public function testHydrateRamseyUuidPropertyWithInvalidUuid(): void
    {
        $object = new class {
            public \Ramsey\Uuid\UuidInterface $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value is not a valid UID.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::INVALID_UID);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => 'foo']);
    }

    /**
     * @group ramsey-uuid
     */
    public function testHydrateRamseyUuidPropertyWithoutValue(): void
    {
        $object = new class {
            public \Ramsey\Uuid\UuidInterface $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group symfony-uuid
     * @dataProvider uuidDataProvider
     */
    public function testHydrateSymfonyUuidProperty(array $data, string $expected): void
    {
        $object = new class {
            public \Symfony\Component\Uid\UuidV4 $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, $object->value->toRfc4122());
    }

    /**
     * @group symfony-uuid
     * @dataProvider uuidDataProvider
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateNullableSymfonyUuidProperty(array $data, ?string $expected): void
    {
        $object = new class {
            public ?\Symfony\Component\Uid\UuidV4 $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, isset($object->value) ? $object->value->toRfc4122() : null);
    }

    /**
     * @group symfony-uuid
     * @dataProvider uuidDataProvider
     * @dataProvider emptyArrayProvider
     */
    public function testHydrateOptionalSymfonyUuidProperty(array $data, ?string $expected = null): void
    {
        $object = new class {
            public ?\Symfony\Component\Uid\UuidV4 $value = null;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, $data);
        $this->assertSame($expected, isset($object->value) ? $object->value->toRfc4122() : null);
    }

    /**
     * @group symfony-uuid
     * @dataProvider strictNullDataProvider
     * @dataProvider nonStrictNullDataProvider
     */
    public function testHydrateSymfonyUuidPropertyWithEmptyValue(array $data): void
    {
        $object = new class {
            public \Symfony\Component\Uid\UuidV4 $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must not be empty.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_NOT_BE_EMPTY);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group symfony-uuid
     * @dataProvider strictNotStringDataProvider
     */
    public function testHydrateSymfonyUuidPropertyWithInvalidValue(array $data): void
    {
        $object = new class {
            public \Symfony\Component\Uid\UuidV4 $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be of type string.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_STRING);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, $data);
    }

    /**
     * @group symfony-uuid
     */
    public function testHydrateSymfonyUuidPropertyWithInvalidUuid(): void
    {
        $object = new class {
            public \Symfony\Component\Uid\UuidV4 $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value is not a valid UID.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::INVALID_UID);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, ['value' => 'foo']);
    }

    /**
     * @group symfony-uuid
     */
    public function testHydrateSymfonyUuidPropertyWithoutValue(): void
    {
        $object = new class {
            public \Symfony\Component\Uid\UuidV4 $value;
        };

        $this->assertInvalidValueExceptionCount(1);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
        $this->createHydrator()->hydrate($object, []);
    }

    /**
     * @group json
     */
    public function testHydrateObjectWithJson(): void
    {
        $object = new class {
            public string $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrateWithJson($object, '{"value": "foo"}');
        $this->assertSame('foo', $object->value);
    }

    /**
     * @group json
     */
    public function testHydrateObjectWithInvalidJson(): void
    {
        $object = new class {
            public string $value;
        };

        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessageMatches('/^The JSON is invalid and couldn‘t be decoded due to: .+$/');
        $this->createHydrator()->hydrateWithJson($object, '[[]]', 0, 1);
    }

    /**
     * @group json
     */
    public function testHydrateObjectWithNonObjectableJson(): void
    {
        $object = new class {
            public string $value;
        };

        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage('The JSON must be in the form of an array or an object.');
        $this->createHydrator()->hydrateWithJson($object, 'null');
    }

    public function testInvalidObjectExceptionUnsupportedMethodParameterType(): void
    {
        $class = $this->createMock(ReflectionClass::class);
        $class->method('getName')->willReturn('foo');

        $method = $this->createMock(ReflectionMethod::class);
        $method->method('getDeclaringClass')->willReturn($class);
        $method->method('getName')->willReturn('bar');

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->method('getDeclaringClass')->willReturn($class);
        $parameter->method('getDeclaringFunction')->willReturn($method);
        $parameter->method('getName')->willReturn('baz');

        $type = new Type($parameter, 'mixed', false);

        $e = InvalidObjectException::unsupportedParameterType($type, $parameter);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertSame('The parameter {foo::bar($baz[0])} is associated with an unsupported type {mixed}.', $e->getMessage());
    }

    public function testInvalidObjectExceptionUnsupportedFunctionParameterType(): void
    {
        $function = $this->createMock(ReflectionFunction::class);
        $function->method('getName')->willReturn('foo');

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->method('getDeclaringFunction')->willReturn($function);
        $parameter->method('getName')->willReturn('bar');

        $type = new Type($parameter, 'mixed', false);

        $e = InvalidObjectException::unsupportedParameterType($type, $parameter);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertSame('The parameter {foo($bar[0])} is associated with an unsupported type {mixed}.', $e->getMessage());
    }

    public function testTypeFromParameter(): void
    {
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->method('getType')->willReturn(null);
        $type = Type::fromParameter($parameter);
        $this->assertSame(BuiltinType::MIXED, $type->getName());
        $this->assertTrue($type->allowsNull());

        $namedType = $this->createMock(ReflectionNamedType::class);
        $namedType->method('getName')->willReturn('foo');
        $namedType->method('allowsNull')->willReturn(false);
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->method('getType')->willReturn($namedType);
        $type = Type::fromParameter($parameter);
        $this->assertSame('foo', $type->getName());
        $this->assertFalse($type->allowsNull());

        $namedType = $this->createMock(ReflectionNamedType::class);
        $namedType->method('getName')->willReturn('foo');
        $namedType->method('allowsNull')->willReturn(true);
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->method('getType')->willReturn($namedType);
        $type = Type::fromParameter($parameter);
        $this->assertSame('foo', $type->getName());
        $this->assertTrue($type->allowsNull());

        if (PHP_VERSION_ID < 80000) {
            return;
        }

        $namedTypes = [];
        $namedTypes[0] = $this->createMock(ReflectionNamedType::class);
        $namedTypes[0]->method('getName')->willReturn('foo');
        $namedTypes[1] = $this->createMock(ReflectionNamedType::class);
        $namedTypes[1]->method('getName')->willReturn('bar');
        $unionType = $this->createMock(ReflectionUnionType::class);
        $unionType->method('getTypes')->willReturn($namedTypes);
        $unionType->method('allowsNull')->willReturn(false);
        $unionType->method('__toString')->willReturn('foo|bar');
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->method('getType')->willReturn($unionType);
        $type = Type::fromParameter($parameter);
        $this->assertSame('foo|bar', $type->getName());
        $this->assertFalse($type->allowsNull());

        $namedTypes = [];
        $namedTypes[0] = $this->createMock(ReflectionNamedType::class);
        $namedTypes[0]->method('getName')->willReturn('foo');
        $namedTypes[1] = $this->createMock(ReflectionNamedType::class);
        $namedTypes[1]->method('getName')->willReturn('bar');
        $unionType = $this->createMock(ReflectionUnionType::class);
        $unionType->method('getTypes')->willReturn($namedTypes);
        $unionType->method('allowsNull')->willReturn(true);
        $unionType->method('__toString')->willReturn('foo|bar|null');
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->method('getType')->willReturn($unionType);
        $type = Type::fromParameter($parameter);
        $this->assertSame('foo|bar|null', $type->getName());
        $this->assertTrue($type->allowsNull());

        if (PHP_VERSION_ID < 80100) {
            return;
        }

        $namedTypes = [];
        $namedTypes[0] = $this->createMock(ReflectionNamedType::class);
        $namedTypes[0]->method('getName')->willReturn('foo');
        $namedTypes[1] = $this->createMock(ReflectionNamedType::class);
        $namedTypes[1]->method('getName')->willReturn('bar');
        $intersectionType = $this->createMock(ReflectionIntersectionType::class);
        $intersectionType->method('getTypes')->willReturn($namedTypes);
        $intersectionType->method('allowsNull')->willReturn(false);
        $intersectionType->method('__toString')->willReturn('foo&bar');
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->method('getType')->willReturn($intersectionType);
        $type = Type::fromParameter($parameter);
        $this->assertSame('foo&bar', $type->getName());
        $this->assertFalse($type->allowsNull());

        $namedTypes = [];
        $namedTypes[0] = $this->createMock(ReflectionNamedType::class);
        $namedTypes[0]->method('getName')->willReturn('foo');
        $namedTypes[1] = $this->createMock(ReflectionNamedType::class);
        $namedTypes[1]->method('getName')->willReturn('bar');
        $intersectionType = $this->createMock(ReflectionIntersectionType::class);
        $intersectionType->method('getTypes')->willReturn($namedTypes);
        $intersectionType->method('allowsNull')->willReturn(true);
        $intersectionType->method('__toString')->willReturn('(foo&bar)|null');
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->method('getType')->willReturn($intersectionType);
        $type = Type::fromParameter($parameter);
        $this->assertSame('(foo&bar)|null', $type->getName());
        $this->assertTrue($type->allowsNull());
    }

    public function testHydrateStore(): void
    {
        $this->phpRequired('8.1');

        $sold = Fixture\Store\Status::SOLD;

        $data = [
            'name' => 'Pear',
            'category' => [
                'name' => 'Vegetables',
            ],
            'tags' => [
                [
                    'name' => 'foo',
                ],
                [
                    'name' => 'bar',
                ],
            ],
            'status' => $sold->value,
        ];

        $this->assertInvalidValueExceptionCount(0);
        $product = $this->createHydrator()->hydrate(Fixture\Store\Product::class, $data);
        $this->assertSame('Pear', $product->name);
        $this->assertSame('Vegetables', $product->category->name);
        $this->assertCount(2, $product->tags);
        $this->assertArrayHasKey(0, $product->tags);
        $this->assertSame('foo', $product->tags[0]->name);
        $this->assertArrayHasKey(1, $product->tags);
        $this->assertSame('bar', $product->tags[1]->name);
        $this->assertSame($sold, $product->status);
        $this->assertSame('2020-01-01 12:00:00', $product->createdAt->format('Y-m-d H:i:s'));
    }

    public function testUnknownObject(): void
    {
        $this->expectException(InvalidObjectException::class);
        $this->createHydrator()->hydrate(\Unknown::class, []);
    }

    public function testUnstantiableObject(): void
    {
        $this->expectException(InvalidObjectException::class);
        $this->createHydrator()->hydrate(Fixture\UnstantiableObject::class, []);
    }

    public function testStaticalProperty(): void
    {
        $object = new class {
            public static string $value = 'foo';
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, ['value' => 'bar']);
        $this->assertSame('foo', $object::$value);
    }

    public function testIgnoredProperty(): void
    {
        $object = new class {
            /** @Ignore() */
            #[Ignore]
            public string $value = 'foo';
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, ['value' => 'bar']);
        $this->assertSame('foo', $object->value);
    }

    public function testAliasedProperty(): void
    {
        $proto = new class {
            /** @Alias("alias") */
            #[Alias('alias')]
            public string $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $object = $this->createHydrator()->hydrate(get_class($proto), ['alias' => 'foo']);
        $this->assertSame('foo', $object->value);

        $this->assertInvalidValueExceptionCount(1);
        $this->createHydrator()->hydrate(get_class($proto), ['value' => 'foo']);
        $this->assertInvalidValueExceptionMessage(0, 'This value must be provided.');
        $this->assertInvalidValueExceptionErrorCode(0, ErrorCode::MUST_BE_PROVIDED);
        $this->assertInvalidValueExceptionPropertyPath(0, 'value');
    }

    public function testDefaultValuedProperty(): void
    {
        $object = new class {
            /** @DefaultValue("foo") */
            #[DefaultValue('foo')]
            public string $value;
        };

        $this->assertInvalidValueExceptionCount(0);
        $this->createHydrator()->hydrate($object, []);
        $this->assertSame('foo', $object->value);
    }

    public function testUntypedProperty(): void
    {
        $object = new class {
            public $value;
        };

        $this->createHydrator()->hydrate($object, ['value' => 'foo']);
        $this->assertSame('foo', $object->value);
    }

    public function testUnsupportedPropertyType(): void
    {
        $object = new class {
            public balalaika $value;
        };

        $this->expectException(InvalidObjectException::class);
        $this->createHydrator()->hydrate($object, ['value' => 'foo']);
    }

    public function testSymfonyViolations(): void
    {
        $violations = null;

        try {
            $this->createHydrator()->hydrate(new class {
                public string $value;
            }, []);
        } catch (InvalidDataException $e) {
            $violations = $e->getViolations();
        }

        $this->assertNotNull($violations);
        $this->assertCount(1, $violations);
        $this->assertTrue($violations->has(0));
        $this->assertSame(ErrorCode::MUST_BE_PROVIDED, $violations->get(0)->getCode());
        $this->assertSame('This value must be provided.', $violations->get(0)->getMessage());
        $this->assertSame('value', $violations->get(0)->getPropertyPath());
    }

    /**
     * @dataProvider strictBooleanProvider
     * @dataProvider nonStrictBooleanProvider
     */
    public function testSourcelessType($element, bool $expected): void
    {
        $type = Type::fromName(BuiltinType::BOOL);

        $this->assertSame($expected, $this->createHydrator()->castValue($element, $type));
    }

    public function strictNullProvider(): Generator
    {
        yield [null, null];
    }

    public function nonStrictNullProvider(): Generator
    {
        yield ['', null];
        yield [' ', null];
    }

    public function strictBooleanProvider(): Generator
    {
        yield [true, true];
        yield [false, false];
    }

    public function nonStrictBooleanProvider(): Generator
    {
        yield ['1', true];
        yield ['0', false];
        yield ['true', true];
        yield ['false', false];
        yield ['yes', true];
        yield ['no', false];
        yield ['on', true];
        yield ['off', false];
    }

    public function notBooleanProvider(): Generator
    {
        yield [0];
        yield [1];
        yield [42];
        yield [3.14159];
        yield ['foo'];
        yield [[]];
    }

    public function strictIntegerProvider(): Generator
    {
        yield [-1, -1];
        yield [0, 0];
        yield [1, 1];
    }

    public function nonStrictIntegerProvider(): Generator
    {
        yield ['-1', -1];
        yield ['0', 0];
        yield ['1', 1];
        yield ['+1', 1];
    }

    public function notIntegerProvider(): Generator
    {
        yield [true];
        yield [false];
        yield [3.14159];
        yield ['foo'];
        yield [[]];
    }

    public function strictNumberProvider(): Generator
    {
        yield [-1, -1.];
        yield [0, 0.];
        yield [1, 1.];
        yield [-1., -1.];
        yield [0., 0.];
        yield [1., 1.];
        yield [-.1, -.1];
        yield [.0, .0];
        yield [.1, .1];
    }

    public function nonStrictNumberProvider(): Generator
    {
        yield ['-1', -1.];
        yield ['0', 0.];
        yield ['1', 1.];
        yield ['+1', 1.];
        yield ['-1.', -1.];
        yield ['0.', 0.];
        yield ['1.', 1.];
        yield ['+1.', 1.];
        yield ['-.1', -.1];
        yield ['.0', .0];
        yield ['.1', .1];
        yield ['+.1', .1];
        yield ['-1.0', -1.];
        yield ['0.0', 0.];
        yield ['1.0', 1.];
        yield ['+1.0', 1.];
        yield ['1e-1', .1];
        yield ['1e1', 10.];
        yield ['1e+1', 10.];
        yield ['1.e-1', .1];
        yield ['1.e1', 10.];
        yield ['1.e+1', 10.];
        yield ['.1e-1', .01];
        yield ['.1e1', 1.];
        yield ['.1e+1', 1.];
        yield ['1.0e-1', .1];
        yield ['1.0e1', 10.];
        yield ['1.0e+1', 10.];
    }

    public function notNumberProvider(): Generator
    {
        yield [true];
        yield [false];
        yield ['foo'];
        yield [[]];
    }

    public function strictStringProvider(): Generator
    {
        yield ['foo', 'foo'];

        // Must not be cast to a null
        yield ['', ''];
        yield [' ', ' '];

        // Must not be cast to a boolean
        yield ['true', 'true'];
        yield ['false', 'false'];
        yield ['yes', 'yes'];
        yield ['no', 'no'];
        yield ['on', 'on'];
        yield ['off', 'off'];

        // Must not be cast to a number
        yield ['-1', '-1'];
        yield ['0', '0'];
        yield ['1', '1'];
        yield ['+1', '+1'];
        yield ['-1.', '-1.'];
        yield ['0.', '0.'];
        yield ['1.', '1.'];
        yield ['+1.', '+1.'];
        yield ['-.1', '-.1'];
        yield ['.0', '.0'];
        yield ['.1', '.1'];
        yield ['+.1', '+.1'];
        yield ['-1.0', '-1.0'];
        yield ['0.0', '0.0'];
        yield ['1.0', '1.0'];
        yield ['+1.0', '+1.0'];
        yield ['1e-1', '1e-1'];
        yield ['1e1', '1e1'];
        yield ['1e+1', '1e+1'];
        yield ['1.e-1', '1.e-1'];
        yield ['1.e1', '1.e1'];
        yield ['1.e+1', '1.e+1'];
        yield ['.1e-1', '.1e-1'];
        yield ['.1e1', '.1e1'];
        yield ['.1e+1', '.1e+1'];
        yield ['1.0e-1', '1.0e-1'];
        yield ['1.0e1', '1.0e1'];
        yield ['1.0e+1', '1.0e+1'];
    }

    public function nonStrictStringProvider(): Generator
    {
        yield [-1, '-1'];
        yield [0, '0'];
        yield [1, '1'];
    }

    public function strictNotStringProvider(): Generator
    {
        yield [true];
        yield [false];
        yield [42];
        yield [3.14159];
        yield [[]];
    }

    public function nonStrictNotStringProvider(): Generator
    {
        yield [true];
        yield [false];
        yield [3.14159];
        yield [[]];
    }

    public function emptyArrayProvider(): Generator
    {
        yield [[]];
    }

    public function notArrayProvider(): Generator
    {
        yield [true];
        yield [false];
        yield [42];
        yield [3.14159];
        yield ['foo'];
    }

    public function strictNullDataProvider(): Generator
    {
        foreach ($this->strictNullProvider() as [$actual, $expected]) {
            yield [['value' => $actual], $expected];
        }
    }

    public function nonStrictNullDataProvider(): Generator
    {
        foreach ($this->nonStrictNullProvider() as [$actual, $expected]) {
            yield [['value' => $actual], $expected];
        }
    }

    public function strictBooleanDataProvider(): Generator
    {
        foreach ($this->strictBooleanProvider() as [$actual, $expected]) {
            yield [['value' => $actual], $expected];
        }
    }

    public function nonStrictBooleanDataProvider(): Generator
    {
        foreach ($this->nonStrictBooleanProvider() as [$actual, $expected]) {
            yield [['value' => $actual], $expected];
        }
    }

    public function notBooleanDataProvider(): Generator
    {
        foreach ($this->notBooleanProvider() as [$actual]) {
            yield [['value' => $actual]];
        }
    }

    public function strictIntegerDataProvider(): Generator
    {
        foreach ($this->strictIntegerProvider() as [$actual, $expected]) {
            yield [['value' => $actual], $expected];
        }
    }

    public function nonStrictIntegerDataProvider(): Generator
    {
        foreach ($this->nonStrictIntegerProvider() as [$actual, $expected]) {
            yield [['value' => $actual], $expected];
        }
    }

    public function notIntegerDataProvider(): Generator
    {
        foreach ($this->notIntegerProvider() as [$actual]) {
            yield [['value' => $actual]];
        }
    }

    public function strictNumberDataProvider(): Generator
    {
        foreach ($this->strictNumberProvider() as [$actual, $expected]) {
            yield [['value' => $actual], $expected];
        }
    }

    public function nonStrictNumberDataProvider(): Generator
    {
        foreach ($this->nonStrictNumberProvider() as [$actual, $expected]) {
            yield [['value' => $actual], $expected];
        }
    }

    public function notNumberDataProvider(): Generator
    {
        foreach ($this->notNumberProvider() as [$actual]) {
            yield [['value' => $actual]];
        }
    }

    public function strictStringDataProvider(): Generator
    {
        foreach ($this->strictStringProvider() as [$actual, $expected]) {
            yield [['value' => $actual], $expected];
        }
    }

    public function nonStrictStringDataProvider(): Generator
    {
        foreach ($this->nonStrictStringProvider() as [$actual, $expected]) {
            yield [['value' => $actual], $expected];
        }
    }

    public function strictNotStringDataProvider(): Generator
    {
        foreach ($this->strictNotStringProvider() as [$actual]) {
            yield [['value' => $actual]];
        }
    }

    public function nonStrictNotStringDataProvider(): Generator
    {
        foreach ($this->nonStrictNotStringProvider() as [$actual]) {
            yield [['value' => $actual]];
        }
    }

    public function integerEnumDataProvider(): Generator
    {
        if (PHP_VERSION_ID < 80100) {
            return [[[], null]];
        }

        $foo = Fixture\IntegerEnum::FOO;
        $bar = Fixture\IntegerEnum::BAR;
        $baz = Fixture\IntegerEnum::BAZ;

        yield [['value' => $foo->value], $foo];
        yield [['value' => $bar->value], $bar];
        yield [['value' => $baz->value], $baz];

        yield [['value' => (string) $foo->value], $foo];
        yield [['value' => (string) $bar->value], $bar];
        yield [['value' => (string) $baz->value], $baz];
    }

    public function stringEnumDataProvider(): Generator
    {
        if (PHP_VERSION_ID < 80100) {
            return [[[], null]];
        }

        $foo = Fixture\StringEnum::FOO;
        $bar = Fixture\StringEnum::BAR;
        $baz = Fixture\StringEnum::BAZ;

        yield [['value' => $foo->value], $foo];
        yield [['value' => $bar->value], $bar];
        yield [['value' => $baz->value], $baz];
    }

    public function myclabsEnumDataProvider(): Generator
    {
        $foo = Fixture\MyclabsEnum::FOO();
        $bar = Fixture\MyclabsEnum::BAR();
        $baz = Fixture\MyclabsEnum::BAZ();

        yield [['value' => $foo->getValue()], $foo];
        yield [['value' => $bar->getValue()], $bar];
        yield [['value' => $baz->getValue()], $baz];
    }

    public function arrayDataProvider(): Generator
    {
        yield [['value' => []], []];
        yield [['value' => ['foo']], ['foo']];
    }

    public function notArrayDataProvider(): Generator
    {
        foreach ($this->notArrayProvider() as [$actual]) {
            yield [['value' => $actual]];
        }
    }

    public function timestampDataProvider(): Generator
    {
        // default formatted timestamp
        $timestamp = date(TimestampTypeConverter::DEFAULT_FORMAT);

        yield [['value' => $timestamp], $timestamp];
        yield [['value' => $timestamp], $timestamp, TimestampTypeConverter::DEFAULT_FORMAT];

        yield [['value' => '700101'], '700101', 'ymd'];
        yield [['value' => '000000'], '000000', 'His'];

        yield [['value' => '-1'], '-1', 'U'];
        yield [['value' => '0'], '0', 'U'];
        yield [['value' => '1'], '1', 'U'];
        yield [['value' => '+1'], '1', 'U'];

        // Must be converted to a string...
        yield [['value' => -1], '-1', 'U'];
        yield [['value' => 0], '0', 'U'];
        yield [['value' => 1], '1', 'U'];

        // The timezone must be applied...
        yield [['value' => '00:00:00'], '00:00:00', 'H:i:s', 'Europe/Kiev'];

        // ISO 8601
        yield [['value' => '00:00:00.123456'], '00:00:00.123456', 'H:i:s.u'];
        yield [['value' => '00:00:00.123456+00:00'], '00:00:00.123456+00:00', 'H:i:s.uP'];
        yield [['value' => 'Monday 00:00:00.123456'], 'Monday 00:00:00.123456', 'l H:i:s.u'];
        yield [['value' => '00:00:00.1234567890'], '00:00:00.123456', 'H:i:s.u'];
        yield [['value' => '00:00:00.1234567890+00:00'], '00:00:00.123456+00:00', 'H:i:s.uP'];
        yield [['value' => 'Monday 00:00:00.1234567890'], 'Monday 00:00:00.123456', 'l H:i:s.u'];
    }

    public function invalidTimestampDataProvider(): Generator
    {
        yield [
            ['value' => '01/01/1970 00:00:00'],
            'Y-m-d H:i:s',
            ErrorCode::INVALID_TIMESTAMP,
            'This value is not a valid timestamp; expected format: Y-m-d H:i:s.',
        ];

        yield [
            ['value' => '1970-01-01 00:00:00'],
            'U',
            ErrorCode::MUST_BE_INTEGER,
            'This value must be of type integer.',
        ];

        yield [
            ['value' => 0],
            'Y',
            ErrorCode::MUST_BE_STRING,
            'This value must be of type string.',
        ];
    }

    public function timezoneDataProvider(): Generator
    {
        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            yield [['value' => $timezone], $timezone];
        }
    }

    public function uuidDataProvider(): Generator
    {
        yield [['value' => '207ddb61-c300-4368-9f26-33d0a99eac00'], '207ddb61-c300-4368-9f26-33d0a99eac00'];
    }

    private function createHydrator(array $context = [], array $typeConverters = []): HydratorInterface
    {
        $hydrator = new Hydrator($context, $typeConverters);
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

    private function assertInvalidValueExceptionTranslationDomain(
        int $exceptionIndex,
        string $expectedTranslationDomain
    ): void {
        $this->invalidValueExceptionTranslationDomain[] = [$exceptionIndex, $expectedTranslationDomain];
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

            foreach ($this->invalidValueExceptionTranslationDomain as [
                $index,
                $invalidValueExceptionTranslationDomain,
            ]) {
                $invalidDataExceptionHandled = true;
                $this->assertArrayHasKey($index, $invalidDataException->getExceptions());
                $this->assertSame(
                    $invalidValueExceptionTranslationDomain,
                    $invalidDataException->getExceptions()[$index]->getTranslationDomain(),
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
            $this->invalidValueExceptionTranslationDomain = [];

            if ($invalidDataExceptionHandled) {
                $this->assertTrue(true);
            }
        }
    }
}
