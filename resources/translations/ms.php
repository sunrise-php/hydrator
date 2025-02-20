<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Nilai ini mesti disediakan.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Nilai ini tidak boleh kosong.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Nilai ini mesti berjenis boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Nilai ini mesti berjenis integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Nilai ini mesti berjenis number.',
    ErrorMessage::MUST_BE_STRING => 'Nilai ini mesti berjenis string.',
    ErrorMessage::MUST_BE_ARRAY => 'Nilai ini mesti berjenis array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Nilai ini terhad kepada {{ maximum_elements }} elemen.',
    ErrorMessage::INVALID_CHOICE => 'Nilai ini bukan pilihan yang sah; nilai yang dijangka: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Nilai ini bukan cap waktu yang sah; format yang dijangka: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Nilai ini bukan zon masa yang sah.',
    ErrorMessage::INVALID_UID => 'Nilai ini bukan UID yang sah.',
];
