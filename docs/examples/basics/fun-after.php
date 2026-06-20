<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Fun;
use Psl\Str;

$strlen = Fun\after::<string, string, int>(static fn(string $s): string => Str\trim($s), static fn(string $s): int => Str\length($s));

$strlen('  hi  '); // 2
