<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ContextKey;
use Sunrise\Hydrator\Hydrator;
use Sunrise\Hydrator\HydratorInterface;

use function DI\create;
use function DI\get;

return [
    // See https://dev.sunrise-studio.io/docs/reference/parameters?id=appinput_timestamp_format
    'hydrator.context.timestamp_format' => get('app.input_timestamp_format'),
    // See https://dev.sunrise-studio.io/docs/reference/parameters?id=apptimezone_identifier
    'hydrator.context.timezone_identifier' => get('app.timezone_identifier'),

    'hydrator.context' => [
        ContextKey::TIMESTAMP_FORMAT => get('hydrator.context.timestamp_format'),
        ContextKey::TIMEZONE => get('hydrator.context.timezone_identifier'),
    ],

    'hydrator.type_converters' => [],

    HydratorInterface::class => create(Hydrator::class)
        ->constructor(
            get('hydrator.context'),
            get('hydrator.type_converters'),
        ),
];
