<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

use Psl\MIME\Internal\CMS\OutputEncoder;
use Psl\MIME\Internal\CMS\SignedDataParser;

/**
 * S/MIME signature verifier that parses and validates CMS SignedData structures.
 *
 * Verifies the cryptographic signature and optionally validates the signer's certificate
 * chain against a set of trusted CA certificates.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @see VerifierInterface For the contract this class implements.
 * @see Signer For producing messages that this verifier can validate.
 *
 * @api
 */
final readonly class Verifier implements VerifierInterface
{
    /**
     * Create a new S/MIME verifier.
     *
     * @param list<string> $trustedCertificates Trusted CA certificates in PEM format used to validate
     *                                          the signer's certificate chain. An empty list means no
     *                                          chain validation is possible unless verification is
     *                                          performed with certificate chain checking disabled.
     */
    public function __construct(
        private array $trustedCertificates = [],
    ) {}

    /**
     * @inheritDoc
     */
    public function verify(
        string $signedMessage,
        Encoding $encoding = Encoding::PEM,
        bool $verifyCertificateChain = true,
    ): VerificationResult {
        $der = OutputEncoder::decode($signedMessage, $encoding);

        return SignedDataParser::parse($der, $this->trustedCertificates, !$verifyCertificateChain);
    }
}
