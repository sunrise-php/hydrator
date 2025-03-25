<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'Giá trị này phải được cung cấp.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'Giá trị này không được để trống.',
    ErrorMessage::MUST_BE_BOOLEAN => 'Giá trị này phải thuộc kiểu boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'Giá trị này phải thuộc kiểu integer.',
    ErrorMessage::MUST_BE_NUMBER => 'Giá trị này phải thuộc loại number.',
    ErrorMessage::MUST_BE_STRING => 'Giá trị này phải thuộc kiểu chuỗi.',
    ErrorMessage::MUST_BE_ARRAY => 'Giá trị này phải có kiểu array.',
    ErrorMessage::ARRAY_OVERFLOW => 'Giá trị này bị giới hạn ở {{ maximum_elements }} phần tử.',
    ErrorMessage::INVALID_CHOICE => 'Giá trị này không phải là lựa chọn hợp lệ; các giá trị mong đợi: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'Giá trị này không phải là timestamp hợp lệ; định dạng mong đợi: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'Giá trị này không phải là múi giờ hợp lệ.',
    ErrorMessage::INVALID_UID => 'Giá trị này không phải là UID hợp lệ.',
];
