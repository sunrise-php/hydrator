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

use JsonException;
use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use SimdJsonException;
use Sunrise\Hydrator\Annotation\Alias;
use Sunrise\Hydrator\Annotation\Ignore;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\TypeConverter\ArrayTypeConverter;
use Sunrise\Hydrator\TypeConverter\BackedEnumTypeConverter;
use Sunrise\Hydrator\TypeConverter\BoolTypeConverter;
use Sunrise\Hydrator\TypeConverter\FloatTypeConverter;
use Sunrise\Hydrator\TypeConverter\IntTypeConverter;
use Sunrise\Hydrator\TypeConverter\RelationshipTypeConverter;
use Sunrise\Hydrator\TypeConverter\StringTypeConverter;
use Sunrise\Hydrator\TypeConverter\TimestampTypeConverter;
use Sunrise\Hydrator\TypeConverter\TimezoneTypeConverter;
use Sunrise\Hydrator\TypeConverter\UidTypeConverter;

use function array_key_exists;
use function extension_loaded;
use function is_array;
use function is_object;
use function json_decode;
use function simdjson_decode;
use function sprintf;
use function usort;

use const JSON_THROW_ON_ERROR;
use const PHP_MAJOR_VERSION;
use const PHP_VERSION_ID;

/**
 * Hydrator
 */
class Hydrator implements HydratorInterface
{

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
     */
    public function __construct()
    {
        $this->annotationReader = PHP_MAJOR_VERSION >= 8 ? new AnnotationReader() : DoctrineAnnotationReader::default();

        $this->addTypeConverter(
            new BoolTypeConverter(),
            new IntTypeConverter(),
            new FloatTypeConverter(),
            new StringTypeConverter(),
            new BackedEnumTypeConverter(),
            new TimestampTypeConverter(),
            new TimezoneTypeConverter(),
            new UidTypeConverter(),
            new ArrayTypeConverter(),
            new RelationshipTypeConverter(),
        );
    }

    /**
     * @inheritDoc
     */
    public function addTypeConverter(TypeConverterInterface ...$typeConverters): void
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
    }

    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path)
    {
        foreach ($this->typeConverters as $typeConverter) {
            $result = $typeConverter->castValue($value, $type, $path);
            if ($result->valid()) {
                return $result->current();
            }
        }

        throw Exception\UnsupportedPropertyTypeException::unsupportedType($type->getHolder(), $type->getName());
    }

    /**
     * Sets the given annotation reader
     *
     * @param AnnotationReaderInterface|\Doctrine\Common\Annotations\Reader $annotationReader
     *
     * @return self
     */
    public function setAnnotationReader($annotationReader): self
    {
        if ($annotationReader instanceof AnnotationReaderInterface) {
            $this->annotationReader = $annotationReader;
            return $this;
        }

        // BC with previous versions...
        if ($annotationReader instanceof \Doctrine\Common\Annotations\Reader) {
            $this->annotationReader = new DoctrineAnnotationReader($annotationReader);
            return $this;
        }

        throw new LogicException('Unsupported annotation reader');
    }

    /**
     * Uses the doctrine's default annotation reader
     *
     * @return self
     *
     * @throws LogicException If the doctrine/annotations package isn't installed on the server.
     *
     * @deprecated 3.1.0 Use the {@see setAnnotationReader()} method with {@see DoctrineAnnotationReader::default()}.
     */
    public function useDefaultAnnotationReader(): self
    {
        $this->setAnnotationReader(DoctrineAnnotationReader::default());

        return $this;
    }

    /**
     * Hydrates the given object with the given data
     *
     * @param class-string<T>|T $object
     * @param array<array-key, mixed> $data
     * @param list<array-key> $path
     *
     * @return T
     *
     * @throws Exception\InvalidDataException
     *         If the given data is invalid.
     *
     * @throws Exception\UninitializableObjectException
     *         If the object cannot be initialized.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If one of the object properties contains an unsupported type.
     *
     * @template T of object
     */
    public function hydrate($object, array $data, array $path = []): object
    {
        [$object, $class] = $this->instantObject($object);
        $properties = $class->getProperties();
        $defaultValues = $this->getClassConstructorDefaultValues($class);
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

            if ($this->annotationReader->getAnnotations($property, Ignore::class)->valid()) {
                continue;
            }

            $key = $property->getName();
            $alias = $this->annotationReader->getAnnotations($property, Alias::class)->current();
            if (isset($alias)) {
                $key = $alias->value;
            }

            if (array_key_exists($key, $data) === false) {
                if ($property->isInitialized($object)) {
                    continue;
                }

                if (array_key_exists($property->getName(), $defaultValues)) {
                    $property->setValue($object, $defaultValues[$property->getName()]);
                    continue;
                }

                $violations[] = InvalidValueException::shouldBeProvided([...$path, $key]);
                continue;
            }

            try {
                $this->hydrateProperty($object, $property, $data[$key], [...$path, $key]);
            } catch (InvalidDataException $e) {
                $violations = [...$violations, ...$e->getExceptions()];
            } catch (InvalidValueException $e) {
                $violations[] = $e;
            }
        }

        if (!empty($violations)) {
            throw new InvalidDataException('Invalid data.', $violations);
        }

        return $object;
    }

    /**
     * Hydrates the given object with the given JSON
     *
     * @param class-string<T>|T $object
     * @param string $json
     * @param int<0, max> $flags
     * @param int<1, 2147483647> $depth
     * @param list<array-key> $path
     *
     * @return T
     *
     * @throws Exception\InvalidDataException
     *         If the given data is invalid.
     *
     * @throws Exception\UninitializableObjectException
     *         If the object cannot be initialized.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If one of the object properties contains an unsupported type.
     *
     * @template T of object
     */
    public function hydrateWithJson($object, string $json, int $flags = 0, int $depth = 512, array $path = []): object
    {
        // @codeCoverageIgnoreStart
        if (!extension_loaded('json') && !extension_loaded('simdjson')) {
            throw new LogicException('Requires JSON or Simdjson extension.');
        } // @codeCoverageIgnoreEnd

        try {
            // phpcs:ignore Generic.Files.LineLength
            $data = extension_loaded('simdjson') ? simdjson_decode($json, true, $depth) : json_decode($json, true, $depth, $flags | JSON_THROW_ON_ERROR);
        } catch (JsonException|SimdJsonException $e) {
            // phpcs:ignore Generic.Files.LineLength
            throw new InvalidDataException(sprintf('The JSON is invalid and couldn‘t be decoded due to: %s', $e->getMessage()));
        }

        if (!is_array($data)) {
            throw new InvalidDataException('The JSON must be in the form of an array or an object.');
        }

        return $this->hydrate($object, $data);
    }

    /**
     * Instantiates the given object
     *
     * @param class-string<T>|T $object
     *
     * @return array{0: T, 1: ReflectionClass}
     *
     * @throws Exception\UninitializableObjectException
     *         If the given object cannot be instantiated.
     *
     * @template T of object
     */
    private function instantObject($object): array
    {
        $class = new ReflectionClass($object);

        if (is_object($object)) {
            return [$object, $class];
        }

        if (!$class->isInstantiable()) {
            throw new Exception\UninitializableObjectException(sprintf(
                'The class %s cannot be hydrated because it is an uninstantiable class.',
                $class->getName(),
            ));
        }

        return [$class->newInstanceWithoutConstructor(), $class];
    }

    /**
     * Hydrates the given property with the given value
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param mixed $value
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidDataException If the given value is invalid.
     *
     * @throws InvalidValueException If the given value is invalid.
     *
     * @throws Exception\UnsupportedPropertyTypeException If the given property contains an unsupported type.
     */
    private function hydrateProperty(object $object, ReflectionProperty $property, $value, array $path): void
    {
        $type = $this->getPropertyType($property);
        if ($type === null) {
            $property->setValue($object, $value);
            return;
        }

        if ($value === null) {
            if ($type->allowsNull()) {
                $property->setValue($object, null);
                return;
            }

            throw InvalidValueException::shouldNotBeEmpty($path);
        }

        $property->setValue($object, $this->castValue($value, $type, $path));
    }

    /**
     * Gets the given property's type
     *
     * @param ReflectionProperty $property
     *
     * @return Type|null
     *
     * @throws Exception\UnsupportedPropertyTypeException If the given property contains an unsupported type.
     */
    private function getPropertyType(ReflectionProperty $property): ?Type
    {
        $type = $property->getType();
        if ($type === null) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            return new Type($property, $type->getName(), $type->allowsNull());
        }

        throw Exception\UnsupportedPropertyTypeException::unsupportedType($property, (string) $type);
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
}
