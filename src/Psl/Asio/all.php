<?php

declare(strict_types=1);

namespace Psl\Asio;

use Psl\Iter;

use function Psl\Type\object;

/**
 * @template T
 *
 * @param iterable<array-key, Awaitable<T>> $awaitables
 *
 * @return Awaitable<list<T>>
 */
function all(iterable $awaitables): Awaitable
{
    if (empty($awaitables)) {
        return new Internal\FinishedAwaitable([]);
    }

    /**
     * @var Internal\Deferred<list<T>> $deferred
     */
    $deferred = new Internal\Deferred();
    $result = $deferred->awaitable();

    $pending = Iter\count($awaitables);
    /** @var list<T> $values */
    $values = [];

    /**
     * @var Awaitable<T> $awaitable
     */
    foreach ($awaitables as $key => $awaitable) {
        object(Awaitable::class)->assert($awaitable);

        /** @psalm-suppress MixedArrayAssignment */
        $values[$key] = null; // add entry to array to preserve order
        $awaitable->onJoin(static function ($exception, $value) use (&$deferred, &$values, &$pending, $key) {
            /**
             * @var int $pending
             * @var Internal\Deferred<list<T>> $deferred
             */
            if ($pending === 0) {
                return;
            }

            if ($exception) {
                $pending = 0;
                $deferred->fail($exception);
                $deferred = null;
                return;
            }

            /**
             *  @psalm-suppress MixedArrayAssignment
             *  @psalm-suppress MixedAssignment
             */
            $values[$key] = $value;
            /** @var int $pending */
            --$pending;
            if (0 === $pending) {
                /** @var list<T> $values */
                $deferred->finish($values);
            }
        });
    }

    return $result;
}
