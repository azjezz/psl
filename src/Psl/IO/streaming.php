<?php

declare(strict_types=1);

namespace Psl\IO;

use Generator;
use Psl\Async;
use Psl\Channel;
use Psl\Result;
use Psl\Str;
use Psl;

/**
 * Streaming the output of the given read stream handles using a generator.
 *
 * Example:
 *
 *  $handles = [
 *    'foo' => get_read_stream('foo'),
 *    'bar' => get_read_stream('bar'),
 *  ];
 *
 *  foreach(IO\streaming($handles) as $type => $chunk) {
 *    IO\write_line('received chunk "%s" from "%s" stream', $chunk, $type);
 *  }
 *
 * @template T of array-key
 *
 * @param iterable<T, ReadStreamHandleInterface> $handles
 *
 * @throws Exception\AlreadyClosedException If one of the handles has been already closed.
 * @throws Exception\RuntimeException If an error occurred during the operation.
 * @throws Exception\TimeoutException If $timeout is reached before being able to read all the handles until the end.
 *
 * @return Generator<T, string, mixed, void>
 */
function streaming(iterable $handles, ?float $timeout = null): Generator
{
    /**
     * @psalm-suppress UnnecessaryVarAnnotation
     * @var Channel\ReceiverInterface<array{T|null, Result\ResultInterface<string>}> $receiver
     * @psalm-suppress UnnecessaryVarAnnotation
     * @var Channel\SenderInterface<array{T|null, Result\ResultInterface<string>}> $sender
     */
    [$receiver, $sender] = Channel\unbounded();

    /** @var Psl\Ref<array<T, string>> $watchers */
    $watchers = new Psl\Ref([]);
    foreach ($handles as $k => $handle) {
        $stream = $handle->getStream();
        if ($stream === null) {
            throw new Exception\AlreadyClosedException(Str\format('Handle "%s" is already closed.', (string) $k));
        }

        $watchers->value[$k] = Async\Scheduler::onReadable($stream, static function (string $watcher) use ($k, $handle, $sender, $watchers): void {
            $result = Result\wrap($handle->tryRead(...));
            if ($result->isFailed() || ($result->isSucceeded() && $result->getResult() === '')) {
                Async\Scheduler::cancel($watcher);
                unset($watchers->value[$k]);
                if ($watchers->value === []) {
                    $sender->close();
                }

                return;
            }

            $sender->send([$k, $result]);
        });
    }

    $timeout_watcher = null;
    if ($timeout !== null) {
        $timeout_watcher = Async\Scheduler::delay($timeout, static function () use ($sender): void {
            /** @var Result\ResultInterface<string> $failure */
            $failure = new Result\Failure(
                new Exception\TimeoutException('Reached timeout before being able to read all the handles until the end.')
            );

            $sender->send([null, $failure]);
        });
    }

    try {
        while (true) {
            [$k, $result] = $receiver->receive();
            if (null === $k || $result->isFailed()) {
                throw $result->getThrowable();
            }

            yield $k => $result->getResult();
        }
    } catch (Channel\Exception\ClosedChannelException) {
        // completed.
        return;
    } finally {
        if ($timeout_watcher !== null) {
            Async\Scheduler::cancel($timeout_watcher);
        }

        foreach ($watchers->value as $watcher) {
            Async\Scheduler::cancel($watcher);
        }
    }
}
