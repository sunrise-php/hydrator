<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'يجب تقديم هذه القيمة.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'يجب ألا تكون هذه القيمة فارغة.',
    ErrorMessage::MUST_BE_BOOLEAN => 'يجب أن تكون هذه القيمة من النوع boolean.',
    ErrorMessage::MUST_BE_INTEGER => 'هذا القيمة يجب أن تكون من النوع integer.',
    ErrorMessage::MUST_BE_NUMBER => 'يجب أن تكون هذه القيمة من نوع number.',
    ErrorMessage::MUST_BE_STRING => 'يجب أن تكون هذه القيمة من نوع string.',
    ErrorMessage::MUST_BE_ARRAY => 'يجب أن تكون هذه القيمة من نوع array.',
    ErrorMessage::ARRAY_OVERFLOW => 'هذا القيمة مُقتصرة على {{ maximum_elements }} من العناصر.',
    ErrorMessage::INVALID_CHOICE => 'هذه القيمة ليست اختيارًا صالحًا؛ القيم المتوقعة: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'هذه القيمة ليست طابع زمني صالح; التنسيق المتوقع: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'هذه القيمة ليست منطقة زمنية صالحة.',
    ErrorMessage::INVALID_UID => 'هذه القيمة ليست UID صالحًا.',
];
