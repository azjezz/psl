<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;

Str\trim('  hello  '); // 'hello'
Str\trim_left('  hello  '); // 'hello  '
Str\trim_right('  hello  '); // '  hello'

Str\pad_left('42', 5, '0'); // '00042'
Str\pad_right('hi', 10, '.'); // 'hi........'
