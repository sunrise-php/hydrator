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

namespace Sunrise\Hydrator\Exception;

use LogicException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Sunrise\Hydrator\Type;

use function sprintf;

class InvalidObjectException extends LogicException implements ExceptionInterface
{
    /**
     * @since 3.2.0
     */
    final public static function uninstantiableObject(string $className): self
    {
        return new self(sprintf(
            'The uninstantiable class %s cannot be hydrated.',
            $className,
        ));
    }

    /**
     * @since 3.2.0
     */
    final public static function unsupportedType(Type $type): self
    {
        /** @var mixed $holder */
        $holder = $type->getHolder();

        if ($holder instanceof ReflectionProperty) {
            return self::unsupportedPropertyType($type, $holder);
        }
        if ($holder instanceof ReflectionParameter) {
            return self::unsupportedParameterType($type, $holder);
        }

        return new self(sprintf(
            'The type {%s} is not supported.',
            $type->getName(),
        ));
    }

    /**
     * @since 3.2.0
     */
    final public static function unsupportedPropertyType(Type $type, ReflectionProperty $property): self
    {
        return new self(sprintf(
            'The property {%s::$%s} is associated with an unsupported type {%s}.',
            $property->getDeclaringClass()->getName(),
            $property->getName(),
            $type->getName(),
        ));
    }

    /**
     * @since 3.2.0
     */
    final public static function unsupportedParameterType(Type $type, ReflectionParameter $parameter): self
    {
        $holder = $parameter->getDeclaringFunction();

        return $holder instanceof ReflectionMethod ?
            self::unsupportedMethodParameterType($type, $parameter, $holder) :
            self::unsupportedFunctionParameterType($type, $parameter, $holder);
    }

    /**
     * @since 3.2.0
     */
    final public static function unsupportedMethodParameterType(
        Type $type,
        ReflectionParameter $parameter,
        ReflectionMethod $method
    ): self {
        return new self(sprintf(
            'The parameter {%s::%s($%s[%d])} is associated with an unsupported type {%s}.',
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $parameter->getName(),
            $parameter->getPosition(),
            $type->getName(),
        ));
    }

    /**
     * @since 3.2.0
     */
    final public static function unsupportedFunctionParameterType(
        Type $type,
        ReflectionParameter $parameter,
        ReflectionFunctionAbstract $function
    ): self {
        return new self(sprintf(
            'The parameter {%s($%s[%d])} is associated with an unsupported type {%s}.',
            $function->getName(),
            $parameter->getName(),
            $parameter->getPosition(),
            $type->getName(),
        ));
    }
}
