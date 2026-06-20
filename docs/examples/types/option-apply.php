<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Option;

$result = Option\some::<string>('admin')
    ->apply(fn(string $role) => print "Found role: {$role}\n")
    ->map::<string>(fn(string $role) => strtoupper($role));
