<?php

declare(strict_types=1);

namespace Psl\IO;

use Generator;
use Iterator;
use IteratorAggregate;
use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;

use function strlen;
use function substr;

/**
 * A read handle that lazily consumes an iterable of strings.
 *
 * Each call to {@see read()} advances the underlying iterator and returns
 * the next chunk (or a portion of it, respecting $maxBytes). When the
 * iterator is exhausted, the handle reports EOF.
 *
 * {@see tryRead()} returns only data that has already been fetched from the
 * iterator and is sitting in the internal buffer. It never advances the
 * iterator, so it returns an empty string when the buffer is empty.
 *
 * This is useful for wrapping generators or other lazy string producers as
 * a streaming {@see ReadHandleInterface} without buffering the entire content
 * in memory.
 *
 * @api
 */
final class IterableReadHandle implements ReadHandleInterface, CloseHandleInterface
{
    use ReadHandleConvenienceMethodsTrait;

    /** @var Iterator<string> */
    private Iterator $iterator;
    private bool $started = false;
    private bool $eof = false;
    private bool $closed = false;
    private string $buffer = '';

    /**
     * @param iterable<mixed, string> $iterable
     */
    public function __construct(iterable $iterable)
    {
        if ($iterable instanceof Iterator) {
            $this->iterator = $iterable;
        } elseif ($iterable instanceof IteratorAggregate) {
            /** @var Iterator<mixed, string> $iterator */
            $iterator = $iterable->getIterator();
            while ($iterator instanceof IteratorAggregate) {
                /** @var Iterator<mixed, string> $iterator */
                $iterator = $iterator->getIterator();
            }

            $this->iterator = $iterator;
        } else {
            /** @var Iterator<mixed, string> $iterator */
            $iterator = (static function () use ($iterable): Generator {
                yield from $iterable;
            })();

            $this->iterator = $iterator;
        }
    }

    /**
     * Whether the iterator has been fully consumed.
     *
     * Returns {@see true} once {@see read()} has advanced past the last element
     * of the iterable. Returns {@see false} if the iterator has not been started
     * or still has remaining elements.
     *
     * @throws Exception\AlreadyClosedException If the handle has been closed.
     */
    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        $this->assertHandleIsOpen();

        return $this->eof;
    }

    /**
     * Return data already available in the internal buffer without advancing the iterator.
     *
     * If the buffer is empty, returns an empty string immediately. To fetch
     * the next chunk from the iterator, use {@see read()} instead.
     *
     * @param null|positive-int $maxBytes Maximum number of bytes to return, or null for the entire buffer.
     *
     * @throws Exception\AlreadyClosedException If the handle has been closed.
     */
    #[Override]
    public function tryRead(null|int $maxBytes = null): string
    {
        $this->assertHandleIsOpen();

        if ($this->buffer !== '') {
            return $this->consumeBuffer($maxBytes);
        }

        return '';
    }

    /**
     * Read the next chunk from the iterable, advancing the iterator if the buffer is empty.
     *
     * Returns buffered data first. When the buffer is empty, advances the underlying
     * iterator to obtain the next non-empty string. Returns an empty string when the
     * iterator is exhausted (EOF).
     *
     * @param null|positive-int $maxBytes Maximum number of bytes to return, or null for the entire chunk.
     *
     * @throws Exception\AlreadyClosedException If the handle has been closed.
     * @throws Exception\RuntimeException If the iterator throws during advancement.
     */
    #[Override]
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $this->assertHandleIsOpen();

        if ($this->eof) {
            return '';
        }

        if ($this->buffer !== '') {
            return $this->consumeBuffer($maxBytes);
        }

        $this->advance();

        if ($this->eof) {
            return '';
        }

        return $this->consumeBuffer($maxBytes);
    }

    /**
     * Whether the handle has been closed.
     */
    #[Override]
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close the handle, discarding any buffered data.
     *
     * After closing, all read operations will throw {@see Exception\AlreadyClosedException}.
     * The underlying iterator is not explicitly closed but will no longer be advanced.
     */
    #[Override]
    public function close(): void
    {
        $this->closed = true;
        $this->eof = true;
        $this->buffer = '';
    }

    /**
     * Advance the iterator to the next non-empty value, storing it in the internal buffer.
     *
     * Skips empty strings yielded by the iterator. Sets {@see $eof} to {@see true}
     * when the iterator is exhausted.
     */
    private function advance(): void
    {
        while (true) {
            if (!$this->started) {
                $this->started = true;
                $this->iterator->rewind();
            } else {
                $this->iterator->next();
            }

            if (!$this->iterator->valid()) {
                $this->eof = true;
                return;
            }

            $value = $this->iterator->current();
            if ($value !== null && $value !== '') {
                $this->buffer = $value;
                return;
            }
        }
    }

    /**
     * Return up to $maxBytes from the internal buffer, removing the consumed portion.
     *
     * When $maxBytes is {@see null} or larger than the buffer, the entire buffer is
     * returned and cleared. Otherwise, the first $maxBytes are returned and the
     * remainder stays in the buffer for subsequent reads.
     *
     * @param null|positive-int $maxBytes Maximum bytes to consume, or {@see null} for all.
     */
    private function consumeBuffer(null|int $maxBytes): string
    {
        if ($maxBytes === null || $maxBytes >= strlen($this->buffer)) {
            $result = $this->buffer;
            $this->buffer = '';
            return $result;
        }

        $result = substr($this->buffer, 0, $maxBytes);
        $this->buffer = substr($this->buffer, $maxBytes);
        return $result;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been closed.
     */
    private function assertHandleIsOpen(): void
    {
        if ($this->closed) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }
    }
}
