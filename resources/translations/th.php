<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'ต้องระบุค่านี้',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'ค่านี้ต้องไม่ว่างเปล่า',
    ErrorMessage::MUST_BE_BOOLEAN => 'ค่านี้ต้องเป็นประเภท boolean',
    ErrorMessage::MUST_BE_INTEGER => 'ค่านี้ต้องเป็นประเภท integer',
    ErrorMessage::MUST_BE_NUMBER => 'ค่านี้ต้องเป็นประเภท number',
    ErrorMessage::MUST_BE_STRING => 'ค่านี้ต้องเป็นประเภท string',
    ErrorMessage::MUST_BE_ARRAY => 'ค่านี้ต้องเป็นประเภท array',
    ErrorMessage::ARRAY_OVERFLOW => 'ค่านี้จำกัดไว้ที่ {{ maximum_elements }} องค์ประกอบ',
    ErrorMessage::INVALID_CHOICE => 'ค่านี้ไม่ใช่ตัวเลือกที่ถูกต้อง; ค่าที่คาดไว้: {{ expected_values }}',
    ErrorMessage::INVALID_TIMESTAMP => 'ค่านี้ไม่ใช่ timestamp ที่ถูกต้อง; รูปแบบที่คาดไว้: {{ expected_format }}',
    ErrorMessage::INVALID_TIMEZONE => 'ค่านี้ไม่ใช่เขตเวลา ที่ถูกต้อง',
    ErrorMessage::INVALID_UID => 'ค่านี้ไม่ใช่ UID ที่ถูกต้อง',
];
