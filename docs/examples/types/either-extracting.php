<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Either;

$either = new Either\Right::<string>('hello');

// Direct access (throws if wrong side)
$either->getRight(); // 'hello'
// $either->getLeft();        // throws RightException

// Safe access with defaults
$either->getRightOr::<string>('fallback'); // 'hello'
$either->getLeftOr::<string>('fallback'); // 'fallback'

// Lazy default -- only computed if needed
$either->getRightOrElse(static fn(string $left): string => 'computed from: ' . $left);

// Convert to Option
$either->unwrapRight(); // Some('hello')
$either->unwrapLeft(); // None
