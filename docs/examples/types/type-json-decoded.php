<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

$shape = Type\shape::<string, string|array>([
    'name' => Type\string(),
    'metadata' => Type\json_decoded::<array>(Type\shape::<string, string|bool>([
        'role' => Type\string(),
        'active' => Type\bool(),
    ])),
]);

// Works with JSON strings (e.g. from a database column)
$fromDb = $shape->coerce([
    'name' => 'Alice',
    'metadata' => '{"role": "admin", "active": true}',
]);

// Also works with already-decoded arrays
$fromArray = $shape->coerce([
    'name' => 'Alice',
    'metadata' => ['role' => 'admin', 'active' => true],
]);
