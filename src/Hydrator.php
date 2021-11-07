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
use Sunrise\Hydrator\Annotation\Alias;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * Import functions
 */
use function array_key_exists;
use function class_exists;
use function ctype_digit;
use function filter_var;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function is_subclass_of;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function sprintf;
use function strtotime;

/**
 * Import constants
 */
use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;
use const JSON_ERROR_NONE;
use const PHP_MAJOR_VERSION;

/**
 * Hydrator
 */
class Hydrator implements HydratorInterface
{

    /**
     * @var array<string, string>
     */
    private const PROPERTY_HYDRATOR_MAP = [
        'bool' => 'hydratePropertyWithBooleanValue',
        'int' => 'hydratePropertyWithIntegerNumber',
        'float' => 'hydratePropertyWithNumber',
        'string' => 'hydratePropertyWithString',
        'array' => 'hydratePropertyWithArray',
        'object' => 'hydratePropertyWithObject',
        'DateTime' => 'hydratePropertyWithTimestamp',
        'DateTimeImmutable' => 'hydratePropertyWithTimestamp',
    ];

    /**
     * @var SimpleAnnotationReader|null
     */
    private $annotationReader = null;

    /**
     * Enables support for annotations
     *
     * @return self
     */
    public function useAnnotations() : self
    {
        if (isset($this->annotationReader)) {
            return $this;
        }

        if (class_exists(SimpleAnnotationReader::class)) {
            $this->annotationReader = /** @scrutinizer ignore-deprecated */ new SimpleAnnotationReader();
            $this->annotationReader->addNamespace('Sunrise\Hydrator\Annotation');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception\UntypedPropertyException
     *         If one of the object properties isn't typed.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If one of the object properties contains an unsupported type.
     *
     * @throws Exception\MissingRequiredValueException
     *         If the given data doesn't contain required value.
     *
     * @throws Exception\InvalidValueException
     *         If the given data contains an invalid value.
     */
    public function hydrate($object, array $data) : object
    {
        $object = $this->initializeObject($object);

        $class = new ReflectionClass($object);
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            // statical properties cannot be hydrated...
            if ($property->isStatic()) {
                continue;
            }

            $property->setAccessible(true);

            if (!$property->hasType()) {
                throw new Exception\UntypedPropertyException(sprintf(
                    'The %s.%s property is not typed.',
                    $class->getShortName(),
                    $property->getName()
                ));
            }

            if ($property->getType() instanceof ReflectionUnionType) {
                throw new Exception\UnsupportedPropertyTypeException(sprintf(
                    'The %s.%s property contains an union type that is not supported.',
                    $class->getShortName(),
                    $property->getName()
                ));
            }

            $key = $property->getName();
            if (!array_key_exists($key, $data)) {
                $alias = $this->getPropertyAlias($property);
                if (isset($alias)) {
                    $key = $alias->value;
                }
            }

            if (!array_key_exists($key, $data)) {
                if (!$property->isInitialized($object)) {
                    throw new Exception\MissingRequiredValueException($property, sprintf(
                        'The %s.%s property is required.',
                        $class->getShortName(),
                        $property->getName()
                    ));
                }

                continue;
            }

            $this->hydrateProperty($object, $class, $property, $property->getType(), $data[$key]);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     *         If the given JSON cannot be decoded.
     */
    public function hydrateWithJson($object, string $json, int $options = 0) : object
    {
        json_decode(''); // reset previous error...
        $data = (array) json_decode($json, true, 512, $options);
        if (JSON_ERROR_NONE <> json_last_error()) {
            throw new InvalidArgumentException(sprintf(
                'Unable to decode JSON: %s',
                json_last_error_msg()
            ));
        }

        return $this->hydrate($object, $data);
    }

    /**
     * Initializes the given object
     *
     * @param object|class-string $object
     *
     * @return object
     *
     * @throws InvalidArgumentException
     *         If the given object cannot be initialized.
     */
    private function initializeObject($object) : object
    {
        if (is_object($object)) {
            return $object;
        }

        if (!is_string($object) || !class_exists($object)) {
            throw new InvalidArgumentException(sprintf(
                'The method %s::hydrate() expects an object or name of an existing class.',
                __CLASS__
            ));
        }

        $class = new ReflectionClass($object);
        $constructor = $class->getConstructor();
        if (isset($constructor) && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new InvalidArgumentException(sprintf(
                'The object %s cannot be hydrated because its constructor has required parameters.',
                $class->getName()
            ));
        }

        return $class->newInstance();
    }

    /**
     * Gets an alias for the given property
     *
     * @param ReflectionProperty $property
     *
     * @return Alias|null
     */
    private function getPropertyAlias(ReflectionProperty $property) : ?Alias
    {
        if (PHP_MAJOR_VERSION >= 8) {
            $attributes = $property->getAttributes(Alias::class);
            if (isset($attributes[0])) {
                return $attributes[0]->newInstance();
            }
        }

        if (isset($this->annotationReader)) {
            $annotation = $this->annotationReader->getPropertyAnnotation($property, Alias::class);
            if (isset($annotation)) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * Hydrates the given property with the given value
     *
     * @param object $object
     * @param ReflectionClass $class
     * @param ReflectionProperty $property
     * @param ReflectionNamedType $type
     * @param mixed $value
     *
     * @return void
     *
     * @throws Exception\InvalidValueException
     *         If the given value isn't valid.
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *         If the given property contains an unsupported type.
     */
    private function hydrateProperty(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (null === $value) {
            $this->hydratePropertyWithNull($object, $class, $property, $type);
            return;
        }

        if (isset(self::PROPERTY_HYDRATOR_MAP[$type->getName()])) {
            $this->{self::PROPERTY_HYDRATOR_MAP[$type->getName()]}($object, $class, $property, $type, $value);
            return;
        }

        if (is_subclass_of($type->getName(), ObjectCollectionInterface::class)) {
            $this->hydratePropertyWithManyAssociations($object, $class, $property, $type, $value);
            return;
        }

        if (class_exists($type->getName())) {
            $this->hydratePropertyWithOneAssociation($object, $class, $property, $type, $value);
            return;
        }

        throw new Exception\UnsupportedPropertyTypeException(sprintf(
            'The %s.%s property contains an unsupported type %s.',
            $class->getShortName(),
            $property->getName(),
            $type->getName()
        ));
    }

    /**
     * Hydrates the given property with null
     *
     * @param object $object
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
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type
    ) : void {
        if (!$type->allowsNull()) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property cannot accept null.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $property->setValue($object, null);
    }

    /**
     * Hydrates the given property with the given boolean value
     *
     * @param object $object
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
    private function hydratePropertyWithBooleanValue(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_bool($value)) {
            // if the value isn't boolean, then we will use filter_var, because it will give us the ability to specify
            // boolean values as strings. this behavior is great for html forms. details at:
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L273
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if (!isset($value)) {
                throw new Exception\InvalidValueException($property, sprintf(
                    'The %s.%s property accepts a boolean value only.',
                    $class->getShortName(),
                    $property->getName()
                ));
            }
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given integer number
     *
     * @param object $object
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
    private function hydratePropertyWithIntegerNumber(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_int($value)) {
            // it's senseless to convert the value type if it's not a number, so we will use filter_var to correct
            // converting the value type to int. also remember that string numbers must be between PHP_INT_MIN and
            // PHP_INT_MAX, otherwise the result will be null. this behavior is great for html forms. details at:
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L197
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L94
            $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            if (!isset($value)) {
                throw new Exception\InvalidValueException($property, sprintf(
                    'The %s.%s property accepts an integer number only.',
                    $class->getShortName(),
                    $property->getName()
                ));
            }
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given number
     *
     * @param object $object
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
    private function hydratePropertyWithNumber(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_float($value)) {
            // it's senseless to convert the value type if it's not a number, so we will use filter_var to correct
            // converting the value type to float. this behavior is great for html forms. details at:
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L342
            $value = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

            if (!isset($value)) {
                throw new Exception\InvalidValueException($property, sprintf(
                    'The %s.%s property accepts a number only.',
                    $class->getShortName(),
                    $property->getName()
                ));
            }
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given string
     *
     * @param object $object
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
    private function hydratePropertyWithString(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_string($value)) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property accepts a string only.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given array
     *
     * @param object $object
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
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_array($value)) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property accepts an array only.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given object
     *
     * @param object $object
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
    private function hydratePropertyWithObject(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_object($value)) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property accepts an object only.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given timestamp
     *
     * @param object $object
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
    private function hydratePropertyWithTimestamp(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        $prototype = $type->getName();

        if (is_int($value) || ctype_digit($value)) {
            $property->setValue($object, (new $prototype)->setTimestamp((int) $value));
            return;
        }

        if (is_string($value) && false !== strtotime($value)) {
            $property->setValue($object, new $prototype($value));
            return;
        }

        throw new Exception\InvalidValueException($property, sprintf(
            'The %s.%s property accepts a valid date-time string or a timestamp only.',
            $class->getShortName(),
            $property->getName()
        ));
    }

    /**
     * Hydrates the given property with the given many associations
     *
     * @param object $object
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
    private function hydratePropertyWithManyAssociations(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_array($value)) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property accepts an array only.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $prototype = $type->getName();
        $collection = new $prototype();
        foreach ($value as $key => $item) {
            if (!is_array($item)) {
                throw new Exception\InvalidValueException($property, sprintf(
                    'The %s.%s property accepts an array with arrays only.',
                    $class->getShortName(),
                    $property->getName()
                ));
            }

            $collection->add($key, $this->hydrate($collection->getItemClassName(), $item));
        }

        $property->setValue($object, $collection);
    }

    /**
     * Hydrates the given property with the given one association
     *
     * @param object $object
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
    private function hydratePropertyWithOneAssociation(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        $value
    ) : void {
        if (!is_array($value)) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property accepts an array only.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $property->setValue($object, $this->hydrate($type->getName(), $value));
    }
}
