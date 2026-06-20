<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Fun;
use Psl\Str;

$transform = Fun\pipe::<string>(
    static fn(string $s): string => Str\trim($s),
    static fn(string $s): string => Str\lowercase($s),
    static fn(string $s): string => Str\replace($s, ' ', '-'),
);

$transform('  Hello World  '); // 'hello-world'
