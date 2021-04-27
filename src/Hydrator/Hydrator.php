<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2021, Anatoly Fenric
 * @license https://github.com/sunrise-php/hydrator/blob/master/LICENSE
 * @link https://github.com/sunrise-php/hydrator
 */

namespace Sunrise\Hydrator;

/**
 * Import classes
 */
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use ArrayAccess;
use DateTimeInterface;
use ReflectionClass;
use ReflectionProperty;
use ReflectionNamedType;

/**
 * Import functions
 */
use function array_key_exists;
use function in_array;
use function is_array;
use function is_scalar;
use function is_string;
use function is_subclass_of;
use function sprintf;
use function strtotime;

/**
 * Hydrator
 */
class Hydrator implements HydratorInterface
{

    /**
     * @var SimpleAnnotationReader
     */
    private $annotationReader;

    /**
     * Constructor of the class
     */
    public function __construct()
    {
        $this->annotationReader = /** @scrutinizer ignore-deprecated */ new SimpleAnnotationReader();
        $this->annotationReader->addNamespace(Annotation::class);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\MissingRequiredValueException
     *         If the given data does not contain required value.
     *         * data error.
     *
     * @throws Exception\InvalidValueException
     *         If the given data contains an invalid value.
     *         * data error.
     *
     * @throws Exception\UntypedObjectPropertyException
     *         If one of the properties of the given object is not typed.
     *         * DTO error.
     *
     * @throws Exception\UnsupportedObjectPropertyTypeException
     *         If one of the properties of the given object contains an unsupported type.
     *         * DTO error.
     */
    public function hydrate(HydrableObjectInterface $object, array $data) : HydrableObjectInterface
    {
        $class = new ReflectionClass($object);
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $key = $property->getName();

            if (!array_key_exists($key, $data)) {
                $alias =  $this->annotationReader->getPropertyAnnotation($property, Annotation\Alias::class);
                if ($alias instanceof Annotation\Alias) {
                    $key = $alias->value;
                }
            }

            if (!array_key_exists($key, $data)) {
                if (!$property->isInitialized($object)) {
                    throw new Exception\MissingRequiredValueException(sprintf(
                        'The <%s.%s> property is required.',
                        $class->getShortName(),
                        $property->getName(),
                    ));
                }

                continue;
            }

            if (!$property->hasType()) {
                throw new Exception\UntypedObjectPropertyException(sprintf(
                    'The <%s.%s> property is not typed.',
                    $class->getShortName(),
                    $property->getName(),
                ));
            }

            $property->setAccessible(true);

            $this->hydrateProperty($object, $class, $property, $property->getType(), $data[$key]);
        }

        return $object;
    }

    /**
     * Hydrates the given property with the given value
     *
     * @param HydrableObjectInterface $object
     * @param ReflectionClass $class
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     *
     * @return void
     *
     * @throws Exception\UnsupportedObjectPropertyTypeException
     *         If the given property contains an unsupported type.
     *
     * @throws Exception\InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydrateProperty(
        HydrableObjectInterface $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (null === $value) {
            $this->hydratePropertyWithNull($object, $class, $property, $type);
            return;
        }

        if (in_array($type->getName(), ['bool', 'int', 'float', 'string'])) {
            $this->hydratePropertyWithScalar($object, $class, $property, $type, $value);
            return;
        }

        if ('array' === $type->getName() || is_subclass_of($type->getName(), ArrayAccess::class)) {
            $this->hydratePropertyWithArray($object, $class, $property, $type, $value);
            return;
        }

        if (is_subclass_of($type->getName(), DateTimeInterface::class)) {
            $this->hydratePropertyWithDateTime($object, $class, $property, $type, $value);
            return;
        }

        if (is_subclass_of($type->getName(), HydrableObjectInterface::class)) {
            $this->hydratePropertyWithOneToOneAssociation($object, $class, $property, $type, $value);
            return;
        }

        if (is_subclass_of($type->getName(), HydrableObjectCollectionInterface::class)) {
            $this->hydratePropertyWithOneToManyAssociation($object, $class, $property, $type, $value);
            return;
        }

        throw new Exception\UnsupportedObjectPropertyTypeException(sprintf(
            'The <%s.%s> property contains the <%s> unhydrable type.',
            $class->getShortName(),
            $property->getName(),
            $type->getName(),
        ));
    }

    /**
     * Hydrates the given property with null
     *
     * @param HydrableObjectInterface $object
     * @param ReflectionClass $class
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     *
     * @return void
     *
     * @throws Exception\InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydratePropertyWithNull(
        HydrableObjectInterface $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type
    ) : void {
        if (!$type->allowsNull()) {
            throw new Exception\InvalidValueException(sprintf(
                'The <%s.%s> property does not support null.',
                $class->getShortName(),
                $property->getName(),
            ));
        }

        $property->setValue($object, null);
    }

    /**
     * Hydrates the given property with the given scalar value
     *
     * @param HydrableObjectInterface $object
     * @param ReflectionClass $class
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     *
     * @return void
     *
     * @throws Exception\InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydratePropertyWithScalar(
        HydrableObjectInterface $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_scalar($value)) {
            throw new Exception\InvalidValueException(sprintf(
                'The <%s.%s> property only accepts a scalar value.',
                $class->getShortName(),
                $property->getName(),
            ));
        }

        switch ($type->getName()) {
            case 'bool':
                $property->setValue($object, (bool) $value);
                break;
            case 'int':
                $property->setValue($object, (int) $value);
                break;
            case 'float':
                $property->setValue($object, (float) $value);
                break;
            case 'string':
                $property->setValue($object, (string) $value);
                break;
        }
    }

    /**
     * Hydrates the given property with the given array value
     *
     * @param HydrableObjectInterface $object
     * @param ReflectionClass $class
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     *
     * @return void
     *
     * @throws Exception\InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydratePropertyWithArray(
        HydrableObjectInterface $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_array($value)) {
            throw new Exception\InvalidValueException(sprintf(
                'The <%s.%s> property only accepts an array.',
                $class->getShortName(),
                $property->getName(),
            ));
        }

        if ('array' === $type->getName()) {
            $property->setValue($object, $value);
            return;
        }

        $arrayClassName = $type->getName();
        $array = new $arrayClassName();
        foreach ($value as $offset => $element) {
            $array->offsetSet($offset, $element);
        }

        $property->setValue($object, $array);
    }

    /**
     * Hydrates the given property with the given date-time value
     *
     * @param HydrableObjectInterface $object
     * @param ReflectionClass $class
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     *
     * @return void
     *
     * @throws Exception\InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydratePropertyWithDateTime(
        HydrableObjectInterface $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_string($value) || false === strtotime($value)) {
            throw new Exception\InvalidValueException(sprintf(
                'The <%s.%s> property only accepts a valid date-time string.',
                $class->getShortName(),
                $property->getName(),
            ));
        }

        $dateTimeClassName = $type->getName();
        $dateTime = new $dateTimeClassName($value);
        $property->setValue($object, $dateTime);
    }

    /**
     * Hydrates the given property with the given one-to-one value
     *
     * @param HydrableObjectInterface $object
     * @param ReflectionClass $class
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     *
     * @return void
     *
     * @throws Exception\InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydratePropertyWithOneToOneAssociation(
        HydrableObjectInterface $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_array($value)) {
            throw new Exception\InvalidValueException(sprintf(
                'The <%s.%s> property only accepts an array.',
                $class->getShortName(),
                $property->getName(),
            ));
        }

        $childObjectClassName = $type->getName();
        $childObject = new $childObjectClassName();
        $this->hydrate($childObject, $value);
        $property->setValue($object, $childObject);
    }

    /**
     * Hydrates the given property with the given one-to-many value
     *
     * @param HydrableObjectInterface $object
     * @param ReflectionClass $class
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     *
     * @return void
     *
     * @throws Exception\InvalidValueException
     *         If the given value isn't valid.
     */
    private function hydratePropertyWithOneToManyAssociation(
        HydrableObjectInterface $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_array($value)) {
            throw new Exception\InvalidValueException(sprintf(
                'The <%s.%s> property only accepts an array.',
                $class->getShortName(),
                $property->getName(),
            ));
        }

        $objectCollectionClassName = $type->getName();
        $objectCollection = new $objectCollectionClassName();
        $collectionObjectClassName = $objectCollectionClassName::T;
        foreach ($value as $item) {
            $collectionObject = new $collectionObjectClassName();
            $this->hydrate($collectionObject, (array) $item);
            $objectCollection->add($collectionObject);
        }

        $property->setValue($object, $objectCollection);
    }
}
