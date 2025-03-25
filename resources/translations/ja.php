<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'この値は提供される必要があります。',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'この値は空にしてはいけません。',
    ErrorMessage::MUST_BE_BOOLEAN => 'この値は boolean 型でなければなりません。',
    ErrorMessage::MUST_BE_INTEGER => 'この値は整数型でなければなりません。',
    ErrorMessage::MUST_BE_NUMBER => 'この値は、数値型でなければなりません。',
    ErrorMessage::MUST_BE_STRING => 'この値は文字列型でなければなりません。',
    ErrorMessage::MUST_BE_ARRAY => 'この値は配列型でなければなりません。',
    ErrorMessage::ARRAY_OVERFLOW => 'この値は{{ maximum_elements }}個の要素に制限されています。',
    ErrorMessage::INVALID_CHOICE => 'この値は有効な選択ではありません。予想される値: {{ expected_values }}。',
    ErrorMessage::INVALID_TIMESTAMP => 'この値は有効なタイムスタンプではありません。期待される形式: {{ expected_format }}。',
    ErrorMessage::INVALID_TIMEZONE => 'この値は有効なタイムゾーンではありません。',
    ErrorMessage::INVALID_UID => 'この値は有効なUIDではありません。',
];
