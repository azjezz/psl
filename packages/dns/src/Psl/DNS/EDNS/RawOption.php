<?php

declare(strict_types=1);

namespace Psl\DNS\EDNS;

/**
 * Fallback for unknown EDNS0 option codes.
 *
 * Preserves the raw option data for round-trip fidelity when
 * proxying or forwarding DNS packets.
 *
 * @api
 */
final readonly class RawOption implements OptionInterface
{
    /**
     * @param int $code The EDNS0 option code.
     * @param string $data The raw option data bytes.
     */
    public function __construct(
        public int $code,
        public string $data,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function toWireFormat(): string
    {
        return $this->data;
    }
}
