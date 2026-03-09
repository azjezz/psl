<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Str;

/** @var Async\Awaitable<string> $promise */
$promise = Async\run(static fn() => 'hello');

$upper = $promise->map(fn(string $body) => Str\uppercase($body));
// If $promise resolves with 'hello', $upper resolves with 'HELLO'
// If $promise is rejected, $upper is also rejected with the same exception

$upper->await(); // 'HELLO'
