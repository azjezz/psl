<?php

declare(strict_types=1);

namespace Psl\DNS\EDNS;

use function pack;

/**
 * Extended DNS Error option (RFC 8914, option code 15).
 *
 * Provides additional error information beyond the response code,
 * with a structured info code and optional human-readable text.
 *
 * @api
 */
final class ExtendedDNSErrorOption implements OptionInterface
{
    /**
     * {@inheritDoc}
     */
    public int $code {
        get => 15;
    }

    /**
     * @param int<0, 65535> $infoCode Error info code (see {@see ExtendedDNSError} for known values).
     * @param string $extraText Optional UTF-8 diagnostic text.
     */
    public function __construct(
        public readonly int $infoCode,
        public readonly string $extraText = '',
    ) {}

    /**
     * {@inheritDoc}
     */
    public function toWireFormat(): string
    {
        $result = pack('n', $this->infoCode);
        if ($this->extraText !== '') {
            $result .= $this->extraText;
        }

        return $result;
    }
}
