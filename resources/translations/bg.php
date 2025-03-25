<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Тази стойност трябва да бъде предоставена.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Тази стойност не трябва да бъде празна.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Тази стойност трябва да бъде от тип boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Тази стойност трябва да бъде от тип integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Тази стойност трябва да бъде от тип number.',
    ErrorMessage::MUST_BE_STRING => 'Тази стойност трябва да бъде от тип string.',
    ErrorMessage::MUST_BE_ARRAY => 'Тази стойност трябва да бъде от тип array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Стойността е ограничена до {{ maximum_elements }} елементи.',
    ErrorMessage::INVALID_CHOICE => 'Тази стойност не е валиден избор; очаквани стойности: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Тази стойност не е валиден timestamp; очакван формат: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Тази стойност не е валидна часова зона.',
    ErrorMessage::INVALID_UID => 'Това стойност няма валиден UID.',
];
