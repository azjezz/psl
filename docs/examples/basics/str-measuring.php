<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;

Str\length('Hello'); // 5
Str\length('مرحبا'); // 5 (Arabic characters, 5 codepoints)
Str\is_empty(''); // true
Str\width('你好'); // 4 (CJK characters count as 2)

Str\format('Hello, %s! You have %d messages.', 'Alice', 5);
// 'Hello, Alice! You have 5 messages.'

Str\concat('Hello', ', ', 'World'); // 'Hello, World'
Str\truncate('Hello, World', 0, 8, '...'); // 'Hello...'

Str\strip_prefix('HelloWorld', 'Hello'); // 'World'
Str\strip_suffix('HelloWorld', 'World'); // 'Hello'
