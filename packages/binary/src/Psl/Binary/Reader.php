<?php

declare(strict_types=1);

namespace Psl\Binary;

use Override;

use function strlen;
use function substr;

/**
 * Mutable cursor-based binary reader.
 *
 * Each read method advances the internal cursor. All methods throw
 * {@see Exception\UnderflowException} if insufficient data remains.
 */
final class Reader implements BufferedReaderInterface
{
    use ReaderConvenienceMethodsTrait;

    /**
     * @var int<0, max>
     */
    private int $cursor = 0;

    /**
     * @var int<0, max>
     */
    private readonly int $length;

    public function __construct(
        private readonly string $bytes,
        private readonly Endianness $endianness = Endianness::Big,
    ) {
        $this->length = strlen($bytes);
    }

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
     */
    #[Override]
    public function cursor(): int
    {
        return $this->cursor;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function length(): int
    {
        return $this->length;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function remaining(): int
    {
        /** @var int<0, max> */
        return $this->length - $this->cursor;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function isConsumed(): bool
    {
        return $this->cursor >= $this->length;
    }

    /**
     * Consume the specified number of bytes from the buffer, advancing the cursor.
     *
     * @param int<0, max> $length
     *
     * @throws Exception\UnderflowException If insufficient data remains.
     */
    private function consume(int $length): string
    {
        if (($this->cursor + $length) > $this->length) {
            throw new Exception\UnderflowException(
                'Expected at least ' . $length . ' bytes, got ' . ($this->length - $this->cursor) . '.',
            );
        }

        $data = substr($this->bytes, $this->cursor, $length);
        $this->cursor += $length;

        return $data;
    }
}
