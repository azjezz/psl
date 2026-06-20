<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;

// Apply: execute a function on each element (returns void)
Iter\apply::<string>(['alice', 'bob'], fn(string $name) => print "Hello, {$name}!\n");

// Prints:
//   Hello, alice!
//   Hello, bob!
