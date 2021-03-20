<?php

declare(strict_types=1);

namespace Psl\Asio;

use Psl;
use Psl\Iter;
use Psl\Vec;
use Throwable;

use function count;

/**
 * The returned wait handle will only fail if the given number of required awaitables fail.
 *
 * @template T
 *
 * @param iterable<array-key, Awaitable<T>> $awaitables A list of wait handles.
 * @param int $required Number of wait handles that must succeed for the returned awaitable to succeed.
 *
 * @throws Psl\Exception\InvariantViolationException
 *
 * @return Awaitable<list<T>>
 */
function some(iterable $awaitables, int $required = 1): Awaitable
{
    Psl\invariant($required >= 0, '$required must be a non-negative.');

    $pending = Iter\count($awaitables);

    Psl\invariant($required <= $pending, '$required is too large.');
    if (Iter\is_empty($awaitables)) {
        return new Internal\FinishedAwaitable([]);
    }

    /**  @var Internal\Deferred<list<T>> $deferred */
    $deferred = new Internal\Deferred();
    $result = $deferred->awaitable();
    $values = Vec\fill($pending, null);
    $last_exception = null;

    foreach (Vec\values($awaitables) as $key => $awaitable) {
        $awaitable->onJoin(
            static function (
                ?Throwable $exception,
                $value
            ) use (
                &$values,
                &$last_exception,
                &$pending,
                $key,
                $required,
                $deferred
            ) {
                /**
                 * @var ?T $value
                 * @var int $pending
                 * @var array<int, T> $values
                 */
                if ($exception) {
                    $last_exception = $exception;
                    unset($values[$key]);
                } else {
                    $values[$key] = $value;
                }

                if (0 === --$pending) {
                    if (count($values) < $required) {
                        /** @var Throwable $last_exception */
                        $deferred->fail($last_exception);
                    } else {
                        $deferred->finish(Vec\values($values));
                    }
                }
            }
        );
    }

    return $result;
}
