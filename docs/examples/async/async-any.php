<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;

// Returns 'hello' -- the first successful result
$result = Async\any([
    Async\Awaitable::error(new Exception('failed')),
    Async\Awaitable::complete('hello'),
]);
