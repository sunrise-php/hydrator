<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Este valor deve ser fornecido.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Este valor não deve estar vazio.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Este valor deve ser do tipo booleano.',
    ErrorMessage::MUST_BE_INTEGER => 'Este valor deve ser do tipo integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Este valor deve ser do tipo number.',
    ErrorMessage::MUST_BE_STRING => 'Este valor deve ser do tipo string.',
    ErrorMessage::MUST_BE_ARRAY => 'Este valor deve ser do tipo array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Este valor é limitado a {{ maximum_elements }} elementos.',
    ErrorMessage::INVALID_CHOICE => 'Este valor não é uma escolha válida; valores esperados: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Este valor não é uma timestamp válida; formato esperado: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Este valor não é um fuso horário válido.',
    ErrorMessage::INVALID_UID => 'Este valor não é um UID válido.',
];
