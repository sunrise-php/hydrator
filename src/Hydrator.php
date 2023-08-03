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

use BackedEnum;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader as AnnotationReaderInterface;
use JsonException;
use Sunrise\Hydrator\Annotation\Alias;
use Sunrise\Hydrator\Annotation\Format;
use Sunrise\Hydrator\Annotation\Ignore;
use Sunrise\Hydrator\Annotation\Relationship;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use DateTimeImmutable;
use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use ReflectionNamedType;
use ReflectionProperty;
use ValueError;

use function array_key_exists;
use function class_exists;
use function extension_loaded;
use function filter_var;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function is_subclass_of;
use function json_decode;
use function sprintf;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;
use const JSON_THROW_ON_ERROR;
use const PHP_MAJOR_VERSION;
use const PHP_VERSION_ID;

/**
 * Hydrator
 */
class Hydrator implements HydratorInterface
{

    /**
     * @var AnnotationReaderInterface|null
     */
    private ?AnnotationReaderInterface $annotationReader = null;

    /**
     * Gets the annotation reader
     *
     * @return AnnotationReaderInterface|null
     */
    public function getAnnotationReader(): ?AnnotationReaderInterface
    {
        return $this->annotationReader;
    }

    /**
     * Sets the given annotation reader
     *
     * @param AnnotationReaderInterface|null $annotationReader
     *
     * @return self
     */
    public function setAnnotationReader(?AnnotationReaderInterface $annotationReader): self
    {
        $this->annotationReader = $annotationReader;

        return $this;
    }

    /**
     * Uses the default annotation reader
     *
     * @return self
     *
     * @throws LogicException
     *         If the doctrine/annotations package isn't installed.
     */
    public function useDefaultAnnotationReader(): self
    {
        // @codeCoverageIgnoreStart
        if (!class_exists(AnnotationReader::class)) {
            throw new LogicException('The package doctrine/annotations is required.');
        } // @codeCoverageIgnoreEnd

        $this->annotationReader = new AnnotationReader();

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
     * @throws Exception\UntypedPropertyException
     *         If one of the object properties isn't typed.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If one of the object properties contains an unsupported type.
     *
     * @template T of object
     */
    public function hydrate($object, array $data, array $path = []): object
    {
        $object = $this->instantObject($object);
        $class = new ReflectionClass($object);
        $properties = $class->getProperties();
        $defaultValues = $this->getClassConstructorDefaultValues($class);
        $violations = [];
        foreach ($properties as $property) {
            if (PHP_VERSION_ID < 80100) {
                $property->setAccessible(true);
            }

            if ($property->isStatic()) {
                continue;
            }

            $ignore = $this->getPropertyAnnotation($property, Ignore::class);
            if (isset($ignore)) {
                continue;
            }

            $key = $property->getName();
            $alias = $this->getPropertyAnnotation($property, Alias::class);
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
     *
     * @return T
     *
     * @throws Exception\InvalidDataException
     *         If the given data is invalid.
     *
     * @throws Exception\UninitializableObjectException
     *         If the object cannot be initialized.
     *
     * @throws Exception\UntypedPropertyException
     *         If one of the object properties isn't typed.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If one of the object properties contains an unsupported type.
     *
     * @template T of object
     */
    public function hydrateWithJson($object, string $json, int $flags = 0, int $depth = 512): object
    {
        // @codeCoverageIgnoreStart
        if (!extension_loaded('json')) {
            throw new LogicException('JSON extension is required.');
        } // @codeCoverageIgnoreEnd

        try {
            $data = json_decode($json, true, $depth, $flags | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidDataException(sprintf('Invalid JSON: %s', $e->getMessage()));
        }

        if (!is_array($data)) {
            throw new InvalidDataException('JSON must be an object.');
        }

        return $this->hydrate($object, $data);
    }

    /**
     * Instantiates the given object
     *
     * @param class-string<T>|T $object
     *
     * @return T
     *
     * @throws Exception\UninitializableObjectException
     *         If the given object cannot be instantiated.
     *
     * @template T of object
     */
    private function instantObject($object): object
    {
        if (is_object($object)) {
            return $object;
        }

        $class = new ReflectionClass($object);
        if (!$class->isInstantiable()) {
            throw new Exception\UninitializableObjectException(sprintf(
                'The class %s cannot be hydrated because it is an uninstantiable class.',
                $class->getName(),
            ));
        }

        /** @var T */
        return $class->newInstanceWithoutConstructor();
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
     * @throws InvalidValueException
     *         If the given value is invalid.
     *
     * @throws InvalidDataException
     *         If the given value is invalid.
     *
     * @throws Exception\UntypedPropertyException
     *         If the given property isn't typed.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If the given property contains an unsupported type.
     */
    private function hydrateProperty(
        object $object,
        ReflectionProperty $property,
        $value,
        array $path
    ): void {
        $type = $this->getPropertyType($property);
        $typeName = $type->getName();

        if ($value === null) {
            $this->hydratePropertyWithNull($object, $property, $type, $path);
            return;
        }
        if ($typeName === 'bool') {
            $this->hydrateBooleanProperty($object, $property, $type, $value, $path);
            return;
        }
        if ($typeName === 'int') {
            $this->hydrateIntegerProperty($object, $property, $type, $value, $path);
            return;
        }
        if ($typeName === 'float') {
            $this->hydrateNumericProperty($object, $property, $type, $value, $path);
            return;
        }
        if ($typeName === 'string') {
            $this->hydrateStringProperty($object, $property, $value, $path);
            return;
        }
        if ($typeName === 'array') {
            $this->hydrateArrayProperty($object, $property, $value, $path);
            return;
        }
        if ($typeName === DateTimeImmutable::class) {
            $this->hydrateTimestampProperty($object, $property, $type, $value, $path);
            return;
        }
        if (is_subclass_of($typeName, BackedEnum::class)) {
            $this->hydrateEnumerableProperty($object, $property, $type, $typeName, $value, $path);
            return;
        }
        if (class_exists($typeName)) {
            $this->hydrateRelationshipProperty($object, $property, $typeName, $value, $path);
            return;
        }

        throw new Exception\UnsupportedPropertyTypeException(sprintf(
            'The property %s.%s contains an unsupported type %s.',
            $property->getDeclaringClass()->getName(),
            $property->getName(),
            $typeName,
        ));
    }

    /**
     * Hydrates the given property with null
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydratePropertyWithNull(
        object $object,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        array $path
    ): void {
        if (!$type->allowsNull()) {
            throw InvalidValueException::shouldNotBeEmpty($path);
        }

        $property->setValue($object, null);
    }

    /**
     * Hydrates the given boolean property with the given value
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydrateBooleanProperty(
        object $object,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value,
        array $path
    ): void {
        if (is_string($value)) {
            // As part of the support for HTML forms and other untyped data sources,
            // an empty string should not be cast to a boolean type, therefore,
            // such values should be treated as NULL.
            if (trim($value) === '') {
                $this->hydratePropertyWithNull($object, $property, $type, $path);
                return;
            }

            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L273
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        if (!is_bool($value)) {
            throw InvalidValueException::shouldBeBoolean($path);
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given integer property with the given value
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydrateIntegerProperty(
        object $object,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value,
        array $path
    ): void {
        if (is_string($value)) {
            // As part of the support for HTML forms and other untyped data sources,
            // an empty string cannot be cast to an integer type, therefore,
            // such values should be treated as NULL.
            if (trim($value) === '') {
                $this->hydratePropertyWithNull($object, $property, $type, $path);
                return;
            }

            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L94
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L197
            $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        }

        if (!is_int($value)) {
            throw InvalidValueException::shouldBeInteger($path);
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given numeric property with the given value
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydrateNumericProperty(
        object $object,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value,
        array $path
    ): void {
        if (is_string($value)) {
            // As part of the support for HTML forms and other untyped data sources,
            // an empty string cannot be cast to a number type, therefore,
            // such values should be treated as NULL.
            if (trim($value) === '') {
                $this->hydratePropertyWithNull($object, $property, $type, $path);
                return;
            }

            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L342
            $value = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        }

        if (is_int($value)) {
            $value = (float) $value;
        }

        if (!is_float($value)) {
            throw InvalidValueException::shouldBeNumber($path);
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given string property with the given value
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param mixed $value
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydrateStringProperty(
        object $object,
        ReflectionProperty $property,
        $value,
        array $path
    ): void {
        if (!is_string($value)) {
            throw InvalidValueException::shouldBeString($path);
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given array property with the given value
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param mixed $value
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydrateArrayProperty(
        object $object,
        ReflectionProperty $property,
        $value,
        array $path
    ): void {
        $relationship = $this->getPropertyAnnotation($property, Relationship::class);
        if (isset($relationship)) {
            $this->hydrateRelationshipsProperty($object, $property, $relationship, $value, $path);
            return;
        }

        if (!is_array($value)) {
            throw InvalidValueException::shouldBeArray($path);
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given timestamp property with the given value
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidValueException
     *         If the given value isn't valid.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If the given property doesn't contain the Format attribute.
     */
    private function hydrateTimestampProperty(
        object $object,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value,
        array $path
    ): void {
        $format = $this->getPropertyAnnotation($property, Format::class);
        if (!isset($format)) {
            throw new Exception\UnsupportedPropertyTypeException(sprintf(
                'The property %1$s.%2$s must contain the attribute %3$s, ' .
                'for example: #[\%3$s(\DateTimeInterface::DATE_RFC3339)].',
                $property->getDeclaringClass()->getName(),
                $property->getName(),
                Format::class,
            ));
        }

        if (is_string($value)) {
            // As part of the support for HTML forms and other untyped data sources,
            // an instance of DateTimeImmutable should not be created from an empty string, therefore,
            // such values should be treated as NULL.
            if (trim($value) === '') {
                $this->hydratePropertyWithNull($object, $property, $type, $path);
                return;
            }

            if ($format->value === 'U') {
                // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L94
                // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L197
                $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            }
        }

        if ($format->value === 'U' && !is_int($value)) {
            throw InvalidValueException::shouldBeInteger($path);
        }
        if ($format->value !== 'U' && !is_string($value)) {
            throw InvalidValueException::shouldBeString($path);
        }

        /** @var int|string $value */

        $timestamp = DateTimeImmutable::createFromFormat($format->value, (string) $value);
        if ($timestamp === false) {
            throw InvalidValueException::invalidTimestamp($path, $format->value);
        }

        $property->setValue($object, $timestamp);
    }

    /**
     * Hydrates the given enumerable property with the given value
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param class-string<BackedEnum> $enumName
     * @param mixed $value
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydrateEnumerableProperty(
        object $object,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        string $enumName,
        $value,
        array $path
    ): void {
        $enumType = (string) (new ReflectionEnum($enumName))->getBackingType();

        if (is_string($value)) {
            // As part of the support for HTML forms and other untyped data sources,
            // an instance of BackedEnum should not be created from an empty string, therefore,
            // such values should be treated as NULL.
            if (trim($value) === '') {
                $this->hydratePropertyWithNull($object, $property, $type, $path);
                return;
            }

            if ($enumType === 'int') {
                // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L94
                // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L197
                $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            }
        }

        if ($enumType === 'int' && !is_int($value)) {
            throw InvalidValueException::shouldBeInteger($path);
        }
        if ($enumType === 'string' && !is_string($value)) {
            throw InvalidValueException::shouldBeString($path);
        }

        /** @var int|string $value */

        try {
            $property->setValue($object, $enumName::from($value));
        } catch (ValueError $e) {
            throw InvalidValueException::invalidChoice($path, $enumName);
        }
    }

    /**
     * Hydrates the given relationship property with the given value
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param class-string $className
     * @param mixed $value
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidValueException
     *         If the given value isn't valid.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If the given property refers to a non-instantiable class.
     */
    private function hydrateRelationshipProperty(
        object $object,
        ReflectionProperty $property,
        string $className,
        $value,
        array $path
    ): void {
        $classReflection = new ReflectionClass($className);
        if (!$classReflection->isInstantiable()) {
            throw new Exception\UnsupportedPropertyTypeException(sprintf(
                'The property %s.%s refers to a non-instantiable class %s.',
                $property->getDeclaringClass()->getName(),
                $property->getName(),
                $classReflection->getName(),
            ));
        }

        if (!is_array($value)) {
            throw InvalidValueException::shouldBeArray($path);
        }

        $classInstance = $classReflection->newInstanceWithoutConstructor();

        $property->setValue($object, $this->hydrate($classInstance, $value, $path));
    }

    /**
     * Hydrates the given relationships property with the given value
     *
     * @param object $object
     * @param ReflectionProperty $property
     * @param Relationship $relationship
     * @param mixed $value
     * @param list<array-key> $path
     *
     * @return void
     *
     * @throws InvalidDataException
     *         If the given value isn't valid.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If the given property refers to a non-instantiable class.
     */
    private function hydrateRelationshipsProperty(
        object $object,
        ReflectionProperty $property,
        Relationship $relationship,
        $value,
        array $path
    ): void {
        $classReflection = new ReflectionClass($relationship->target);
        if (!$classReflection->isInstantiable()) {
            throw new Exception\UnsupportedPropertyTypeException(sprintf(
                'The property %s.%s refers to a non-instantiable class %s.',
                $property->getDeclaringClass()->getName(),
                $property->getName(),
                $classReflection->getName(),
            ));
        }

        if (!is_array($value)) {
            throw InvalidValueException::shouldBeArray($path);
        }

        $counter = 0;
        $violations = [];
        $classInstances = [];
        $classPrototype = $classReflection->newInstanceWithoutConstructor();
        foreach ($value as $key => $data) {
            if (isset($relationship->limit) && ++$counter > $relationship->limit) {
                $violations[] = InvalidValueException::redundantElement([...$path, $key], $relationship->limit);
                break;
            }

            if (!is_array($data)) {
                $violations[] = InvalidValueException::shouldBeArray([...$path, $key]);
                continue;
            }

            try {
                $classInstances[$key] = $this->hydrate(clone $classPrototype, $data, [...$path, $key]);
            } catch (InvalidDataException $e) {
                $violations = [...$violations, ...$e->getExceptions()];
            }
        }

        if (!empty($violations)) {
            throw new InvalidDataException('Invalid data.', $violations);
        }

        $property->setValue($object, $classInstances);
    }

    /**
     * Gets a type from the given property
     *
     * @param ReflectionProperty $property
     *
     * @return ReflectionNamedType
     *
     * @throws Exception\UntypedPropertyException
     *         If the given property isn't typed.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If the given property contains an unsupported type.
     */
    private function getPropertyType(ReflectionProperty $property): ReflectionNamedType
    {
        $type = $property->getType();

        if (!isset($type)) {
            throw new Exception\UntypedPropertyException(sprintf(
                'The property %s.%s is not typed.',
                $property->getDeclaringClass()->getName(),
                $property->getName(),
            ));
        }

        if (!($type instanceof ReflectionNamedType)) {
            throw new Exception\UnsupportedPropertyTypeException(sprintf(
                'The property %s.%s contains an unsupported type %s.',
                $property->getDeclaringClass()->getName(),
                $property->getName(),
                (string) $type,
            ));
        }

        return $type;
    }

    /**
     * Gets an annotation from the given property
     *
     * @param ReflectionProperty $property
     * @param class-string<T> $annotationName
     *
     * @return T|null
     *
     * @template T of object
     */
    private function getPropertyAnnotation(ReflectionProperty $property, string $annotationName): ?object
    {
        if (PHP_MAJOR_VERSION >= 8) {
            /**
             * @psalm-var list<ReflectionAttribute> $annotations
             * @phpstan-var list<ReflectionAttribute<T>> $annotations
             * @psalm-suppress TooManyTemplateParams
             */
            $annotations = $property->getAttributes($annotationName);

            if (isset($annotations[0])) {
                /** @var T */
                return $annotations[0]->newInstance();
            }
        }

        if (isset($this->annotationReader)) {
            $annotations = $this->annotationReader->getPropertyAnnotations($property);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof $annotationName) {
                    return $annotation;
                }
            }
        }

        return null;
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
        $result = [];
        $constructor = $class->getConstructor();
        if (isset($constructor)) {
            foreach ($constructor->getParameters() as $parameter) {
                if ($parameter->isDefaultValueAvailable()) {
                    /** @psalm-suppress MixedAssignment */
                    $result[$parameter->getName()] = $parameter->getDefaultValue();
                }
            }
        }

        return $result;
    }
}
