<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

$type = Type\dict::<string, array>(Type\string(), Type\shape::<string, string>([
    'title' => Type\string(),
    'content' => Type\string(),
]));

// If key 123 (int) is used instead of a string key:
try {
    $type->assert([
        123 => ['title' => 'Hello', 'content' => 'World'],
    ]);
} catch (AssertException $e) {
    echo $e->getMessage() . "\n";
}

// If 'title' is an array instead of string:
try {
    $type->coerce([
        'foo' => ['title' => ['nested'], 'content' => 'World'],
    ]);
} catch (CoercionException $e) {
    echo $e->getMessage() . "\n";
}
