<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ErrorMessage;

return [
    ErrorMessage::MUST_BE_PROVIDED => 'ఈ విలువ అందించాలి.',
    ErrorMessage::MUST_NOT_BE_EMPTY => 'ఈ విలువ ఖాళీగా ఉండకూడదు.',
    ErrorMessage::MUST_BE_BOOLEAN => 'ఈ విలువ boolean రకం అయ్యి ఉండాలి.',
    ErrorMessage::MUST_BE_INTEGER => 'ఈ విలువ integer రకం లో ఉండాలి.',
    ErrorMessage::MUST_BE_NUMBER => 'సంఖ్య రకం యొక్క విలువ ఉండాలి.',
    ErrorMessage::MUST_BE_STRING => 'ఈ విలువ string రకం కలిగి ఉండాలి.',
    ErrorMessage::MUST_BE_ARRAY => 'ఈ విలువ తప్పనిసరిగా array రకంగా ఉండాలి.',
    ErrorMessage::ARRAY_OVERFLOW => 'ఈ విలువ {{ maximum_elements }} మూలకాలకు పరిమితం చేయబడింది.',
    ErrorMessage::INVALID_CHOICE => 'ఈ వెల్‍यూ చెల్లుబాటు అయ్యే ఎంపిక కాదు;иниచినించు వెల్‍यూతలు: {{ expected_values }}.',
    ErrorMessage::INVALID_TIMESTAMP => 'ఈ విలువ చెల్లుబాటు అయ్యే టైమ్స్టాంప్ కాదు; అంచనా విధానం: {{ expected_format }}.',
    ErrorMessage::INVALID_TIMEZONE => 'ఈ విలువ చెల్లుబాటు అయ్యే కాలమండలం కాదు.',
    ErrorMessage::INVALID_UID => 'ఈ విలువ చెల్లు UID కాదు.',
];
