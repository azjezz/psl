<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

use Psl\MIME\Exception\CMSException;
use Psl\MIME\Exception\SMIMEException;

/**
 * Contract for S/MIME message signing.
 *
 * Implementations produce CMS SignedData structures containing the original content
 * and a digital signature, as defined in RFC 5652 and RFC 8551.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @see Signer For the default implementation.
 *
 * @api
 */
interface SignerInterface
{
    /**
     * Sign the given content and return the resulting CMS SignedData message.
     *
     * The returned string contains an opaque signature with the content embedded within
     * the signed structure, encoded according to the specified {@see Encoding}.
     *
     * @param string $content The raw content to sign.
     * @param Encoding $encoding The output encoding format for the signed message.
     *
     * @throws SMIMEException If the signing operation fails.
     * @throws CMSException If the signing certificate or private key is invalid.
     */
    public function sign(string $content, Encoding $encoding = Encoding::PEM): string;
}
