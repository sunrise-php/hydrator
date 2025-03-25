<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'এই মানটি অবশ্যই প্রদান করতে হবে।',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'এই মানটি খালি হওয়া উচিত নয়।',
    ErrorMessage::MUST_BE_BOOLEAN => 'এই মানটি অবশ্যই boolean ধরনের হতে হবে।',
    ErrorMessage::MUST_BE_INTEGER => 'এই মানটি অবশ্যই integer ধরনের হতে হবে।',
    ErrorMessage::MUST_BE_NUMBER => 'এই মানটি অবশ্যই number ধরনের হতে হবে।',
    ErrorMessage::MUST_BE_STRING => 'এই মানটি স্ট্রিং টাইপের হতে হবে।',
    ErrorMessage::MUST_BE_ARRAY => 'এই মানটি অবশ্যই array ধরনের হতে হবে।',
    ErrorMessage::ARRAY_OVERFLOW => 'এই মানটি {{ maximum_elements }} উপাদানে সীমাবদ্ধ।',
    ErrorMessage::INVALID_CHOICE => 'এই মানটি একটি বৈধ পছন্দ নয়; প্রত্যাশিত মান: {{ expected_values }}।',
    ErrorMessage::INVALID_TIMESTAMP => 'এই মানটি একটি সঠিক টাইমস্ট্যাম্প নয়; প্রত্যাশিত ফর্ম্যাট: {{ expected_format }}।',
    ErrorMessage::INVALID_TIMEZONE => 'এই মানটি একটি বৈধ সময় অঞ্চল নয়।',
    ErrorMessage::INVALID_UID => 'এই মানটি একটি বৈধ UID নয়।',
];
