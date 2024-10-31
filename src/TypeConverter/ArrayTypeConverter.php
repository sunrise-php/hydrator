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

namespace Sunrise\Hydrator\TypeConverter;

use Generator;
use stdClass;
use Sunrise\Hydrator\Annotation\Subtype;
use Sunrise\Hydrator\AnnotationReaderAwareInterface;
use Sunrise\Hydrator\AnnotationReaderInterface;
use Sunrise\Hydrator\Dictionary\BuiltinType;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorAwareInterface;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

use function count;
use function get_object_vars;
use function is_array;

/**
 * @since 3.1.0
 */
final class ArrayTypeConverter implements
    TypeConverterInterface,
    AnnotationReaderAwareInterface,
    HydratorAwareInterface
{
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private AnnotationReaderInterface $annotationReader;

    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private HydratorInterface $hydrator;

    public function setAnnotationReader(AnnotationReaderInterface $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    public function setHydrator(HydratorInterface $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritDoc
     *
     * @psalm-suppress MixedAssignment
     */
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        if ($type->getName() !== BuiltinType::ARRAY) {
            return;
        }

        // https://www.php.net/stdClass
        if ($value instanceof stdClass) {
            $value = get_object_vars($value);
        }

        if ($value === []) {
            return yield [];
        }

        if (!is_array($value)) {
            throw InvalidValueException::mustBeArray($path, $value);
        }

        /**
         * @phpstan-var Subtype|null $subtype
         * @psalm-suppress UnnecessaryVarAnnotation
         */
        $subtype = $this->annotationReader->getAnnotations(Subtype::class, $type->getHolder())->current();

        if ($subtype === null) {
            return yield $value;
        }

        if ($subtype->limit !== null && count($value) > $subtype->limit) {
            throw InvalidValueException::arrayOverflow($path, $subtype->limit, $value);
        }

        $violations = [];
        foreach ($value as $key => $element) {
            try {
                $value[$key] = $this->hydrator->castValue(
                    $element,
                    new Type($type->getHolder(), $subtype->name, $subtype->allowsNull),
                    [...$path, $key],
                    $context,
                );
            } catch (InvalidDataException $e) {
                $violations = [...$violations, ...$e->getExceptions()];
            } catch (InvalidValueException $e) {
                $violations[] = $e;
            }
        }

        if ($violations !== []) {
            throw new InvalidDataException('Invalid data', $violations);
        }

        yield $value;
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 20;
    }
}
