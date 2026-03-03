<?php

declare(strict_types=1);

namespace Psl\Binary;

use Override;
use Psl\Str\Byte;

/**
 * @require-implements WriterInterface
 */
trait WriterConvenienceMethodsTrait
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u8PrefixedBytes(string $value): static
    {
        return $this->u8(Byte\length($value))->bytes($value);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u16PrefixedBytes(string $value, null|Endianness $endianness = null): static
    {
        return $this->u16(Byte\length($value), $endianness)->bytes($value);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u32PrefixedBytes(string $value, null|Endianness $endianness = null): static
    {
        return $this->u32(Byte\length($value), $endianness)->bytes($value);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u64PrefixedBytes(string $value, null|Endianness $endianness = null): static
    {
        return $this->u64(Byte\length($value), $endianness)->bytes($value);
    }
}
