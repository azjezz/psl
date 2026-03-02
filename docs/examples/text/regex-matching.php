<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Regex;

Regex\matches('hello@example.com', '/^[^@]+@[^@]+$/'); // true
Regex\matches('not-an-email', '/^[^@]+@[^@]+$/'); // false
Regex\matches('foo bar foo', '/foo/', 4); // true (from offset)
