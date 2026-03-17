<?php

declare(strict_types=1);

namespace Psl\IO;

use Generator;
use Psl;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Channel;
use Psl\Result;
use Revolt\EventLoop;

use function sprintf;

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
 * @param iterable<T, ReadHandleInterface&StreamHandleInterface> $handles
 *
 * @throws Exception\AlreadyClosedException If one of the handles has been already closed.
 * @throws Exception\RuntimeException If an error occurred during the operation.
 * @throws CancelledException If the operation is cancelled.
 *
 * @return Generator<T, string, mixed, null>
 */
function streaming(iterable $handles, CancellationTokenInterface $cancellation = new NullCancellationToken()): Generator
{
    /**
     * @var Channel\ReceiverInterface<array{0: T|null, 1: Result\ResultInterface<string>}> $receiver
     * @var Channel\SenderInterface<array{0: T|null, 1: Result\ResultInterface<string>}> $sender
     */
    [$receiver, $sender] = Channel\unbounded();

    /** @var Psl\Ref<array<T, string>> $watchers */
    $watchers = new Psl\Ref([]);
    foreach ($handles as $index => $handle) {
        $stream = $handle->getStream();
        if (null === $stream) {
            throw new Exception\AlreadyClosedException(sprintf('Handle "%s" is already closed.', (string) $index));
        }

        // @mago-expect analysis:possibly-invalid-argument
        $watchers->value[$index] = EventLoop::onReadable($stream, static function (string $watcher) use (
            $index,
            $handle,
            $sender,
            $watchers,
        ): void {
            try {
                $result = Result\wrap($handle->tryRead(...));
                if ($result->isFailed() || $result->isSucceeded() && $result->getResult() === '') {
                    EventLoop::cancel($watcher);
                    unset($watchers->value[$index]);
                }

                $sender->send([$index, $result]);
            } finally {
                if ([] === $watchers->value) {
                    $sender->close();
                }
            }
        });
    }

    $cancellationSubscription = null;
    if ($cancellation->cancellable) {
        $cancellationSubscription = $cancellation->subscribe(static function (CancelledException $exception) use (
            $sender,
        ): void {
            /** @var Result\ResultInterface<string> $failure */
            $failure = new Result\Failure($exception);

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
        if (null !== $cancellationSubscription) {
            $cancellation->unsubscribe($cancellationSubscription);
        }

        foreach ($watchers->value as $watcher) {
            EventLoop::cancel($watcher);
        }
    }
}
