<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Option;

$opt = Option\some::<int>(5);

// map: transform the inner value
$opt->map::<int>(fn(int $v) => $v * 2); // Some(10)
Option\none()->map::<int>(fn(int $v) => $v * 2); // None

// andThen: chain operations that return Options (flatMap)
Option\some::<string>('hello')
    ->andThen::<string>(fn(string $v) => Option\from_nullable::<string>($v !== '' ? $v : null))
    ->andThen::<int>(fn(string $v) => Option\some::<int>(strlen($v)));
// Some(5) if non-empty string, None otherwise

// mapOr / mapOrElse: transform with a default
Option\none()->mapOr::<int>(fn(int $v) => $v * 2, 0); // Some(0)
