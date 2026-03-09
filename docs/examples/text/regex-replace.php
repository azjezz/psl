<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Regex;
use Psl\Str;

Regex\replace('hello world', '/world/', 'PHP');
// 'hello PHP'

// Dynamic replacement with a callback
Regex\replace_with('hello world', '/\b(\w)/', fn($m) => Str\uppercase($m[1]));
// 'Hello World'

// Replace multiple patterns at once
Regex\replace_every('foo bar baz', [
    '/foo/' => 'one',
    '/bar/' => 'two',
    '/baz/' => 'three',
]);

// 'one two three'
