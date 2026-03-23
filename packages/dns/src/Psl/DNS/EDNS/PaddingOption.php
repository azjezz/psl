<?php

declare(strict_types=1);

namespace Psl\DNS\EDNS;

use function str_repeat;

/**
 * EDNS Padding option (RFC 7830, option code 12).
 *
 * Pads DNS messages to a target size, obscuring query and response
 * lengths for privacy when used with encrypted transports (DoT/DoH).
 *
 * @api
 */
final class PaddingOption implements OptionInterface
{
    /**
     * {@inheritDoc}
     */
    public int $code {
        get => 12;
    }

    /**
     * @param non-negative-int $length Number of padding bytes.
     */
    public function __construct(
        public readonly int $length,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function toWireFormat(): string
    {
        return str_repeat("\x00", $this->length);
    }
}
