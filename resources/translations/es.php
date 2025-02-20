<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Este valor debe ser proporcionado.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Este valor no debe estar vacío.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Este valor debe ser de tipo boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Este valor debe ser de tipo integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Este valor debe ser de tipo number.',
    ErrorMessage::MUST_BE_STRING => 'Este valor debe ser de tipo string.',
    ErrorMessage::MUST_BE_ARRAY => 'Este valor debe ser de tipo array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Este valor está limitado a {{ maximum_elements }} elementos.',
    ErrorMessage::INVALID_CHOICE => 'Este valor no es una opción válida; valores esperados: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Este valor no es una marca de tiempo válida; formato esperado: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Este valor no es una zona horaria válida.',
    ErrorMessage::INVALID_UID => 'Este valor no es un UID válido.',
];
