<?php

declare(strict_types=1);

namespace Psl\Asio;

use Psl;
use Psl\Iter;
use Throwable;

/**
 * Returns an awaitable that finishes when the first wait handle finishes,
 * and fails only if all wait handles fail.
 *
 * @template T
 *
 * @param list<Awaitable<T>> $awaitables
 *
 * @throws Psl\Exception\InvariantViolationException If $awaitables is empty.
 *
 * @return Awaitable<T>
 */
function first(array $awaitables): Awaitable
{
    Psl\invariant(!Iter\is_empty($awaitables), '$awaitables is empty.');

    /** @var Internal\Deferred<T> $deferred */
    $deferred = new Internal\Deferred();
    $result = $deferred->awaitable();
    $pending = Iter\count($awaitables);
    $last_exception = null;

    foreach ($awaitables as $awaitable) {
        $awaitable->onJoin(static function ($error, $value) use (&$deferred, &$last_exception, &$pending) {
            /**
             * @var Internal\Deferred<T> $deferred
             * @var int $pending
             */
            if ($pending === 0) {
                return;
            }

            if (!$error) {
                $pending = 0;
                $deferred->finish($value);
                $deferred = null;
                return;
            }

            /** @var Throwable $last_exception */
            $last_exception = $error;
            if (0 === --$pending) {
                $deferred->fail($last_exception);
            }
        });
    }

    return $result;
}
