<?php

declare(strict_types=1);

namespace Psl\Binary;

use Override;
use Psl\IO;

/**
 * Binary writer that writes directly to an IO handle.
 *
 * Each write method encodes the value and immediately writes it to the
 * underlying handle, avoiding buffering in PHP memory.
 */
final readonly class HandleWriter implements WriterInterface
{
    use WriterConvenienceMethodsTrait;

    public function __construct(
        private IO\WriteHandleInterface $handle,
        private Endianness $endianness = Endianness::Big,
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u8(int $value): static
    {
        $this->handle->writeAll(namespace\encode_u8($value));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u16(int $value, null|Endianness $endianness = null): static
    {
        $this->handle->writeAll(namespace\encode_u16($value, $endianness ?? $this->endianness));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u32(int $value, null|Endianness $endianness = null): static
    {
        $this->handle->writeAll(namespace\encode_u32($value, $endianness ?? $this->endianness));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u64(int $value, null|Endianness $endianness = null): static
    {
        $this->handle->writeAll(namespace\encode_u64($value, $endianness ?? $this->endianness));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i8(int $value): static
    {
        $this->handle->writeAll(namespace\encode_i8($value));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i16(int $value, null|Endianness $endianness = null): static
    {
        $this->handle->writeAll(namespace\encode_i16($value, $endianness ?? $this->endianness));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i32(int $value, null|Endianness $endianness = null): static
    {
        $this->handle->writeAll(namespace\encode_i32($value, $endianness ?? $this->endianness));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i64(int $value, null|Endianness $endianness = null): static
    {
        $this->handle->writeAll(namespace\encode_i64($value, $endianness ?? $this->endianness));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function f32(float $value, null|Endianness $endianness = null): static
    {
        $this->handle->writeAll(namespace\encode_f32($value, $endianness ?? $this->endianness));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function f64(float $value, null|Endianness $endianness = null): static
    {
        $this->handle->writeAll(namespace\encode_f64($value, $endianness ?? $this->endianness));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function bytes(string $value): static
    {
        $this->handle->writeAll($value);

        return $this;
    }
}
