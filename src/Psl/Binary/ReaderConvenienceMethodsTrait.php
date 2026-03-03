<?php

declare(strict_types=1);

namespace Psl\Binary;

use Override;

/**
 * @require-implements ReaderInterface
 */
trait ReaderConvenienceMethodsTrait
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function skip(int $length): void
    {
        if ($length === 0) {
            return;
        }

        $this->bytes($length);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u8PrefixedBytes(): string
    {
        return $this->bytes($this->u8());
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u16PrefixedBytes(null|Endianness $endianness = null): string
    {
        return $this->bytes($this->u16($endianness));
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u32PrefixedBytes(null|Endianness $endianness = null): string
    {
        return $this->bytes($this->u32($endianness));
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function u64PrefixedBytes(null|Endianness $endianness = null): string
    {
        return $this->bytes($this->u64($endianness));
    }
}
