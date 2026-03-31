<?php

declare(strict_types=1);

namespace Psl\MIME\Internal\CMS;

use OpenSSLAsymmetricKey;
use Psl\MIME\Exception\CMSException;
use Psl\MIME\Exception\SMIMEException;

use function count;
use function openssl_decrypt;
use function openssl_pkey_get_private;
use function openssl_private_decrypt;
use function strlen;

use const OPENSSL_PKCS1_PADDING;
use const OPENSSL_RAW_DATA;

/**
 * Parses and decrypts CMS EnvelopedData structures per RFC 5652 section 6.
 *
 * Decodes a DER-encoded ContentInfo containing EnvelopedData, attempts to unwrap
 * the content-encryption key from each RecipientInfo using the provided private key,
 * and decrypts the AES-CBC encrypted content.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652#section-6
 *
 * @internal
 */
final class EnvelopedDataParser
{
    /**
     * Parse and decrypt a CMS EnvelopedData ContentInfo.
     *
     * @param string $der DER-encoded ContentInfo.
     * @param string $privateKeyPem Recipient private key in PEM format.
     * @param null|string $passphrase Optional passphrase for the private key.
     *
     * @throws SMIMEException If decryption fails.
     * @throws CMSException If the CMS structure is malformed.
     */
    public static function parse(string $der, string $privateKeyPem, null|string $passphrase = null): string
    {
        $contentInfo = DERDecoder::parseSequence($der);
        $elements = DERDecoder::parseAll($contentInfo);

        if (count($elements) < 2) {
            throw CMSException::forMalformedStructure('ContentInfo missing fields');
        }

        if ($elements[0][0] !== 0x06) {
            throw CMSException::forMalformedStructure('ContentInfo missing OID');
        }

        if ($elements[0][1] !== OID::ENVELOPED_DATA) {
            throw CMSException::forMalformedStructure('not EnvelopedData content type');
        }

        if ($elements[1][0] !== 0xA0) {
            throw CMSException::forMalformedStructure('ContentInfo missing [0] EXPLICIT');
        }

        $envelopedData = DERDecoder::parseSequence($elements[1][1]);
        $edElements = DERDecoder::parseAll($envelopedData);

        if (count($edElements) < 3) {
            throw CMSException::forMalformedStructure('EnvelopedData too short');
        }

        if ($edElements[1][0] !== 0x31) {
            throw CMSException::forMalformedStructure('RecipientInfos not a SET');
        }

        $recipientInfos = DERDecoder::parseAll($edElements[1][1]);

        $key = openssl_pkey_get_private($privateKeyPem, $passphrase ?? '');
        if (!$key instanceof OpenSSLAsymmetricKey) {
            throw CMSException::forInvalidKey('failed to load private key');
        }

        foreach ($recipientInfos as [$riTag, $riContent]) {
            if ($riTag !== 0x30) {
                continue;
            }

            $cek = self::tryDecryptRecipientInfo($riContent, $key);
            if ($cek === null) {
                continue;
            }

            $decrypted = self::tryDecryptContent($edElements[2][1], $cek);
            if ($decrypted !== null) {
                return $decrypted;
            }
        }

        throw SMIMEException::forDecryptionFailure();
    }

    /**
     * Attempt to decrypt the encrypted key from a single KeyTransRecipientInfo.
     *
     * Returns the decrypted content-encryption key (CEK) on success, or null if
     * the private key does not match this recipient or decryption fails.
     *
     * @param string $riContent The inner bytes of a KeyTransRecipientInfo SEQUENCE.
     * @param OpenSSLAsymmetricKey $key The recipient's private key.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-6.2.1
     */
    private static function tryDecryptRecipientInfo(string $riContent, OpenSSLAsymmetricKey $key): null|string
    {
        $riElements = DERDecoder::parseAll($riContent);
        if (count($riElements) < 4) {
            return null;
        }

        $encryptedKey = $riElements[3][1];

        $cek = '';
        $result = openssl_private_decrypt($encryptedKey, $cek, $key, OPENSSL_PKCS1_PADDING);
        if ($result === false) {
            return null;
        }

        return $cek;
    }

    /**
     * Attempt to decrypt the EncryptedContentInfo using the given content-encryption key.
     *
     * Parses the encryption algorithm OID and IV from the EncryptedContentInfo,
     * validates the CEK length, and performs AES-CBC decryption.
     *
     * @param string $encryptedContentInfo The inner bytes of the EncryptedContentInfo SEQUENCE.
     * @param string $cek The decrypted content-encryption key.
     *
     * @throws CMSException If the EncryptedContentInfo structure is malformed or uses an unsupported algorithm.
     *
     * @return null|string The decrypted plaintext, or null if decryption fails (e.g. wrong key length).
     */
    private static function tryDecryptContent(string $encryptedContentInfo, string $cek): null|string
    {
        $eciElements = DERDecoder::parseAll($encryptedContentInfo);

        if (count($eciElements) < 3) {
            throw CMSException::forMalformedStructure('EncryptedContentInfo too short');
        }

        if ($eciElements[1][0] !== 0x30) {
            throw CMSException::forMalformedStructure('encryption algorithm not a SEQUENCE');
        }

        $algoElements = DERDecoder::parseAll($eciElements[1][1]);
        if (count($algoElements) < 2) {
            throw CMSException::forMalformedStructure('encryption algorithm missing fields');
        }

        $algoOid = $algoElements[0][1];
        $cipher = match ($algoOid) {
            OID::AES_128_CBC => 'aes-128-cbc',
            OID::AES_192_CBC => 'aes-192-cbc',
            OID::AES_256_CBC => 'aes-256-cbc',
            default => throw CMSException::forMalformedStructure('unsupported encryption algorithm'),
        };

        $expectedKeyLen = match ($cipher) {
            'aes-128-cbc' => 16,
            'aes-192-cbc' => 24,
            'aes-256-cbc' => 32,
        };

        if (strlen($cek) !== $expectedKeyLen) {
            return null;
        }

        if ($algoElements[1][0] !== 0x04) {
            throw CMSException::forMalformedStructure('IV not an OCTET STRING');
        }

        $iv = $algoElements[1][1];

        $ciphertext = $eciElements[2][1];

        $decrypted = openssl_decrypt($ciphertext, $cipher, $cek, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            return null;
        }

        return $decrypted;
    }
}
