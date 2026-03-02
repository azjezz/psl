<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Fun;
use Psl\IO;
use Psl\Str;

$process = Fun\pipe(
    Fun\tap(static fn(string $v) => IO\write_error_line('input: %s', $v)),
    static fn(string $s): string => Str\uppercase($s),
    Fun\tap(static fn(string $v) => IO\write_error_line('output: %s', $v)),
);

$process('hello'); // 'HELLO' (logs are emitted as a side effect)
