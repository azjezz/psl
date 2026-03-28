<?php

declare(strict_types=1);

namespace Psl\IO;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;

/**
 * A read handle that is always at EOF.
 *
 * Every read operation returns an empty string immediately, and
 * {@see reachedEndOfDataSource()} always returns {@see true} from the start.
 *
 * Unlike `new MemoryHandle('')`, which only reports EOF after the first read
 * attempt, this handle reports EOF unconditionally.
 *
 * @api
 */
final class SinkReadHandle implements ReadHandleInterface, CloseHandleInterface
{
    use ReadHandleConvenienceMethodsTrait;

    private bool $closed = false;

    /**
     * Always returns {@see true}. This handle has no data source.
     *
     * @throws Exception\AlreadyClosedException If the handle has been closed.
     */
    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        $this->assertHandleIsOpen();

        return true;
    }

    /**
     * Always returns an empty string. No data is ever available.
     *
     * @param null|positive-int $maxBytes Ignored.
     *
     * @throws Exception\AlreadyClosedException If the handle has been closed.
     */
    #[Override]
    public function tryRead(null|int $maxBytes = null): string
    {
        $this->assertHandleIsOpen();

        return '';
    }

    /**
     * Always returns an empty string. No data is ever available.
     *
     * @param null|positive-int $maxBytes Ignored.
     *
     * @throws Exception\AlreadyClosedException If the handle has been closed.
     */
    #[Override]
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $this->assertHandleIsOpen();

        return '';
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
     * Close the handle.
     *
     * After closing, all operations will throw {@see Exception\AlreadyClosedException}.
     */
    #[Override]
    public function close(): void
    {
        $this->closed = true;
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
