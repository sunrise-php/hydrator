<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Трябва да бъде предоставена тази стойност.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Тази стойност не трябва да бъде празна.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Тази стойност трябва да бъде от тип булев.',
    ErrorMessage::MUST_BE_INTEGER => 'Тази стойност трябва да бъде цяло число.',
    ErrorMessage::MUST_BE_NUMBER => 'Тази стойност трябва да бъде число.',
    ErrorMessage::MUST_BE_STRING => 'Тази стойност трябва да бъде низ.',
    ErrorMessage::MUST_BE_ARRAY => 'Тази стойност трябва да бъде масив.',
    ErrorMessage::ARRAY_OVERFLOW => 'Тази стойност е ограничена до {{ maximum_elements }} елемента.',
    ErrorMessage::INVALID_CHOICE => 'Тази стойност не е валиден избор; очаквани стойности: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Тази стойност не е валиден времеви отпечатък; очакван формат: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Тази стойност не е валидна часова зона.',
    ErrorMessage::INVALID_UID => 'Тази стойност не е валиден UID.',
];
