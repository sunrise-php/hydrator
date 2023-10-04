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
use function is_array;

/**
 * @since 3.1.0
 *
 * @psalm-suppress MissingConstructor
 */
final class ArrayTypeConverter implements
    TypeConverterInterface,
    AnnotationReaderAwareInterface,
    HydratorAwareInterface
{

    /**
     * @var AnnotationReaderInterface
     */
    private AnnotationReaderInterface $annotationReader;

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    /**
     * @inheritDoc
     */
    public function setAnnotationReader(AnnotationReaderInterface $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @inheritDoc
     */
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
        if ($type->getName() <> BuiltinType::ARRAY) {
            return;
        }

        if ($value === []) {
            return yield [];
        }

        if (!is_array($value)) {
            throw InvalidValueException::mustBeArray($path);
        }

        $subtype = $this->annotationReader->getAnnotations(Subtype::class, $type->getHolder())->current();
        if ($subtype === null) {
            return yield $value;
        }

        if (isset($subtype->limit) && count($value) > $subtype->limit) {
            throw InvalidValueException::arrayOverflow($path, $subtype->limit);
        }

        $violations = [];
        foreach ($value as $key => $element) {
            try {
                $value[$key] = $this->hydrator->castValue(
                    $element,
                    new Type($type->getHolder(), $subtype->name, false),
                    [...$path, $key],
                    $context,
                );
            } catch (InvalidDataException $e) {
                $violations = [...$violations, ...$e->getExceptions()];
            } catch (InvalidValueException $e) {
                $violations[] = $e;
            }
        }

        if ($violations === []) {
            return yield $value;
        }

        throw new InvalidDataException('Invalid data', $violations);
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 20;
    }
}
