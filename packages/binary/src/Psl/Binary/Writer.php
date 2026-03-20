<?php

declare(strict_types=1);

namespace Psl\Binary;

use Override;
use Psl\Default\DefaultInterface;

/**
 * Immutable binary writer that builds a byte string incrementally.
 *
 * Each method returns a new Writer instance with the appended bytes.
 *
 * @psalm-immutable
 */
final readonly class Writer implements BufferedWriterInterface, DefaultInterface
{
    use WriterConvenienceMethodsTrait;

    /**
     * {@inheritDoc}
     */
    #[Override]
    public static function default(): static
    {
        return new self();
    }

    public function __construct(
        private string $bytes = '',
        private Endianness $endianness = Endianness::Big,
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u8(int $value): static
    {
        return new self($this->bytes . namespace\encode_u8($value), $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u16(int $value, null|Endianness $endianness = null): static
    {
        return new self(
            $this->bytes . namespace\encode_u16($value, $endianness ?? $this->endianness),
            $this->endianness,
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u32(int $value, null|Endianness $endianness = null): static
    {
        return new self(
            $this->bytes . namespace\encode_u32($value, $endianness ?? $this->endianness),
            $this->endianness,
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u64(int $value, null|Endianness $endianness = null): static
    {
        return new self(
            $this->bytes . namespace\encode_u64($value, $endianness ?? $this->endianness),
            $this->endianness,
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i8(int $value): static
    {
        return new self($this->bytes . namespace\encode_i8($value), $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i16(int $value, null|Endianness $endianness = null): static
    {
        return new self(
            $this->bytes . namespace\encode_i16($value, $endianness ?? $this->endianness),
            $this->endianness,
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i32(int $value, null|Endianness $endianness = null): static
    {
        return new self(
            $this->bytes . namespace\encode_i32($value, $endianness ?? $this->endianness),
            $this->endianness,
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function i64(int $value, null|Endianness $endianness = null): static
    {
        return new self(
            $this->bytes . namespace\encode_i64($value, $endianness ?? $this->endianness),
            $this->endianness,
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function f32(float $value, null|Endianness $endianness = null): static
    {
        return new self(
            $this->bytes . namespace\encode_f32($value, $endianness ?? $this->endianness),
            $this->endianness,
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function f64(float $value, null|Endianness $endianness = null): static
    {
        return new self(
            $this->bytes . namespace\encode_f64($value, $endianness ?? $this->endianness),
            $this->endianness,
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function bytes(string $value): static
    {
        return new self($this->bytes . $value, $this->endianness);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return $this->bytes;
    }

    /**
     * @psalm-mutation-free
     */
    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }
}
