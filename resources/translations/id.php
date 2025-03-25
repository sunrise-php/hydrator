<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Nilai ini harus disediakan.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Nilai ini tidak boleh kosong.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Nilai ini harus bertipe boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Nilai ini harus bertipe integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Nilai ini harus bertipe number.',
    ErrorMessage::MUST_BE_STRING => 'Nilai ini harus bertipe string.',
    ErrorMessage::MUST_BE_ARRAY => 'Nilai ini harus bertipe array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Nilai ini dibatasi hingga {{ maximum_elements }} elemen.',
    ErrorMessage::INVALID_CHOICE => 'Nilai ini bukan pilihan yang valid; nilai yang diharapkan: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Nilai ini bukanlah timestamp yang valid; format yang diharapkan: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Nilai ini bukan zona waktu yang valid.',
    ErrorMessage::INVALID_UID => 'Nilai ini bukan UID yang valid.',
];
