<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\EitherOrBoth;

/** @var EitherOrBoth\EitherOrBoth<string, string> $event */
$event = new EitherOrBoth\Both::<string, string>('new-value', 'old-value');

$description = $event->proceed::<string>(
    left: static fn(string $new): string => "insert: {$new}",
    right: static fn(string $old): string => "delete: {$old}",
    both: static fn(string $new, string $old): string => "update: {$old} -> {$new}",
);

// 'update: old-value -> new-value'
