<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Nilai iki kudu diwenehake.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Nilai iki ora kena kosong.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Nilai iki kudu jinis boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Nilai iki kudu nganggo jinis integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Nilai iki kudu saka jinis number.',
    ErrorMessage::MUST_BE_STRING => 'Nilai iki kudu tipe string.',
    ErrorMessage::MUST_BE_ARRAY => 'Nilai iki kudu jinis array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Nilai iki diwatesi nganti {{ maximum_elements }} unsur.',
    ErrorMessage::INVALID_CHOICE => 'Nilai iki ora pilihan sing sah; nilai sing diarepake: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Nilai iki ora minangka timestamp sing sah; format sing diarepake: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Nilai iki dudu zona wektu sing sah.',
    ErrorMessage::INVALID_UID => 'Nilai iki ora UID sing sah.',
];
