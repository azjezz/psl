<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Option;

$opt = Option\some(5);

// map: transform the inner value
$opt->map(fn(int $v) => $v * 2); // Some(10)
Option\none()->map(fn(int $v) => $v * 2); // None

// andThen: chain operations that return Options (flatMap)
Option\some('hello')
    ->andThen(fn(string $v) => Option\from_nullable($v !== '' ? $v : null))
    ->andThen(fn(string $v) => Option\some(strlen($v)));
// Some(5) if non-empty string, None otherwise

// mapOr / mapOrElse: transform with a default
Option\none()->mapOr(fn(int $v) => $v * 2, 0); // Some(0)
