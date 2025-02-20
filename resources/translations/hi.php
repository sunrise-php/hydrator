<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'यह मान प्रदान किया जाना चाहिए।',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'यह मान खाली नहीं होना चाहिए।',
    ErrorMessage::MUST_BE_BOOLEAN => 'यह मान boolean प्रकार का होना चाहिए।',
    ErrorMessage::MUST_BE_INTEGER => 'यह मान integer प्रकार का होना चाहिए।',
    ErrorMessage::MUST_BE_NUMBER => 'यह मान number प्रकार का होना चाहिए।',
    ErrorMessage::MUST_BE_STRING => 'यह मान string प्रकार का होना चाहिए।',
    ErrorMessage::MUST_BE_ARRAY => 'यह मान array प्रकार का होना चाहिए।',
    ErrorMessage::ARRAY_OVERFLOW => 'यह मान {{ maximum_elements }} तत्वों तक सीमित है।',
    ErrorMessage::INVALID_CHOICE => 'यह मान मान्य विकल्प नहीं है; अपेक्षित मान: {{ expected_values }}।',
    ErrorMessage::INVALID_TIMESTAMP => 'यह मान मान्य समय-मुहर नहीं है; अपेक्षित प्रारूप: {{ expected_format }}।',
    ErrorMessage::INVALID_TIMEZONE => 'यह मान मान्य समय-क्षेत्र नहीं है।',
    ErrorMessage::INVALID_UID => 'यह मान मान्य UID नहीं है।',
];
