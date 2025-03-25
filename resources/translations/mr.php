<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'ही मूल्य दिले जाणे आवश्यक आहे.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'हे मूल्य रिक्त असू नये.',
    ErrorMessage::MUST_BE_BOOLEAN => 'ही किंमत boolean प्रकाराची असावी.',
    ErrorMessage::MUST_BE_INTEGER => 'हे मूल्य पूर्णांक प्रकाराचे असले पाहिजे.',
    ErrorMessage::MUST_BE_NUMBER => 'हे मूल्य क्रमांक प्रकाराचे असणे आवश्यक आहे.',
    ErrorMessage::MUST_BE_STRING => 'ही मान स्ट्रिंग प्रकारातील असणे आवश्यक आहे.',
    ErrorMessage::MUST_BE_ARRAY => 'ही मूल्य array प्रकारातील असणे आवश्यक आहे.',
    ErrorMessage::ARRAY_OVERFLOW => 'ही किंमत {{ maximum_elements }} तत्वांपुरती मर्यादित आहे.',
    ErrorMessage::INVALID_CHOICE => 'हे मूल्य वैध पर्याय नाही; अपेक्षित मूल्ये: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'हे मूल्य वैध timestamp नाही; अपेक्षित स्वरूप: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'हे मूल्य वैध टाइमझोन नाही.',
    ErrorMessage::INVALID_UID => 'हे मूल्य वैध UID नाही.',
];
