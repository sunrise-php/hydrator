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

use DateTimeImmutable;
use Generator;
use Sunrise\Hydrator\Annotation\Format;
use Sunrise\Hydrator\AnnotationReaderAwareInterface;
use Sunrise\Hydrator\AnnotationReaderInterface;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Exception\UnsupportedPropertyTypeException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

use function filter_var;
use function is_a;
use function is_int;
use function is_string;
use function sprintf;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;

/**
 * @since 3.1.0
 *
 * @psalm-suppress MissingConstructor
 */
final class TimestampTypeConverter implements TypeConverterInterface, AnnotationReaderAwareInterface
{

    /**
     * @var AnnotationReaderInterface
     */
    private AnnotationReaderInterface $annotationReader;

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
    public function castValue($value, Type $type, array $path): Generator
    {
        $className = $type->getName();
        if (!is_a($className, DateTimeImmutable::class, true)) {
            return;
        }

        $format = $this->annotationReader->getAnnotations($type->getHolder(), Format::class)->current();
        if ($format === null) {
            throw new UnsupportedPropertyTypeException(sprintf(
                'The property %1$s.%2$s must contain the attribute %3$s, ' .
                'for example: #[\%3$s(\DateTimeInterface::DATE_RFC3339)].',
                $type->getHolder()->getDeclaringClass()->getName(),
                $type->getHolder()->getName(),
                Format::class,
            ));
        }

        if (is_string($value)) {
            $value = trim($value);

            // As part of the support for HTML forms and other untyped data sources,
            // empty strings should not be used to instantiate timestamps;
            // instead, they should be considered as NULL.
            if ($value === '') {
                if ($type->allowsNull()) {
                    return yield null;
                }

                throw InvalidValueException::shouldNotBeEmpty($path);
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

        $timestamp = $className::createFromFormat($format->value, (string) $value);
        if ($timestamp === false) {
            throw InvalidValueException::invalidTimestamp($path, $format->value);
        }

        yield $timestamp;
    }

    /**
     * @inheritDoc
     */
    public function getWeight(): int
    {
        return 50;
    }
}
