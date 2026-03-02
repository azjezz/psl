<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Dict;
use Psl\Str;

// Associate: zip keys and values into a dict
Dict\associate(['x', 'y', 'z'], [10, 20, 30]);
// ['x' => 10, 'y' => 20, 'z' => 30]

// From entries: build from [key, value] tuples
Dict\from_entries([['name', 'Alice'], ['role', 'admin']]);
// ['name' => 'Alice', 'role' => 'admin']

// From keys: generate values from keys using a function
Dict\from_keys(
    ['sm', 'md', 'lg'],
    /**
     * @param 'sm'|'md'|'lg' $size
     *
     * @return 576|768|992
     */
    fn(string $size) => match ($size) {
        'sm' => 576,
        'md' => 768,
        'lg' => 992,
    },
);
// ['sm' => 576, 'md' => 768, 'lg' => 992]

// Reindex: re-key an iterable using a function applied to each value
$users = [['id' => 42, 'name' => 'Alice'], ['id' => 7, 'name' => 'Bob']];
Dict\reindex($users, fn($user) => $user['id']);
// [42 => ['id' => 42, 'name' => 'Alice'], 7 => ['id' => 7, 'name' => 'Bob']]

// Pull: build a dict by deriving both new keys and new values
Dict\pull(
    ['Alice', 'Bob'],
    fn(string $name) => Str\length($name), // value function
    fn(string $name) => Str\lowercase($name), // key function
);

// ['alice' => 5, 'bob' => 3]
