<?php

declare(strict_types=1);

namespace Psl\MIME\DKIM;

use Psl\MIME\Exception\DKIMException;

/**
 * Contract for DKIM message signing.
 *
 * Implementations sign raw MIME messages by computing a DKIM-Signature header and prepending
 * it to the original message, as defined in RFC 6376.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6376 RFC 6376 - DomainKeys Identified Mail (DKIM) Signatures
 *
 * @see Signer For the default implementation.
 *
 * @api
 */
interface SignerInterface
{
    /**
     * Sign a raw MIME message and return the message with the DKIM-Signature header prepended.
     *
     * The input must be a complete MIME message consisting of headers, a blank line (CRLF),
     * and the message body. The returned string is the same message with a DKIM-Signature
     * header inserted before the original headers.
     *
     * @param string $message The raw MIME message (headers + CRLF + body).
     *
     * @throws DKIMException If the signing operation fails (e.g., invalid key or unsupported algorithm).
     *
     * @return string The signed message with the DKIM-Signature header prepended.
     */
    public function sign(string $message): string;
}
