<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Це значення має бути вказано.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Це значення не повинно бути порожнім.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Це значення має бути типу boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Це значення має бути типу integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Це значення має бути типу number.',
    ErrorMessage::MUST_BE_STRING => 'Це значення має бути типу string.',
    ErrorMessage::MUST_BE_ARRAY => 'Це значення має бути типу array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Це значення обмежене {{ maximum_elements }} елементами.',
    ErrorMessage::INVALID_CHOICE => 'Це значення не є дійсним вибором; очікувані значення: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Це значення не є дійсною міткою часу; очікуваний формат: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Це значення не є дійсним часовим поясом.',
    ErrorMessage::INVALID_UID => 'Це значення не є дійсним UID.',
];
