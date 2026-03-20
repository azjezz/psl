<?php

declare(strict_types=1);

namespace Psl\Binary;

use Override;
use Psl\IO;

/**
 * Binary reader that reads directly from an IO handle.
 *
 * Each read method reads the required number of bytes from the underlying
 * handle and decodes the value, avoiding loading all data into memory.
 */
final readonly class HandleReader implements ReaderInterface
{
    use ReaderConvenienceMethodsTrait;

    public function __construct(
        private IO\ReadHandleInterface $handle,
        private Endianness $endianness = Endianness::Big,
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u8(): int
    {
        return namespace\decode_u8($this->consume(1));
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u16(null|Endianness $endianness = null): int
    {
        return namespace\decode_u16($this->consume(2), $endianness ?? $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u32(null|Endianness $endianness = null): int
    {
        return namespace\decode_u32($this->consume(4), $endianness ?? $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u64(null|Endianness $endianness = null): int
    {
        return namespace\decode_u64($this->consume(8), $endianness ?? $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i8(): int
    {
        return namespace\decode_i8($this->consume(1));
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i16(null|Endianness $endianness = null): int
    {
        return namespace\decode_i16($this->consume(2), $endianness ?? $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i32(null|Endianness $endianness = null): int
    {
        return namespace\decode_i32($this->consume(4), $endianness ?? $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i64(null|Endianness $endianness = null): int
    {
        return namespace\decode_i64($this->consume(8), $endianness ?? $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function f32(null|Endianness $endianness = null): float
    {
        return namespace\decode_f32($this->consume(4), $endianness ?? $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function f64(null|Endianness $endianness = null): float
    {
        return namespace\decode_f64($this->consume(8), $endianness ?? $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function bytes(int $length): string
    {
        return $this->consume($length);
    }

    /**
     * {@inheritDoc}
     *
     * When the underlying handle implements {@see IO\SeekHandleInterface}, this method
     * uses seeking to advance the position efficiently. Otherwise, it reads and discards
     * the bytes.
     */
    #[Override]
    public function skip(int $length): void
    {
        if ($length === 0) {
            return;
        }

        if ($this->handle instanceof IO\SeekHandleInterface) {
            try {
                /** @var int<0, max> $target */
                $target = $this->handle->tell() + $length;
                $this->handle->seek($target);
            } catch (IO\Exception\RuntimeException $e) {
                throw new Exception\UnderflowException(
                    'Failed to skip ' . $length . ' bytes: the handle reached end of data.',
                    previous: $e,
                );
            }

            return;
        }

        $this->consume($length);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function isConsumed(): bool
    {
        return $this->handle->reachedEndOfDataSource();
    }

    /**
     * Read the specified number of bytes from the handle.
     *
     * @param int<0, max> $length
     *
     * @throws Exception\UnderflowException If the handle reaches end of data before all bytes are read.
     */
    private function consume(int $length): string
    {
        if ($length === 0) {
            return '';
        }

        try {
            return $this->handle->readFixedSize($length);
        } catch (IO\Exception\RuntimeException $e) {
            throw new Exception\UnderflowException(
                'Expected to read ' . $length . ' bytes, but the handle reached end of data.',
                previous: $e,
            );
        }
    }
}
