<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

use Psl\MIME\Exception\CMSException;
use Psl\MIME\Exception\SMIMEException;

/**
 * Contract for S/MIME message encryption.
 *
 * Implementations produce CMS EnvelopedData structures that encrypt content for one or more
 * recipients using their X.509 public-key certificates, as defined in RFC 5652 and RFC 8551.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @see Encryptor For the default implementation.
 *
 * @api
 */
interface EncryptorInterface
{
    /**
     * Encrypt the given content and return the resulting CMS EnvelopedData message.
     *
     * The content is encrypted with a symmetric {@see CipherAlgorithm}, and the content encryption
     * key is wrapped for each recipient using their public key from their X.509 certificate.
     *
     * @param string $content The raw content to encrypt.
     * @param Encoding $encoding The output encoding format for the encrypted message.
     *
     * @throws SMIMEException If the encryption operation fails.
     * @throws CMSException If a recipient certificate is invalid or cannot be parsed.
     */
    public function encrypt(string $content, Encoding $encoding = Encoding::PEM): string;
}
