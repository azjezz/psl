<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;

Str\contains('Hello, World', 'World'); // true
Str\contains('Hello, World', 'world'); // false (case-sensitive)
Str\contains_ci('Hello, World', 'world'); // true  (case-insensitive)

Str\starts_with('Hello, World', 'Hello'); // true
Str\ends_with('Hello, World', 'World'); // true

// Find position of a substring (returns null if not found)
Str\search('hello', 'l'); // 2
Str\search_last('hello', 'l'); // 3
