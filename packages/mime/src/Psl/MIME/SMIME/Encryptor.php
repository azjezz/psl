<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

use Psl\MIME\Internal\CMS\EnvelopedDataBuilder;
use Psl\MIME\Internal\CMS\OutputEncoder;

/**
 * S/MIME message encryptor that produces CMS EnvelopedData structures.
 *
 * Encrypts content for one or more recipients using their X.509 certificates.
 * Each recipient's public key is used to wrap the symmetric content encryption key,
 * allowing only the intended recipients to decrypt the message.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @see EncryptorInterface For the contract this class implements.
 * @see Decryptor For decrypting messages produced by this encryptor.
 *
 * @api
 */
final readonly class Encryptor implements EncryptorInterface
{
    /**
     * Create a new S/MIME encryptor.
     *
     * @param list<string> $recipientCertificates One or more recipient X.509 certificates in PEM format.
     *                                            Each certificate's public key will be used to wrap the
     *                                            content encryption key.
     * @param CipherAlgorithm $cipher The symmetric cipher algorithm used to encrypt the content.
     */
    public function __construct(
        private array $recipientCertificates,
        private CipherAlgorithm $cipher = CipherAlgorithm::Aes256Cbc,
    ) {}

    /**
     * @inheritDoc
     */
    public function encrypt(string $content, Encoding $encoding = Encoding::PEM): string
    {
        $der = EnvelopedDataBuilder::build($content, $this->recipientCertificates, $this->cipher);

        return OutputEncoder::encode($der, $encoding, 'enveloped-data');
    }
}
