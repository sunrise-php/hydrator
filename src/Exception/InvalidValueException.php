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
use RuntimeException;

use function join;
use function sprintf;

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
     * Constructor of the class
     *
     * @param string $message
     * @param string $errorCode
     * @param list<array-key> $propertyPath
     */
    public function __construct(string $message, string $errorCode, array $propertyPath)
    {
        parent::__construct($message);

        $this->errorCode = $errorCode;
        $this->propertyPath = $propertyPath;
    }

    /**
     * @return string
     */
    final public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    final public function getPropertyPath(): string
    {
        return join('.', $this->propertyPath);
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function mustBeProvided(array $propertyPath): self
    {
        return new self(
            'This value must be provided.',
            ErrorCode::MUST_BE_PROVIDED,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function mustNotBeEmpty(array $propertyPath): self
    {
        return new self(
            'This value must not be empty.',
            ErrorCode::MUST_NOT_BE_EMPTY,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function mustBeBoolean(array $propertyPath): self
    {
        return new self(
            'This value must be of type boolean.',
            ErrorCode::MUST_BE_BOOLEAN,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function mustBeInteger(array $propertyPath): self
    {
        return new self(
            'This value must be of type integer.',
            ErrorCode::MUST_BE_INTEGER,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function mustBeNumber(array $propertyPath): self
    {
        return new self(
            'This value must be of type number.',
            ErrorCode::MUST_BE_NUMBER,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function mustBeString(array $propertyPath): self
    {
        return new self(
            'This value must be of type string.',
            ErrorCode::MUST_BE_STRING,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function mustBeArray(array $propertyPath): self
    {
        return new self(
            'This value must be of type array.',
            ErrorCode::MUST_BE_ARRAY,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     * @param int<0, max> $limit
     *
     * @return self
     */
    final public static function arrayOverflow(array $propertyPath, int $limit): self
    {
        return new self(
            sprintf('This value is limited to %d elements.', $limit),
            ErrorCode::ARRAY_OVERFLOW,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function invalidChoice(array $propertyPath): self
    {
        return new self(
            'This value is not a valid choice.',
            ErrorCode::INVALID_CHOICE,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function invalidTimestamp(array $propertyPath): self
    {
        return new self(
            'This value is not a valid timestamp.',
            ErrorCode::INVALID_TIMESTAMP,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function invalidTimezone(array $propertyPath): self
    {
        return new self(
            'This value is not a valid timezone.',
            ErrorCode::INVALID_TIMEZONE,
            $propertyPath,
        );
    }

    /**
     * @param list<array-key> $propertyPath
     *
     * @return self
     */
    final public static function invalidUid(array $propertyPath): self
    {
        return new self(
            'This value is not a valid UID.',
            ErrorCode::INVALID_UID,
            $propertyPath,
        );
    }
}
