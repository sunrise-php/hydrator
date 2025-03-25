<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => '必须提供此值。',
    ErrorMessage::MUST_NOT_BE_EMPTY => '此值不能为空。',
    ErrorMessage::MUST_BE_BOOLEAN => '该值必须为布尔类型。',
    ErrorMessage::MUST_BE_INTEGER => '此值必须是整数类型。',
    ErrorMessage::MUST_BE_NUMBER => '该值必须是 number 类型。',
    ErrorMessage::MUST_BE_STRING => '该值必须是字符串类型。',
    ErrorMessage::MUST_BE_ARRAY => '此值必须是数组类型。',
    ErrorMessage::ARRAY_OVERFLOW => '该值限制为{{ maximum_elements }}个元素。',
    ErrorMessage::INVALID_CHOICE => '此值不是有效选项；预期值：{{ expected_values }}。',
    ErrorMessage::INVALID_TIMESTAMP => '此值不是有效的时间戳，预期格式：{{ expected_format }}。',
    ErrorMessage::INVALID_TIMEZONE => '该值不是有效的时区。',
    ErrorMessage::INVALID_UID => '此值不是有效的UID。',
];
