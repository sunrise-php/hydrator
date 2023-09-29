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

use ReflectionProperty;

use function sprintf;

/**
 * UnsupportedPropertyTypeException
 */
class UnsupportedPropertyTypeException extends InvalidObjectException
{

    /**
     * @param ReflectionProperty $property
     * @param string $typeName
     *
     * @return self
     */
    public static function unsupportedType(ReflectionProperty $property, string $typeName): self
    {
        return new self(sprintf(
            'The property %s.%s contains an unsupported type %s.',
            $property->getDeclaringClass()->getName(),
            $property->getName(),
            $typeName,
        ));
    }

    /**
     * @param ReflectionProperty $property
     * @param string $className
     *
     * @return self
     */
    public static function nonInstantiableClass(ReflectionProperty $property, string $className): self
    {
        return new self(sprintf(
            'The property %s.%s refers to a non-instantiable class %s.',
            $property->getDeclaringClass()->getName(),
            $property->getName(),
            $className,
        ));
    }
}
