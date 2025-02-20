<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'يجب توفير هذه القيمة.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'يجب ألا تكون هذه القيمة فارغة.',
    ErrorMessage::MUST_BE_BOOLEAN => 'يجب أن تكون هذه القيمة من نوع منطقي.',
    ErrorMessage::MUST_BE_INTEGER => 'يجب أن تكون هذه القيمة من نوع عدد صحيح.',
    ErrorMessage::MUST_BE_NUMBER => 'يجب أن تكون هذه القيمة من نوع رقم.',
    ErrorMessage::MUST_BE_STRING => 'يجب أن تكون هذه القيمة من نوع نص.',
    ErrorMessage::MUST_BE_ARRAY => 'يجب أن تكون هذه القيمة من نوع مصفوفة.',
    ErrorMessage::ARRAY_OVERFLOW => 'هذه القيمة محدودة بـ {{ maximum_elements }} عنصر.',
    ErrorMessage::INVALID_CHOICE => 'هذه القيمة ليست خيارًا صالحًا؛ القيم المتوقعة: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'هذه القيمة ليست طابعًا زمنيًا صالحًا؛ التنسيق المتوقع: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'هذه القيمة ليست منطقة زمنية صالحة.',
    ErrorMessage::INVALID_UID => 'هذه القيمة ليست معرفًا صالحًا.',
];
