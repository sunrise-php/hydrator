<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'この値は必須です。',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'この値は空であってはなりません。',
    ErrorMessage::MUST_BE_BOOLEAN => 'この値は boolean 型でなければなりません。',
    ErrorMessage::MUST_BE_INTEGER => 'この値は integer 型でなければなりません。',
    ErrorMessage::MUST_BE_NUMBER => 'この値は number 型でなければなりません。',
    ErrorMessage::MUST_BE_STRING => 'この値は string 型でなければなりません。',
    ErrorMessage::MUST_BE_ARRAY => 'この値は array 型でなければなりません。',
    ErrorMessage::ARRAY_OVERFLOW => 'この値は {{ maximum_elements }} 個以内でなければなりません。',
    ErrorMessage::INVALID_CHOICE => 'この値は有効な選択肢ではありません。期待される値: {{ expected_values }}。',
    ErrorMessage::INVALID_TIMESTAMP => 'この値は有効なタイムスタンプではありません。期待される形式: {{ expected_format }}。',
    ErrorMessage::INVALID_TIMEZONE => 'この値は有効なタイムゾーンではありません。',
    ErrorMessage::INVALID_UID => 'この値は有効な UID ではありません。',
];
