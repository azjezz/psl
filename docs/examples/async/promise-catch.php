<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;

/** @var Async\Awaitable<string> $promise */
$promise = Async\run(static fn() => throw new Exception('oh no'));

$safe = $promise->catch(fn(\Throwable $e) => 'default response');
// If $promise is rejected, $safe resolves with 'default response'
// If $promise succeeds, $safe resolves with the original value

$safe->await(); // 'default response'
