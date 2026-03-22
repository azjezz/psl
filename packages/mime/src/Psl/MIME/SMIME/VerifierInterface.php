<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

use Psl\MIME\Exception\CMSException;
use Psl\MIME\Exception\SMIMEException;

/**
 * Contract for S/MIME signature verification.
 *
 * Implementations parse CMS SignedData structures, validate the digital signature,
 * and optionally verify the signer's certificate chain against trusted CA certificates.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @see Verifier For the default implementation.
 *
 * @api
 */
interface VerifierInterface
{
    /**
     * Verify an S/MIME signed message and extract the original content.
     *
     * Parses the CMS SignedData structure, validates the signature, and returns a
     * {@see VerificationResult} containing the extracted content and verification metadata.
     *
     * @param string $signedMessage The signed CMS message to verify.
     * @param Encoding $encoding The encoding format of the signed message.
     * @param bool $verifyCertificateChain Whether to validate the signer's certificate against
     *                                     the trusted CA certificates. Set to false to verify
     *                                     only the cryptographic signature itself.
     *
     * @throws SMIMEException If signature verification fails.
     * @throws CMSException If the CMS structure is malformed or cannot be parsed.
     */
    public function verify(
        string $signedMessage,
        Encoding $encoding = Encoding::PEM,
        bool $verifyCertificateChain = true,
    ): VerificationResult;
}
