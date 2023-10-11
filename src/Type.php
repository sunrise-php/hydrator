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
     * The type holder
     *
     * @var ReflectionParameter|ReflectionProperty
     */
    private $holder;

    /**
     * The type name
     *
     * @var string
     */
    private string $name;

    /**
     * Indicates whether the type allows null
     *
     * @var bool
     */
    private bool $allowsNull;

    /**
     * Constructor of the class
     *
     * @param ReflectionParameter|ReflectionProperty $holder
     * @param string $name
     * @param bool $allowsNull
     */
    public function __construct($holder, string $name, bool $allowsNull)
    {
        $this->holder = $holder;
        $this->name = $name;
        $this->allowsNull = $allowsNull;
    }

    /**
     * Creates a new type from the given property
     *
     * @param ReflectionProperty $property
     *
     * @return self
     *
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
     * Creates a new type from the given parameter
     *
     * @param ReflectionParameter $parameter
     *
     * @return self
     *
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
     * Gets the type holder
     *
     * @return ReflectionParameter|ReflectionProperty
     */
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * Gets the type name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Checks if the type allows null
     *
     * @return bool
     */
    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }
}
