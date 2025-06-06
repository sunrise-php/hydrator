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
use DateTimeInterface;
use DateTimeZone;
use Generator;
use Sunrise\Hydrator\Annotation\Format;
use Sunrise\Hydrator\AnnotationReaderAwareInterface;
use Sunrise\Hydrator\AnnotationReaderInterface;
use Sunrise\Hydrator\Dictionary\ContextKey;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

use function filter_var;
use function is_a;
use function is_int;
use function is_string;
use function preg_replace;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;
use const PHP_MAJOR_VERSION;

/**
 * @since 3.1.0
 */
final class TimestampTypeConverter implements TypeConverterInterface, AnnotationReaderAwareInterface
{
    /**
     * The default timestamp format
     *
     * @var non-empty-string
     */
    public const DEFAULT_FORMAT = DateTimeInterface::RFC3339_EXTENDED;

    private AnnotationReaderInterface $annotationReader;

    public function setAnnotationReader(AnnotationReaderInterface $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @inheritDoc
     */
    public function castValue($value, Type $type, array $path, array $context): Generator
    {
        /** @var array{timestamp_format?: non-empty-string, timezone?: non-empty-string} $context */

        $className = $type->getName();
        if (!is_a($className, DateTimeImmutable::class, true)) {
            return;
        }

        // The DateTimeImmutable::createFromFormat method returns self instead of static...
        if (PHP_MAJOR_VERSION === 7 && $className !== DateTimeImmutable::class) {
            throw InvalidObjectException::unsupportedType($type);
        }

        $format = $this->annotationReader->getAnnotations(Format::class, $type->getHolder())->current()->value
            ?? $context[ContextKey::TIMESTAMP_FORMAT]
            ?? self::DEFAULT_FORMAT;

        if (is_string($value)) {
            $value = trim($value);

            // As part of the support for HTML forms and other untyped data sources,
            // empty strings should not be used to instantiate timestamps;
            // instead, they should be considered as NULL.
            if ($value === '') {
                if ($type->allowsNull()) {
                    return yield null;
                }

                throw InvalidValueException::mustNotBeEmpty($path, $value);
            }

            // Support for ISO 8601
            $value = preg_replace('/((?:^|\D)\d{2}:\d{2}:\d{2}[.]\d{6})\d+/', '$1', $value);

            if ($format === 'U') {
                // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L94
                // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L197
                $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            }
        }

        if ($format === 'U' && !is_int($value)) {
            throw InvalidValueException::mustBeInteger($path, $value);
        }
        if ($format !== 'U' && !is_string($value)) {
            throw InvalidValueException::mustBeString($path, $value);
        }

        // @phpstan-ignore cast.string
        $value = (string) $value;

        $timezone = null;
        if (isset($context[ContextKey::TIMEZONE])) {
            $timezone = new DateTimeZone($context[ContextKey::TIMEZONE]);
        }

        $timestamp = $className::createFromFormat($format, $value, $timezone);
        if ($timestamp === false) {
            throw InvalidValueException::invalidTimestamp($path, $format, $value);
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
