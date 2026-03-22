<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

use Psl\DateTime\DateTimeInterface;

/**
 * Immutable value object holding the result of an S/MIME signature verification.
 *
 * Returned by {@see VerifierInterface::verify()} after parsing and validating a CMS SignedData
 * structure. Contains the extracted plaintext content, the verification outcome, and optional
 * metadata about the signer and signature.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @api
 */
final readonly class VerificationResult
{
    /**
     * @param string $content The original plaintext content extracted from the SignedData structure.
     * @param bool $valid Whether the cryptographic signature is valid and, if requested, the
     *                     signer's certificate chain was successfully verified.
     * @param null|string $signerCertificate The signer's X.509 certificate in PEM format, or null
     *                                       if it could not be extracted from the SignedData structure.
     * @param null|DigestAlgorithm $digestAlgorithm The digest algorithm used to compute the message
     *                                              digest in the signature, or null if unknown.
     * @param null|DateTimeInterface $signingTime The signing time extracted from the SignedAttributes,
     *                                            or null if the signing-time attribute was not present.
     */
    public function __construct(
        public string $content,
        public bool $valid,
        public null|string $signerCertificate = null,
        public null|DigestAlgorithm $digestAlgorithm = null,
        public null|DateTimeInterface $signingTime = null,
    ) {}
}
