<?php

declare(strict_types=1);

namespace Psl\Binary;

use Override;

use function strlen;

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
        return $this->u8(strlen($value))->bytes($value);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u16PrefixedBytes(string $value, null|Endianness $endianness = null): static
    {
        return $this->u16(strlen($value), $endianness)->bytes($value);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u32PrefixedBytes(string $value, null|Endianness $endianness = null): static
    {
        return $this->u32(strlen($value), $endianness)->bytes($value);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u64PrefixedBytes(string $value, null|Endianness $endianness = null): static
    {
        return $this->u64(strlen($value), $endianness)->bytes($value);
    }
}
