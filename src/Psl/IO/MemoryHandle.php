<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\DateTime\Duration;
use Psl\Math;

use function str_repeat;
use function strlen;
use function substr;

final class MemoryHandle implements CloseSeekReadWriteHandleInterface
{
    use WriteHandleConvenienceMethodsTrait;
    use ReadHandleConvenienceMethodsTrait;

    /**
     * @var int<0, max>
     */
    private int $offset = 0;
    private string $buffer;
    private bool $closed = false;
    private bool $reachedEof = false;

    /**
     * @psalm-external-mutation-free
     */
    public function __construct(string $buffer = '')
    {
        $this->buffer = $buffer;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function reachedEndOfDataSource(): bool
    {
        $this->assertHandleIsOpen();

        return $this->reachedEof;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    #[\Override]
    public function tryRead(null|int $max_bytes = null): string
    {
        $this->assertHandleIsOpen();

        if (null === $max_bytes) {
            $max_bytes = Math\INT64_MAX;
        }

        $length = strlen($this->buffer);
        if ($this->offset >= $length) {
            $this->reachedEof = true;

            return '';
        }

        $length -= $this->offset;
        $length = $length > $max_bytes ? $max_bytes : $length;
        $result = substr($this->buffer, $this->offset, $length);
        $this->offset = ($offset = $this->offset + $length) >= 0 ? $offset : 0;

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    #[\Override]
    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        return $this->tryRead($max_bytes);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    #[\Override]
    public function seek(int $offset): void
    {
        $this->assertHandleIsOpen();

        $this->offset = $offset;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function tell(): int
    {
        $this->assertHandleIsOpen();

        return $this->offset;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    #[\Override]
    public function tryWrite(string $bytes, null|Duration $timeout = null): int
    {
        $this->assertHandleIsOpen();
        $length = strlen($this->buffer);
        if ($length < $this->offset) {
            $this->buffer .= str_repeat("\0", $this->offset - $length);
            $length = $this->offset;
        }

        $bytes_length = strlen($bytes);
        $new = substr($this->buffer, 0, $this->offset) . $bytes;
        if ($this->offset < $length) {
            $offset = $this->offset + $bytes_length;
            $offset = $offset > $length ? $length : $offset;
            $new .= substr($this->buffer, $offset);
        }

        $this->buffer = $new;
        $this->offset = ($offset = $this->offset + $bytes_length) >= 0 ? $offset : 0;
        return $bytes_length;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    #[\Override]
    public function write(string $bytes, null|Duration $timeout = null): int
    {
        return $this->tryWrite($bytes);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-external-mutation-free
     */
    #[\Override]
    public function close(): void
    {
        $this->closed = true;
    }

    /**
     * @psalm-mutation-free
     */
    public function getBuffer(): string
    {
        return $this->buffer;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     *
     * @psalm-mutation-free
     */
    private function assertHandleIsOpen(): void
    {
        if ($this->closed) {
            throw new Exception\AlreadyClosedException('Handle has already been closed.');
        }
    }
}
