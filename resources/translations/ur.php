<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'یہ قدر فراہم کی جانی چاہیے۔',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'یہ ویلیو خالی نہیں ہونی چاہیے۔',
    ErrorMessage::MUST_BE_BOOLEAN => 'یہ قدر boolean قسم کی ہونی چاہیے۔',
    ErrorMessage::MUST_BE_INTEGER => 'یہ قدر عدد صحیح کی قسم کی ہونی چاہیے۔',
    ErrorMessage::MUST_BE_NUMBER => 'یہ قدر نمبر کے قسم کی ہونی چاہیے۔',
    ErrorMessage::MUST_BE_STRING => 'یہ قدر قسم string کی ہونی چاہیے۔',
    ErrorMessage::MUST_BE_ARRAY => 'یہ قدر قسم array کی ہونی چاہیے۔',
    ErrorMessage::ARRAY_OVERFLOW => 'یہ قدر {{ maximum_elements }} عناصر تک محدود ہے۔',
    ErrorMessage::INVALID_CHOICE => 'یہ قدر ایک درست انتخاب نہیں ہے؛ متوقع اقدار: {{ expected_values }}۔',
    ErrorMessage::INVALID_TIMESTAMP => 'یہ قدر ایک درست ٹائم اسٹیمپ نہیں ہے؛ متوقع فارمیٹ: {{ expected_format }}۔',
    ErrorMessage::INVALID_TIMEZONE => 'یہ قدر ایک درست ٹائم زون نہیں ہے۔',
    ErrorMessage::INVALID_UID => 'یہ قدر ایک درست UID نہیں ہے۔',
];
