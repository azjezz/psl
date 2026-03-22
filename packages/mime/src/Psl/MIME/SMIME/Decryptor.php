<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

use Psl\MIME\Internal\CMS\EnvelopedDataParser;
use Psl\MIME\Internal\CMS\OutputEncoder;

/**
 * S/MIME message decryptor that parses CMS EnvelopedData structures.
 *
 * Decrypts S/MIME encrypted messages using the recipient's private key to unwrap the
 * content encryption key and recover the original plaintext.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @see DecryptorInterface For the contract this class implements.
 * @see Encryptor For producing messages that this decryptor can process.
 *
 * @api
 */
final readonly class Decryptor implements DecryptorInterface
{
    /**
     * Create a new S/MIME decryptor.
     *
     * @param string $privateKey The recipient's private key in PEM format, corresponding to the
     *                           certificate used during encryption.
     * @param null|string $passphrase Passphrase to decrypt the private key, or null if unencrypted.
     */
    public function __construct(
        private string $privateKey,
        private null|string $passphrase = null,
    ) {}

    /**
     * @inheritDoc
     */
    public function decrypt(string $encryptedMessage, Encoding $encoding = Encoding::PEM): string
    {
        $der = OutputEncoder::decode($encryptedMessage, $encoding);

        return EnvelopedDataParser::parse($der, $this->privateKey, $this->passphrase);
    }
}
