<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Questo valore deve essere fornito.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Questo valore non deve essere vuoto.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Questo valore deve essere di tipo booleano.',
    ErrorMessage::MUST_BE_INTEGER => 'Questo valore deve essere di tipo integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Questo valore deve essere di tipo number.',
    ErrorMessage::MUST_BE_STRING => 'Questo valore deve essere di tipo string.',
    ErrorMessage::MUST_BE_ARRAY => 'Questo valore deve essere di tipo array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Questo valore è limitato a {{ maximum_elements }} elementi.',
    ErrorMessage::INVALID_CHOICE => 'Questo valore non è una scelta valida; valori attesi: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Questo valore non è un timestamp valido; formato previsto: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Questo valore non è un fuso orario valido.',
    ErrorMessage::INVALID_UID => 'Questo valore non è un UID valido.',
];
