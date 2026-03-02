<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Either;
use Psl\IO;
use Psl\Str;

$either = new Either\Right('some data')
    ->apply(static fn(string $v): mixed => IO\write_error_line('Processing value: %s', $v))
    ->mapRight(static fn(string $v): string => Str\uppercase($v));

// Right('SOME DATA')
