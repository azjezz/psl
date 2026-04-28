<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\EitherOrBoth;
use Psl\IO;

// Single-closure apply: runs on whichever side(s) are present.
// On Left/Right the closure is called once; on Both it runs twice (once per side).
$event = new EitherOrBoth\Both('new', 'old');

$event->apply(static fn(string $v): mixed => IO\write_error_line('value: %s', $v));

// Logs:
//   value: new
//   value: old
// and returns the Both unchanged.
