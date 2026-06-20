<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;

// Returns 'hello' -- the first successful result
$result = Async\any::<string>([
    Async\Awaitable::error(new Exception('failed')),
    Async\Awaitable::complete::<string>('hello'),
]);
