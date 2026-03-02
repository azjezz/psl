<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;

Str\uppercase('hello'); // 'HELLO'
Str\lowercase('HELLO'); // 'hello'
Str\capitalize('hello world'); // 'Hello world'
Str\capitalize_words('hello world'); // 'Hello World'

Str\replace('hello world', 'world', 'PHP'); // 'hello PHP'
Str\replace_every('aabbcc', ['aa' => '1', 'cc' => '3']); // '1bb3'

Str\reverse('Hello'); // 'olleH'
Str\repeat('ab', 3); // 'ababab'
