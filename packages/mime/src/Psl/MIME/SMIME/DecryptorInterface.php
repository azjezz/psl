<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

use Psl\MIME\Exception\CMSException;
use Psl\MIME\Exception\SMIMEException;

/**
 * Contract for S/MIME message decryption.
 *
 * Implementations parse CMS EnvelopedData structures and decrypt the content using
 * the recipient's private key, as defined in RFC 5652 and RFC 8551.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @see Decryptor For the default implementation.
 *
 * @api
 */
interface DecryptorInterface
{
    /**
     * Decrypt an S/MIME encrypted message and return the original plaintext content.
     *
     * Parses the CMS EnvelopedData structure, unwraps the content encryption key using
     * the recipient's private key, and decrypts the content.
     *
     * @param string $encryptedMessage The encrypted CMS message to decrypt.
     * @param Encoding $encoding The encoding format of the encrypted message.
     *
     * @throws SMIMEException If the decryption operation fails.
     * @throws CMSException If the CMS structure is malformed or the private key cannot decrypt the message.
     */
    public function decrypt(string $encryptedMessage, Encoding $encoding = Encoding::PEM): string;
}
