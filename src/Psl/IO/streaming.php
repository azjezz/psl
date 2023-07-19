<?php

declare(strict_types=1);

namespace Psl\IO;

use Generator;
use Psl;
use Psl\Channel;
use Psl\Result;
use Psl\Str;
use Revolt\EventLoop;

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
 * @return Generator<T, string, mixed, null>
 */
function streaming(iterable $handles, ?float $timeout = null): Generator
{
    /**
     * @psalm-suppress UnnecessaryVarAnnotation
     *
     * @var Channel\ReceiverInterface<array{0: T|null, 1: Result\ResultInterface<string>}> $receiver
     * @var Channel\SenderInterface<array{0: T|null, 1: Result\ResultInterface<string>}> $sender
     */
    [$receiver, $sender] = Channel\unbounded();

    /** @var Psl\Ref<array<T, string>> $watchers */
    $watchers = new Psl\Ref([]);
    foreach ($handles as $index => $handle) {
        $stream = $handle->getStream();
        if ($stream === null) {
            throw new Exception\AlreadyClosedException(Str\format('Handle "%s" is already closed.', (string) $index));
        }

        $watchers->value[$index] = EventLoop::onReadable($stream, static function (string $watcher) use ($index, $handle, $sender, $watchers): void {
            try {
                $result = Result\wrap($handle->tryRead(...));
                if ($result->isFailed() || ($result->isSucceeded() && $result->getResult() === '')) {
                    EventLoop::cancel($watcher);
                    unset($watchers->value[$index]);
                }

                $sender->send([$index, $result]);
            } finally {
                if ($watchers->value === []) {
                    $sender->close();
                }
            }
        });
    }

    $timeout_watcher = null;
    if ($timeout !== null) {
        $timeout_watcher = EventLoop::delay($timeout, static function () use ($sender): void {
            /** @var Result\ResultInterface<string> $failure */
            $failure = new Result\Failure(
                new Exception\TimeoutException('Reached timeout before being able to read all the handles until the end.')
            );

            $sender->send([null, $failure]);
        });
    }

    try {
        while (true) {
            [$index, $result] = $receiver->receive();
            if (null === $index || $result->isFailed()) {
                throw $result->getThrowable();
            }

            yield $index => $result->getResult();
        }
    } catch (Channel\Exception\ClosedChannelException) {
        // completed.
        return;
    } finally {
        if ($timeout_watcher !== null) {
            EventLoop::cancel($timeout_watcher);
        }

        foreach ($watchers->value as $watcher) {
            EventLoop::cancel($watcher);
        }
    }
}
