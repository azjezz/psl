<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;

Str\slice('Hello, World', 7); // 'World'
Str\slice('Hello, World', 0, 5); // 'Hello'

Str\before('user@example.com', '@'); // 'user'
Str\after('user@example.com', '@'); // 'example.com'

Str\chunk('Hello', 2); // ['He', 'll', 'o']
