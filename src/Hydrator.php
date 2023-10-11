<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2021, Anatoly Nekhay
 * @license https://github.com/sunrise-php/hydrator/blob/master/LICENSE
 * @link https://github.com/sunrise-php/hydrator
 */

declare(strict_types=1);

namespace Sunrise\Hydrator;

use Generator;
use JsonException;
use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Sunrise\Hydrator\Annotation\Alias;
use Sunrise\Hydrator\Annotation\Context;
use Sunrise\Hydrator\Annotation\Ignore;
use Sunrise\Hydrator\AnnotationReader\BuiltinAnnotationReader;
use Sunrise\Hydrator\AnnotationReader\DoctrineAnnotationReader;
use Sunrise\Hydrator\AnnotationReader\NullAnnotationReader;
use Sunrise\Hydrator\Dictionary\BuiltinType;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\TypeConverter\ArrayAccessTypeConverter;
use Sunrise\Hydrator\TypeConverter\ArrayTypeConverter;
use Sunrise\Hydrator\TypeConverter\BackedEnumTypeConverter;
use Sunrise\Hydrator\TypeConverter\BooleanTypeConverter;
use Sunrise\Hydrator\TypeConverter\IntegerTypeConverter;
use Sunrise\Hydrator\TypeConverter\MixedTypeConverter;
use Sunrise\Hydrator\TypeConverter\MyclabsEnumTypeConverter;
use Sunrise\Hydrator\TypeConverter\NumberTypeConverter;
use Sunrise\Hydrator\TypeConverter\ObjectTypeConverter;
use Sunrise\Hydrator\TypeConverter\RamseyUuidTypeConverter;
use Sunrise\Hydrator\TypeConverter\StringTypeConverter;
use Sunrise\Hydrator\TypeConverter\SymfonyUidTypeConverter;
use Sunrise\Hydrator\TypeConverter\TimestampTypeConverter;
use Sunrise\Hydrator\TypeConverter\TimezoneTypeConverter;
use TypeError;

use function array_key_exists;
use function class_exists;
use function extension_loaded;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function json_decode;
use function sprintf;
use function usort;

use const JSON_BIGINT_AS_STRING;
use const JSON_THROW_ON_ERROR;
use const PHP_MAJOR_VERSION;
use const PHP_VERSION_ID;

/**
 * Hydrator
 */
class Hydrator implements HydratorInterface
{

    /**
     * @var array<string, mixed>
     */
    private array $context;

    /**
     * @var AnnotationReaderInterface
     */
    private AnnotationReaderInterface $annotationReader;

    /**
     * @var list<TypeConverterInterface>
     */
    private array $typeConverters = [];

    /**
     * Constructor of the class
     *
     * @param array<string, mixed> $context
     */
    public function __construct(array $context = [])
    {
        $this->context = $context;

        $this->annotationReader = PHP_MAJOR_VERSION >= 8 ?
            new BuiltinAnnotationReader() :
            new NullAnnotationReader();

        $this->addTypeConverter(...self::defaultTypeConverters());
    }

    /**
     * Sets the given annotation reader to the hydrator
     *
     * @param AnnotationReaderInterface|\Doctrine\Common\Annotations\Reader $annotationReader
     *
     * @return self
     *
     * @since 3.0.0
     */
    public function setAnnotationReader($annotationReader): self
    {
        // BC with previous versions...
        if ($annotationReader instanceof \Doctrine\Common\Annotations\Reader) {
            $annotationReader = new DoctrineAnnotationReader($annotationReader);
        }

        $this->annotationReader = $annotationReader;
        foreach ($this->typeConverters as $typeConverter) {
            if ($typeConverter instanceof AnnotationReaderAwareInterface) {
                $typeConverter->setAnnotationReader($annotationReader);
            }
        }

        return $this;
    }

    /**
     * Sets the doctrine's default annotation reader to the hydrator
     *
     * @return self
     *
     * @since 3.0.0
     *
     * @deprecated 3.2.0 Use the {@see setAnnotationReader()} method
     *                   with the {@see DoctrineAnnotationReader::default()} attribute.
     */
    public function useDefaultAnnotationReader(): self
    {
        return $this->setAnnotationReader(DoctrineAnnotationReader::default());
    }

    /**
     * Adds the given type converter(s) to the hydrator
     *
     * @param TypeConverterInterface ...$typeConverters
     *
     * @return self
     *
     * @since 3.1.0
     */
    public function addTypeConverter(TypeConverterInterface ...$typeConverters): self
    {
        foreach ($typeConverters as $typeConverter) {
            if ($typeConverter instanceof AnnotationReaderAwareInterface) {
                $typeConverter->setAnnotationReader($this->annotationReader);
            }
            if ($typeConverter instanceof HydratorAwareInterface) {
                $typeConverter->setHydrator($this);
            }

            $this->typeConverters[] = $typeConverter;
        }

        // phpcs:ignore Generic.Files.LineLength
        usort($this->typeConverters, static fn(TypeConverterInterface $a, TypeConverterInterface $b): int => $b->getWeight() <=> $a->getWeight());

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path = [], array $context = [])
    {
        if ($value === null) {
            if ($type->allowsNull()) {
                return null;
            }

            throw InvalidValueException::mustNotBeEmpty($path);
        }

        // phpcs:ignore Generic.Files.LineLength
        $context = ($this->annotationReader->getAnnotations(Context::class, $type->getHolder())->current()->value ?? []) + $context + $this->context;

        foreach ($this->typeConverters as $typeConverter) {
            $result = $typeConverter->castValue($value, $type, $path, $context);
            if ($result->valid()) {
                return $result->current();
            }
        }

        throw InvalidObjectException::unsupportedType($type);
    }

    /**
     * @inheritDoc
     */
    public function hydrate($object, array $data, array $path = [], array $context = []): object
    {
        [$object, $class] = $this->instantObject($object);
        $properties = $class->getProperties();
        $constructorDefaultValues = $this->getClassConstructorDefaultValues($class);

        $violations = [];
        foreach ($properties as $property) {
            // @codeCoverageIgnoreStart
            if (PHP_VERSION_ID < 80100) {
                /** @psalm-suppress UnusedMethodCall */
                $property->setAccessible(true);
            } // @codeCoverageIgnoreEnd

            if ($property->isStatic()) {
                continue;
            }

            if ($this->annotationReader->getAnnotations(Ignore::class, $property)->valid()) {
                continue;
            }

            // phpcs:ignore Generic.Files.LineLength
            $key = $this->annotationReader->getAnnotations(Alias::class, $property)->current()->value ?? $property->getName();

            if (array_key_exists($key, $data) === false) {
                if ($property->isInitialized($object)) {
                    continue;
                }

                if (array_key_exists($property->getName(), $constructorDefaultValues)) {
                    $property->setValue($object, $constructorDefaultValues[$property->getName()]);
                    continue;
                }

                $violations[] = InvalidValueException::mustBeProvided([...$path, $key]);
                continue;
            }

            try {
                // phpcs:ignore Generic.Files.LineLength
                $property->setValue($object, $this->castValue($data[$key], $this->getPropertyType($property), [...$path, $key], $context));
            } catch (InvalidValueException $e) {
                $violations[] = $e;
            } catch (InvalidDataException $e) {
                $violations = [...$violations, ...$e->getExceptions()];
            }
        }

        if (!empty($violations)) {
            throw new InvalidDataException('Invalid data', $violations);
        }

        return $object;
    }

    /**
     * @inheritDoc
     */
    // phpcs:ignore Generic.Files.LineLength
    public function hydrateWithJson($object, string $json, int $flags = 0, int $depth = 512, array $path = [], array $context = []): object
    {
        // @codeCoverageIgnoreStart
        if (!extension_loaded('json')) {
            throw new LogicException('The JSON extension is required.');
        } // @codeCoverageIgnoreEnd

        try {
            $data = json_decode($json, true, $depth, $flags | JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            // phpcs:ignore Generic.Files.LineLength
            throw new InvalidDataException(sprintf('The JSON is invalid and couldn‘t be decoded due to: %s', $e->getMessage()));
        }

        if (!is_array($data)) {
            throw new InvalidDataException('The JSON must be in the form of an array or an object.');
        }

        return $this->hydrate($object, $data, $path, $context);
    }

    /**
     * Instantiates the given object
     *
     * @param class-string<T>|T $object
     *
     * @return array{0: T, 1: ReflectionClass}
     *
     * @throws InvalidObjectException If the object couldn't be instantiated.
     *
     * @template T of object
     */
    private function instantObject($object): array
    {
        if (is_object($object)) {
            return [$object, new ReflectionClass($object)];
        }

        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_string($object)) {
            // phpcs:ignore Generic.Files.LineLength
            throw new TypeError(sprintf('Argument #1 ($object) must be of type object or string, %s given', gettype($object)));
        }

        if (!class_exists($object)) {
            throw InvalidObjectException::uninstantiableObject($object);
        }

        $class = new ReflectionClass($object);
        if (!$class->isInstantiable()) {
            throw InvalidObjectException::uninstantiableObject($class->getName());
        }

        return [$class->newInstanceWithoutConstructor(), $class];
    }

    /**
     * Gets the given property's type
     *
     * @param ReflectionProperty $property
     *
     * @return Type
     */
    private function getPropertyType(ReflectionProperty $property): Type
    {
        $type = $property->getType();
        if ($type === null) {
            return new Type($property, BuiltinType::MIXED, true);
        }

        if ($type instanceof ReflectionNamedType) {
            return new Type($property, $type->getName(), $type->allowsNull());
        }

        return new Type($property, (string) $type, $type->allowsNull());
    }

    /**
     * Gets default values from the given class's constructor
     *
     * @param ReflectionClass<T> $class
     *
     * @return array<non-empty-string, mixed>
     *
     * @template T of object
     */
    private function getClassConstructorDefaultValues(ReflectionClass $class): array
    {
        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $result = [];
        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                /** @psalm-suppress MixedAssignment */
                $result[$parameter->getName()] = $parameter->getDefaultValue();
            }
        }

        return $result;
    }

    /**
     * Gets the default type converters for this environment
     *
     * @return Generator<int, TypeConverterInterface>
     */
    private static function defaultTypeConverters(): Generator
    {
        yield new MixedTypeConverter();
        yield new BooleanTypeConverter();
        yield new IntegerTypeConverter();
        yield new NumberTypeConverter();
        yield new StringTypeConverter();
        yield new TimestampTypeConverter();
        yield new TimezoneTypeConverter();
        yield new ArrayTypeConverter();
        yield new ArrayAccessTypeConverter();
        yield new ObjectTypeConverter();

        if (PHP_MAJOR_VERSION >= 8) {
            yield new BackedEnumTypeConverter();
        }
        if (class_exists(\MyCLabs\Enum\Enum::class)) {
            yield new MyclabsEnumTypeConverter();
        }
        if (class_exists(\Ramsey\Uuid\Uuid::class)) {
            yield new RamseyUuidTypeConverter();
        }
        if (class_exists(\Symfony\Component\Uid\AbstractUid::class)) {
            yield new SymfonyUidTypeConverter();
        }
    }
}
