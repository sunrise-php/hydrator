<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Это значение должно быть предоставлено.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Это значение не должно быть пустым.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Это значение должно быть типа boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Это значение должно быть типа integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Это значение должно быть типа number.',
    ErrorMessage::MUST_BE_STRING => 'Это значение должно быть типа string.',
    ErrorMessage::MUST_BE_ARRAY => 'Это значение должно быть типа array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Это значение ограничено {{ maximum_elements }} элементами.',
    ErrorMessage::INVALID_CHOICE => 'Это значение не является допустимым выбором; ожидаемые значения: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Это значение не является корректной временной меткой; ожидаемый формат: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Это значение не является допустимым часовым поясом.',
    ErrorMessage::INVALID_UID => 'Это значение не является допустимым UID.',
];
