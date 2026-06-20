<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Json;
use Psl\Type;

// Decode and validate as a specific shape
$user = Json\typed::<array>('{"name":"Alice","age":30}', Type\shape::<string, string|int>([
    'name' => Type\string(),
    'age' => Type\int(),
]));
// $user['name'] is string, $user['age'] is int -- guaranteed

// If the JSON structure doesn't match, you get a clear exception
try {
    Json\typed::<array>('{"name":"Alice"}', Type\shape::<string, string|int>([
        'name' => Type\string(),
        'age' => Type\int(),
    ]));
} catch (Json\Exception\DecodeException $e) {
    echo $e->getMessage() . "\n";
}

// Works with any Type -- vectors, optionals, nested shapes
$ids = Json\typed::<array>('[1, 2, 3]', Type\vec::<int>(Type\int()));

// [1, 2, 3] as list<int>
