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

use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use Sunrise\Hydrator\Dictionary\BuiltinType;

/**
 * @since 3.1.0
 */
final class Type
{
    /**
     * @var mixed
     */
    private $holder;

    private string $name;

    private bool $allowsNull;

    /**
     * @param mixed $holder
     */
    public function __construct($holder, string $name, bool $allowsNull)
    {
        $this->holder = $holder;
        $this->name = $name;
        $this->allowsNull = $allowsNull;
    }

    /**
     * @since 3.6.0
     */
    public static function fromName(string $name, bool $allowsNull = false): self
    {
        return new Type(null, $name, $allowsNull);
    }

    /**
     * @since 3.4.0
     */
    public static function fromParameter(ReflectionParameter $parameter): self
    {
        $type = $parameter->getType();
        if ($type === null) {
            return new Type($parameter, BuiltinType::MIXED, true);
        }

        if ($type instanceof ReflectionNamedType) {
            return new Type($parameter, $type->getName(), $type->allowsNull());
        }

        return new Type($parameter, (string) $type, $type->allowsNull());
    }

    /**
     * @since 3.4.0
     */
    public static function fromProperty(ReflectionProperty $property): self
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
     * @return mixed
     */
    public function getHolder()
    {
        return $this->holder;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }
}
