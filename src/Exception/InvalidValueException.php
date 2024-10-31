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

use Sunrise\Hydrator\Dictionary\ErrorCode;
use Sunrise\Hydrator\Dictionary\ErrorMessage;
use RuntimeException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;

use function join;
use function strtr;

class InvalidValueException extends RuntimeException implements ExceptionInterface
{
    /**
     * @see ErrorCode
     */
    private string $errorCode;

    /**
     * @var list<array-key>
     */
    private array $propertyPath;

    /**
     * @see ErrorMessage
     */
    private string $messageTemplate;

    /**
     * @var array<string, int|float|string>
     */
    private array $messagePlaceholders;

    /**
     * @var mixed
     */
    private $invalidValue;

    /**
     * @param list<array-key> $propertyPath
     * @param array<string, int|float|string> $messagePlaceholders
     * @param mixed $invalidValue
     */
    public function __construct(
        string $message,
        string $errorCode,
        array $propertyPath,
        string $messageTemplate,
        array $messagePlaceholders,
        $invalidValue = null
    ) {
        parent::__construct($message);

        $this->errorCode = $errorCode;
        $this->propertyPath = $propertyPath;
        $this->messageTemplate = $messageTemplate;
        $this->messagePlaceholders = $messagePlaceholders;
        $this->invalidValue = $invalidValue;
    }

    final public function getPropertyPath(): string
    {
        return join('.', $this->propertyPath);
    }

    /**
     * @since 3.0.0
     */
    final public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @since 3.7.0
     */
    final public function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    /**
     * @return array<string, int|float|string>
     *
     * @since 3.7.0
     */
    final public function getMessagePlaceholders(): array
    {
        return $this->messagePlaceholders;
    }

    /**
     * @return mixed
     *
     * @since 3.13.0
     */
    final public function getInvalidValue()
    {
        return $this->invalidValue;
    }

    /**
     * @since 3.9.0
     */
    final public function getViolation(): ConstraintViolationInterface
    {
        return new ConstraintViolation(
            $this->getMessage(),
            $this->getMessageTemplate(),
            $this->getMessagePlaceholders(),
            null,
            $this->getPropertyPath(),
            $this->getInvalidValue(),
            null,
            $this->getErrorCode(),
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @since 3.0.0
     */
    final public static function mustBeProvided(array $propertyPath): self
    {
        return new self(
            ErrorMessage::MUST_BE_PROVIDED,
            ErrorCode::MUST_BE_PROVIDED,
            $propertyPath,
            ErrorMessage::MUST_BE_PROVIDED,
            [],
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function mustNotBeEmpty(array $propertyPath, $invalidValue = null): self
    {
        return new self(
            ErrorMessage::MUST_NOT_BE_EMPTY,
            ErrorCode::MUST_NOT_BE_EMPTY,
            $propertyPath,
            ErrorMessage::MUST_NOT_BE_EMPTY,
            [],
            $invalidValue,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function mustBeBoolean(array $propertyPath, $invalidValue = null): self
    {
        return new self(
            ErrorMessage::MUST_BE_BOOLEAN,
            ErrorCode::MUST_BE_BOOLEAN,
            $propertyPath,
            ErrorMessage::MUST_BE_BOOLEAN,
            [],
            $invalidValue,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function mustBeInteger(array $propertyPath, $invalidValue = null): self
    {
        return new self(
            ErrorMessage::MUST_BE_INTEGER,
            ErrorCode::MUST_BE_INTEGER,
            $propertyPath,
            ErrorMessage::MUST_BE_INTEGER,
            [],
            $invalidValue,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function mustBeNumber(array $propertyPath, $invalidValue = null): self
    {
        return new self(
            ErrorMessage::MUST_BE_NUMBER,
            ErrorCode::MUST_BE_NUMBER,
            $propertyPath,
            ErrorMessage::MUST_BE_NUMBER,
            [],
            $invalidValue,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function mustBeString(array $propertyPath, $invalidValue = null): self
    {
        return new self(
            ErrorMessage::MUST_BE_STRING,
            ErrorCode::MUST_BE_STRING,
            $propertyPath,
            ErrorMessage::MUST_BE_STRING,
            [],
            $invalidValue,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function mustBeArray(array $propertyPath, $invalidValue = null): self
    {
        return new self(
            ErrorMessage::MUST_BE_ARRAY,
            ErrorCode::MUST_BE_ARRAY,
            $propertyPath,
            ErrorMessage::MUST_BE_ARRAY,
            [],
            $invalidValue,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param int<0, max> $maximumElements
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function arrayOverflow(array $propertyPath, int $maximumElements, $invalidValue = null): self
    {
        $placeholders = [
            '{{ maximum_elements }}' => $maximumElements,
        ];

        return new self(
            strtr(ErrorMessage::ARRAY_OVERFLOW, $placeholders),
            ErrorCode::ARRAY_OVERFLOW,
            $propertyPath,
            ErrorMessage::ARRAY_OVERFLOW,
            $placeholders,
            $invalidValue,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param list<int|string> $expectedValues
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function invalidChoice(array $propertyPath, array $expectedValues, $invalidValue = null): self
    {
        $placeholders = [
            '{{ expected_values }}' => join(', ', $expectedValues),
        ];

        return new self(
            strtr(ErrorMessage::INVALID_CHOICE, $placeholders),
            ErrorCode::INVALID_CHOICE,
            $propertyPath,
            ErrorMessage::INVALID_CHOICE,
            $placeholders,
            $invalidValue,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function invalidTimestamp(
        array $propertyPath,
        string $expectedFormat,
        $invalidValue = null
    ): self {
        $placeholders = [
            '{{ expected_format }}' => $expectedFormat,
        ];

        return new self(
            strtr(ErrorMessage::INVALID_TIMESTAMP, $placeholders),
            ErrorCode::INVALID_TIMESTAMP,
            $propertyPath,
            ErrorMessage::INVALID_TIMESTAMP,
            $placeholders,
            $invalidValue,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function invalidTimezone(array $propertyPath, $invalidValue = null): self
    {
        return new self(
            ErrorMessage::INVALID_TIMEZONE,
            ErrorCode::INVALID_TIMEZONE,
            $propertyPath,
            ErrorMessage::INVALID_TIMEZONE,
            [],
            $invalidValue,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param mixed $invalidValue
     *
     * @since 3.0.0
     */
    final public static function invalidUid(array $propertyPath, $invalidValue = null): self
    {
        return new self(
            ErrorMessage::INVALID_UID,
            ErrorCode::INVALID_UID,
            $propertyPath,
            ErrorMessage::INVALID_UID,
            [],
            $invalidValue,
        );
    }
}
