<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

use Psl\MIME\Internal\CMS\OutputEncoder;
use Psl\MIME\Internal\CMS\SignedDataBuilder;

/**
 * S/MIME message signer that produces CMS SignedData structures.
 *
 * Produces opaque signatures where the content is embedded within the signed structure.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @see SignerInterface For the contract this class implements.
 * @see Verifier For verifying messages produced by this signer.
 *
 * @api
 */
final readonly class Signer implements SignerInterface
{
    /**
     * Create a new S/MIME signer.
     *
     * @param string $certificate Signing certificate in PEM format.
     * @param string $privateKey Private key corresponding to the certificate, in PEM format.
     * @param null|string $passphrase Passphrase to decrypt the private key, or null if unencrypted.
     * @param list<string> $extraCertificates Additional intermediate certificates to include in the
     *                                        SignedData structure (PEM format), enabling the recipient
     *                                        to build the full certificate chain.
     */
    public function __construct(
        private string $certificate,
        private string $privateKey,
        private null|string $passphrase = null,
        private array $extraCertificates = [],
    ) {}

    /**
     * @inheritDoc
     */
    public function sign(string $content, Encoding $encoding = Encoding::PEM): string
    {
        $der = SignedDataBuilder::build(
            $content,
            $this->certificate,
            $this->privateKey,
            $this->passphrase,
            $this->extraCertificates,
        );

        return OutputEncoder::encode($der, $encoding, 'signed-data');
    }
}
