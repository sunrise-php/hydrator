<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Detta värde måste tillhandahållas.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Detta värde får inte vara tomt.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Detta värde måste vara av typen boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Detta värde måste vara av typen integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Detta värde måste vara av typen number.',
    ErrorMessage::MUST_BE_STRING => 'Detta värde måste vara av typen string.',
    ErrorMessage::MUST_BE_ARRAY => 'Detta värde måste vara av typen array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Detta värde är begränsat till {{ maximum_elements }} element.',
    ErrorMessage::INVALID_CHOICE => 'Detta värde är inte ett giltigt alternativ; förväntade värden: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Det här värdet är inte en giltig tidsstämpel; förväntat format: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Detta värde är inte en giltig tidszon.',
    ErrorMessage::INVALID_UID => 'Detta värde är inte ett giltigt UID.',
];
