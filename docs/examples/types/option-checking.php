<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Option;

$opt = Option\some::<int>(42);
$opt->isSome(); // true
$opt->isNone(); // false
$opt->isSomeAnd(fn(int $v) => $v > 10); // true
$opt->contains(42); // true

// Unwrap the value
$opt->unwrap(); // 42 (throws NoneException if None)
$opt->unwrapOr::<int>(0); // 42 (returns default if None)
$opt->unwrapOrElse::<int>(fn() => 0); // 42 (lazy default if None)
