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
use JsonSerializable;
use ReflectionClass;
use RuntimeException;

/**
 * Import functions
 */
use function is_int;
use function is_string;
use function sprintf;

/**
 * Abstract enum
 *
 * @since 2.6.0
 */
abstract class Enum implements JsonSerializable
{

    /**
     * Cached cases of the enum
     *
     * @var array<class-string<static>, list<static>>
     */
    private static array $cases = [];

    /**
     * The name of the enum's case
     *
     * @var string
     *
     * @readonly
     */
    private string $name;

    /**
     * The value of the enum's case
     *
     * @var int|string
     *
     * @readonly
     */
    private $value;

    /**
     * Constructor of the class
     *
     * @param string $name
     * @param int|string $value
     */
    final protected function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Gets the name of the enum's case
     *
     * @return string
     */
    final public function name(): string
    {
        return $this->name;
    }

    /**
     * Gets the value of the enum's case
     *
     * @return int|string
     */
    final public function value()
    {
        return $this->value;
    }

    /**
     * Gets all cases of the enum
     *
     * @return list<static>
     */
    final public static function cases(): array
    {
        if (isset(self::$cases[static::class])) {
            return self::$cases[static::class];
        }

        $class = new ReflectionClass(static::class);
        $constants = $class->getReflectionConstants();
        foreach ($constants as $constant) {
            $owner = $constant->getDeclaringClass();
            if ($owner->getName() === self::class) {
                continue;
            }

            $name = $constant->getName();
            $value = $constant->getValue();

            if (!is_int($value) && !is_string($value)) {
                continue;
            }

            self::$cases[static::class][] = new static($name, $value);
        }

        return self::$cases[static::class];
    }

    /**
     * Tries to initialize the enum from the given case's value
     *
     * @param int|string $value
     *
     * @return static|null
     */
    final public static function tryFrom($value): ?static
    {
        foreach (self::cases() as $case) {
            if ($case->value == $value) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Tries to initialize the enum from the given case's name
     *
     * @param string $name
     *
     * @return static
     *
     * @throws RuntimeException
     */
    final public static function __callStatic(string $name, array $arguments = []): static
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        throw new RuntimeException(sprintf('Enum case %1$s::%2$s not found', static::class, $name));
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}
