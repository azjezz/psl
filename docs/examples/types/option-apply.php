<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Option;

$result = Option\some('admin')
    ->apply(fn(string $role) => print "Found role: {$role}\n")
    ->map(fn(string $role) => strtoupper($role));
