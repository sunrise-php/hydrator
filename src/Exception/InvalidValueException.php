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

/**
 * InvalidValueException
 */
class InvalidValueException extends RuntimeException implements ExceptionInterface
{

    /**
     * @var string
     */
    private string $errorCode;

    /**
     * @var list<array-key>
     */
    private array $propertyPath;

    /**
     * @var string
     */
    private string $messageTemplate;

    /**
     * @var array<string, int|float|string>
     */
    private array $messagePlaceholders;

    /**
     * Constructor of the class
     *
     * @param string $message
     * @param string $errorCode
     * @param list<array-key> $propertyPath
     * @param string $messageTemplate
     * @param array<string, int|float|string> $messagePlaceholders
     */
    public function __construct(
        string $message,
        string $errorCode,
        array $propertyPath,
        string $messageTemplate,
        array $messagePlaceholders
    ) {
        parent::__construct($message);

        $this->errorCode = $errorCode;
        $this->propertyPath = $propertyPath;
        $this->messageTemplate = $messageTemplate;
        $this->messagePlaceholders = $messagePlaceholders;
    }

    /**
     * @return string
     */
    final public function getPropertyPath(): string
    {
        return join('.', $this->propertyPath);
    }

    /**
     * @return string
     *
     * @since 3.0.0
     */
    final public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return string
     *
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
            null,
            null,
            $this->getErrorCode(),
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
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
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function mustNotBeEmpty(array $propertyPath): self
    {
        return new self(
            ErrorMessage::MUST_NOT_BE_EMPTY,
            ErrorCode::MUST_NOT_BE_EMPTY,
            $propertyPath,
            ErrorMessage::MUST_NOT_BE_EMPTY,
            [],
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function mustBeBoolean(array $propertyPath): self
    {
        return new self(
            ErrorMessage::MUST_BE_BOOLEAN,
            ErrorCode::MUST_BE_BOOLEAN,
            $propertyPath,
            ErrorMessage::MUST_BE_BOOLEAN,
            [],
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function mustBeInteger(array $propertyPath): self
    {
        return new self(
            ErrorMessage::MUST_BE_INTEGER,
            ErrorCode::MUST_BE_INTEGER,
            $propertyPath,
            ErrorMessage::MUST_BE_INTEGER,
            [],
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function mustBeNumber(array $propertyPath): self
    {
        return new self(
            ErrorMessage::MUST_BE_NUMBER,
            ErrorCode::MUST_BE_NUMBER,
            $propertyPath,
            ErrorMessage::MUST_BE_NUMBER,
            [],
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function mustBeString(array $propertyPath): self
    {
        return new self(
            ErrorMessage::MUST_BE_STRING,
            ErrorCode::MUST_BE_STRING,
            $propertyPath,
            ErrorMessage::MUST_BE_STRING,
            [],
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function mustBeArray(array $propertyPath): self
    {
        return new self(
            ErrorMessage::MUST_BE_ARRAY,
            ErrorCode::MUST_BE_ARRAY,
            $propertyPath,
            ErrorMessage::MUST_BE_ARRAY,
            [],
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param int<0, max> $maximumElements
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function arrayOverflow(array $propertyPath, int $maximumElements): self
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
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param list<int|string> $expectedValues
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function invalidChoice(array $propertyPath, array $expectedValues): self
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
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param string $expectedFormat
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function invalidTimestamp(array $propertyPath, string $expectedFormat): self
    {
        $placeholders = [
            '{{ expected_format }}' => $expectedFormat,
        ];

        return new self(
            strtr(ErrorMessage::INVALID_TIMESTAMP, $placeholders),
            ErrorCode::INVALID_TIMESTAMP,
            $propertyPath,
            ErrorMessage::INVALID_TIMESTAMP,
            $placeholders,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function invalidTimezone(array $propertyPath): self
    {
        return new self(
            ErrorMessage::INVALID_TIMEZONE,
            ErrorCode::INVALID_TIMEZONE,
            $propertyPath,
            ErrorMessage::INVALID_TIMEZONE,
            [],
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     *
     * @since 3.0.0
     */
    final public static function invalidUid(array $propertyPath): self
    {
        return new self(
            ErrorMessage::INVALID_UID,
            ErrorCode::INVALID_UID,
            $propertyPath,
            ErrorMessage::INVALID_UID,
            [],
        );
    }
}
