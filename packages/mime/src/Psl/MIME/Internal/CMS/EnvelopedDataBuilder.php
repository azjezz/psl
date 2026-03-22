<?php

declare(strict_types=1);

namespace Psl\MIME\Internal\CMS;

use OpenSSLAsymmetricKey;
use Psl\MIME\Exception\CMSException;
use Psl\MIME\Exception\SMIMEException;
use Psl\MIME\SMIME\CipherAlgorithm;
use Random\RandomException;

use function count;
use function openssl_encrypt;
use function openssl_pkey_get_public;
use function openssl_public_encrypt;
use function random_bytes;

/**
 * Builds CMS EnvelopedData structures per RFC 5652 section 6.
 *
 * Generates a random content-encryption key (CEK), encrypts the content using the
 * specified AES-CBC cipher, then wraps the CEK for each recipient using RSA key transport
 * (KeyTransRecipientInfo).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652#section-6
 *
 * @internal
 */
final class EnvelopedDataBuilder
{
    /**
     * Build a CMS EnvelopedData ContentInfo.
     *
     * @param string $content The content to encrypt.
     * @param list<string> $recipientCertificatesPem Recipient certificates in PEM format.
     * @param CipherAlgorithm $cipher The cipher algorithm to use.
     *
     * @throws SMIMEException If encryption fails.
     * @throws CMSException If a certificate is invalid.
     *
     * @return string DER-encoded ContentInfo.
     */
    public static function build(
        string $content,
        array $recipientCertificatesPem,
        CipherAlgorithm $cipher = CipherAlgorithm::Aes256Cbc,
    ): string {
        if ($recipientCertificatesPem === []) {
            throw SMIMEException::forEncryptionFailure();
        }

        $keySize = match ($cipher) {
            CipherAlgorithm::Aes128Cbc => 16,
            CipherAlgorithm::Aes192Cbc => 24,
            CipherAlgorithm::Aes256Cbc => 32,
        };
        $cipherOid = match ($cipher) {
            CipherAlgorithm::Aes128Cbc => OID::AES_128_CBC,
            CipherAlgorithm::Aes192Cbc => OID::AES_192_CBC,
            CipherAlgorithm::Aes256Cbc => OID::AES_256_CBC,
        };

        try {
            $cek = random_bytes($keySize);
            $iv = random_bytes(16);
        } catch (RandomException $e) {
            throw SMIMEException::forEncryptionFailure($e);
        }

        $encrypted = openssl_encrypt($content, $cipher->value, $cek, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw SMIMEException::forEncryptionFailure();
        }

        $recipientInfos = '';
        foreach ($recipientCertificatesPem as $certPem) {
            $recipientInfos .= self::buildKeyTransRecipientInfo($certPem, $cek);
        }

        $encryptionAlgorithm = DEREncoder::sequence(
            DEREncoder::objectIdentifier($cipherOid) . DEREncoder::octetString($iv),
        );

        $encryptedContentInfo = DEREncoder::sequence(
            DEREncoder::objectIdentifier(OID::DATA) . $encryptionAlgorithm
                . DEREncoder::contextTag(0, $encrypted, constructed: false),
        );

        $envelopedData = DEREncoder::sequence(
            DEREncoder::integer("\x00") . DEREncoder::set($recipientInfos) . $encryptedContentInfo,
        );

        return DEREncoder::sequence(
            DEREncoder::objectIdentifier(OID::ENVELOPED_DATA) . DEREncoder::contextTag(0, $envelopedData),
        );
    }

    /**
     * Build a KeyTransRecipientInfo structure per RFC 5652 section 6.2.1.
     *
     * Encrypts the content-encryption key (CEK) with the recipient's RSA public key
     * using PKCS#1 v1.5 padding.
     *
     * @param string $certPem The recipient's X.509 certificate in PEM format.
     * @param string $cek The plaintext content-encryption key to wrap.
     *
     * @throws CMSException If the certificate or public key is invalid.
     * @throws SMIMEException If the RSA encryption operation fails.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-6.2.1
     */
    private static function buildKeyTransRecipientInfo(string $certPem, string $cek): string
    {
        $pubKey = openssl_pkey_get_public($certPem);
        if (!$pubKey instanceof OpenSSLAsymmetricKey) {
            throw CMSException::forInvalidCertificate('failed to extract public key');
        }

        $certDer = DEREncoder::pemDecode($certPem);
        if ($certDer === '') {
            throw CMSException::forInvalidCertificate('failed to decode PEM certificate');
        }

        $issuerAndSerial = self::extractIssuerAndSerial($certDer);

        $wrappedKey = '';
        $result = openssl_public_encrypt($cek, $wrappedKey, $pubKey, OPENSSL_PKCS1_PADDING);
        if ($result === false) {
            throw SMIMEException::forEncryptionFailure();
        }

        $keyEncryptionAlgorithm = DEREncoder::sequence(
            DEREncoder::objectIdentifier(OID::RSA_ENCRYPTION) . DEREncoder::null(),
        );

        return DEREncoder::sequence(
            DEREncoder::integer("\x00") . $issuerAndSerial . $keyEncryptionAlgorithm
                . DEREncoder::octetString($wrappedKey),
        );
    }

    /**
     * Extract the IssuerAndSerialNumber from a DER-encoded X.509 certificate.
     *
     * @param string $certDer The complete DER-encoded X.509 certificate.
     *
     * @throws CMSException If the certificate structure cannot be parsed.
     *
     * @see SignedDataBuilder::extractIssuerAndSerial() Equivalent implementation in the signing builder.
     */
    private static function extractIssuerAndSerial(string $certDer): string
    {
        [$tag, $certContent] = DERDecoder::parse($certDer);
        if ($tag !== 0x30) {
            throw CMSException::forInvalidCertificate('not a SEQUENCE');
        }

        [$tag, $tbsCert] = DERDecoder::parse($certContent);
        if ($tag !== 0x30) {
            throw CMSException::forInvalidCertificate('TBSCertificate not a SEQUENCE');
        }

        $elements = DERDecoder::parseAll($tbsCert);

        $idx = 0;
        if ($elements[$idx][0] === 0xA0) {
            $idx++;
        }

        if (($idx + 3) >= count($elements)) {
            throw CMSException::forInvalidCertificate('TBSCertificate too short');
        }

        $serialTag = $elements[$idx][0];
        $serialContent = $elements[$idx][1];
        if ($serialTag !== 0x02) {
            throw CMSException::forInvalidCertificate('serial number not an INTEGER');
        }

        $serial = DEREncoder::tag(0x02, $serialContent);

        $idx += 2;
        $issuerTag = $elements[$idx][0];
        $issuerContent = $elements[$idx][1];
        if ($issuerTag !== 0x30) {
            throw CMSException::forInvalidCertificate('issuer not a SEQUENCE');
        }

        $issuer = DEREncoder::sequence($issuerContent);

        return DEREncoder::sequence($issuer . $serial);
    }
}
