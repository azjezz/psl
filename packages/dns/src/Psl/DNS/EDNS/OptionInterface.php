<?php

declare(strict_types=1);

namespace Psl\DNS\EDNS;

/**
 * Represents a single EDNS0 option (RFC 6891 Section 6.1.2).
 *
 * Each option is a code/data pair carried in the RDATA of an OPT record.
 *
 * @api
 *
 * @inheritors CookieOption|ECSOption|ExtendedDNSErrorOption|KeyTagOption|NSIDOption|PaddingOption|RawOption|TCPKeepaliveOption
 */
interface OptionInterface
{
    /**
     * The EDNS0 option code as assigned by IANA.
     */
    public int $code { get; }

    /**
     * Encode the option data (excluding the code and length header).
     */
    public function toWireFormat(): string;
}
