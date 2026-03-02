<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;

// All the same functions, operating on raw bytes
Str\Byte\length('Hello'); // 5
Str\Byte\contains('Hello', 'ell'); // true
Str\Byte\uppercase('hello'); // 'HELLO'
Str\Byte\slice('Hello', 1, 3); // 'ell'
Str\Byte\reverse('Hello'); // 'olleH'
Str\Byte\rot13('Hello'); // 'Uryyb'
