<?php

declare(strict_types=1);

use Sunrise\Hydrator\Dictionary\ContextKey;
use Sunrise\Hydrator\Hydrator;
use Sunrise\Hydrator\HydratorInterface;

use function DI\create;
use function DI\get;

return [
    'hydrator.context.timestamp_format' => get('app.input_timestamp_format'),
    'hydrator.context.timezone_identifier' => get('app.timezone_identifier'),

    'hydrator.context' => [
        ContextKey::TIMESTAMP_FORMAT => get('hydrator.context.timestamp_format'),
        ContextKey::TIMEZONE => get('hydrator.context.timezone_identifier'),
    ],

    'hydrator.type_converters' => [],

    'hydrator.is_doc_block_reader_enabled' => false,

    HydratorInterface::class => create(Hydrator::class)
        ->constructor(
            get('hydrator.context'),
            get('hydrator.type_converters'),
            get('hydrator.is_doc_block_reader_enabled'),
        ),
];
