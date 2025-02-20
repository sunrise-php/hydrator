<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Ezt az értéket meg kell adni.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Ez az érték nem lehet üres.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Ennek az értéknek boolean típusúnak kell lennie.',
    ErrorMessage::MUST_BE_INTEGER => 'Ennek az értéknek integer típusúnak kell lennie.',
    ErrorMessage::MUST_BE_NUMBER => 'Ennek az értéknek number típusúnak kell lennie.',
    ErrorMessage::MUST_BE_STRING => 'Ennek az értéknek string típusúnak kell lennie.',
    ErrorMessage::MUST_BE_ARRAY => 'Ennek az értéknek array típusúnak kell lennie.',
    ErrorMessage::ARRAY_OVERFLOW => 'Ez az érték legfeljebb {{ maximum_elements }} elemet tartalmazhat.',
    ErrorMessage::INVALID_CHOICE => 'Ez az érték nem érvényes választás; elvárt értékek: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Ez az érték nem érvényes időbélyeg; elvárt formátum: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Ez az érték nem érvényes időzóna.',
    ErrorMessage::INVALID_UID => 'Ez az érték nem érvényes UID.',
];
