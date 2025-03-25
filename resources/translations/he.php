<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Este valor debe ser proporcionado.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Denne verdien må ikke være tom.',
    ErrorMessage::MUST_BE_BOOLEAN => '这是值必须为布尔型。',
    ErrorMessage::MUST_BE_INTEGER => 'Այս արժեքը պետք է լինի տիպի integer:',
    ErrorMessage::MUST_BE_NUMBER => 'Yo gabe ser del tipo number.',
    ErrorMessage::MUST_BE_STRING => 'Бұл мән string түрінде болуы керек.',
    ErrorMessage::MUST_BE_ARRAY => 'Esta valor debe ser de tipo array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Эта величина ограничена {{ maximum_elements }} элементами.',
    ErrorMessage::INVALID_CHOICE => 'Dette er ikke en gyldig verdi; forventede verdier: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'This value is not a valid timestamp; expected format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'این مقدار یک منطقه زمانی معتبر نیست.',
    ErrorMessage::INVALID_UID => 'This value is not a valid UID.',
];
