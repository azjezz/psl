<?php

declare(strict_types=1);

namespace Psl\MIME\Internal\CMS;

use OpenSSLAsymmetricKey;
use Psl\MIME\Exception\CMSException;
use Psl\MIME\Exception\SMIMEException;

use function count;
use function gmdate;
use function openssl_digest;
use function openssl_pkey_get_private;
use function openssl_sign;

/**
 * Builds CMS SignedData structures per RFC 5652 section 5.
 *
 * Produces a DER-encoded ContentInfo wrapping a SignedData value with a single
 * SignerInfo using SHA-256 digest and SHA-256-with-RSA signature.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652#section-5
 *
 * @internal
 */
final class SignedDataBuilder
{
    /**
     * Build a CMS SignedData ContentInfo.
     *
     * @param string $content The content to sign.
     * @param string $certificatePem Signing certificate in PEM format.
     * @param string $privateKeyPem Private key in PEM format.
     * @param null|string $passphrase Optional passphrase for the private key.
     * @param list<string> $extraCertificatesPem Additional certificates to include.
     *
     * @throws SMIMEException If signing fails.
     * @throws CMSException If certificate or key is invalid.
     *
     * @return string DER-encoded ContentInfo.
     */
    public static function build(
        string $content,
        string $certificatePem,
        string $privateKeyPem,
        null|string $passphrase = null,
        array $extraCertificatesPem = [],
    ): string {
        $certDer = DEREncoder::pemDecode($certificatePem);
        if ($certDer === '') {
            throw CMSException::forInvalidCertificate('failed to decode PEM certificate');
        }

        $key = openssl_pkey_get_private($privateKeyPem, $passphrase ?? '');
        if (!$key instanceof OpenSSLAsymmetricKey) {
            throw CMSException::forInvalidKey('failed to load private key');
        }

        $issuerAndSerial = self::extractIssuerAndSerial($certDer);

        $digest = openssl_digest($content, 'sha256', true);
        if ($digest === false) {
            throw SMIMEException::forSigningFailure();
        }

        $signedAttrs = self::buildSignedAttributes($digest);
        $signedAttrsDer = DEREncoder::set($signedAttrs);

        $signature = '';
        $result = openssl_sign($signedAttrsDer, $signature, $key, OPENSSL_ALGO_SHA256);
        if ($result === false || $signature === '') {
            throw SMIMEException::forSigningFailure();
        }

        $digestAlgorithm = DEREncoder::sequence(DEREncoder::objectIdentifier(OID::SHA256) . DEREncoder::null());

        $digestAlgorithms = DEREncoder::set($digestAlgorithm);

        $encapsulatedContent = DEREncoder::sequence(
            DEREncoder::objectIdentifier(OID::DATA) . DEREncoder::contextTag(0, DEREncoder::octetString($content)),
        );

        $signerInfo = self::buildSignerInfo($issuerAndSerial, $signedAttrs, $signature);

        $allCertsDer = $certDer;
        foreach ($extraCertificatesPem as $extraPem) {
            $extraDer = DEREncoder::pemDecode($extraPem);
            if ($extraDer !== '') {
                $allCertsDer .= $extraDer;
            }
        }

        $certificates = DEREncoder::contextTag(0, $allCertsDer);

        $signedData = DEREncoder::sequence(
            DEREncoder::integer("\x01") . $digestAlgorithms . $encapsulatedContent . $certificates
                . DEREncoder::set($signerInfo),
        );

        return DEREncoder::sequence(
            DEREncoder::objectIdentifier(OID::SIGNED_DATA) . DEREncoder::contextTag(0, $signedData),
        );
    }

    /**
     * Build the DER-encoded signed attributes: contentType, signingTime, and messageDigest.
     *
     * These attributes are required per RFC 5652 section 5.3 when signed attributes are present.
     *
     * @param string $digest The raw SHA-256 digest of the content being signed.
     *
     * @throws SMIMEException If the current time cannot be formatted.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-5.3
     */
    private static function buildSignedAttributes(string $digest): string
    {
        $contentTypeAttr = DEREncoder::sequence(
            DEREncoder::objectIdentifier(OID::CONTENT_TYPE) . DEREncoder::set(DEREncoder::objectIdentifier(OID::DATA)),
        );

        $signingTime = gmdate('ymdHis') . 'Z';
        $signingTimeAttr = DEREncoder::sequence(
            DEREncoder::objectIdentifier(OID::SIGNING_TIME) . DEREncoder::set(DEREncoder::utcTime($signingTime)),
        );

        $messageDigestAttr = DEREncoder::sequence(
            DEREncoder::objectIdentifier(OID::MESSAGE_DIGEST) . DEREncoder::set(DEREncoder::octetString($digest)),
        );

        return $contentTypeAttr . $signingTimeAttr . $messageDigestAttr;
    }

    /**
     * Build a single SignerInfo structure per RFC 5652 section 5.3.
     *
     * Uses version 1 (IssuerAndSerialNumber), SHA-256 digest, and SHA-256-with-RSA signature.
     *
     * @param string $issuerAndSerial DER-encoded IssuerAndSerialNumber identifying the signer.
     * @param string $signedAttrs The raw signed attributes content (without the SET wrapper).
     * @param string $signature The raw RSA signature bytes.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-5.3
     */
    private static function buildSignerInfo(string $issuerAndSerial, string $signedAttrs, string $signature): string
    {
        $digestAlgorithm = DEREncoder::sequence(DEREncoder::objectIdentifier(OID::SHA256) . DEREncoder::null());

        $signatureAlgorithm = DEREncoder::sequence(
            DEREncoder::objectIdentifier(OID::SHA256_WITH_RSA) . DEREncoder::null(),
        );

        return DEREncoder::sequence(
            DEREncoder::integer("\x01")
                . $issuerAndSerial
                . $digestAlgorithm
                . DEREncoder::contextTag(0, $signedAttrs, constructed: true)
                . $signatureAlgorithm
                . DEREncoder::octetString($signature),
        );
    }

    /**
     * Extract the IssuerAndSerialNumber from a DER-encoded X.509 certificate.
     *
     * Navigates the TBSCertificate structure to locate the serialNumber and issuer
     * fields, then encodes them as a SEQUENCE per RFC 5652 section 10.2.4.
     *
     * @param string $certDer The complete DER-encoded X.509 certificate.
     *
     * @throws CMSException If the certificate structure cannot be parsed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-10.2.4
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
