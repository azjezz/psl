<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Json;

Json\encode(['name' => 'Alice', 'score' => 9.0]);
// '{"name":"Alice","score":9.0}'

// Pretty-print for readability
Json\encode(['name' => 'Alice'], true);
// '{
//     "name": "Alice"
// }'

// Unicode and slashes are unescaped by default
Json\encode(['path' => '/home/user', 'greeting' => "\u{1F44B}"]);

// '{"path":"/home/user","greeting":"..."}' (actual wave emoji in output)
