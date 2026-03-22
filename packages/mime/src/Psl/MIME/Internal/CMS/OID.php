<?php

declare(strict_types=1);

namespace Psl\MIME\Internal\CMS;

/**
 * Pre-encoded ASN.1 OID byte values used in CMS (Cryptographic Message Syntax) structures.
 *
 * Each constant contains the raw BER/DER-encoded OID value bytes (without the 0x06 tag
 * and length prefix). These are passed directly to {@see DEREncoder::objectIdentifier()}.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652
 *
 * @internal
 */
final class OID
{
    /**
     * id-data (1.2.840.113549.1.7.1) - Plain data content type.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-4
     */
    public const string DATA = "\x2a\x86\x48\x86\xf7\x0d\x01\x07\x01";

    /**
     * id-signedData (1.2.840.113549.1.7.2) - Signed-data content type.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-5
     */
    public const string SIGNED_DATA = "\x2a\x86\x48\x86\xf7\x0d\x01\x07\x02";

    /**
     * id-envelopedData (1.2.840.113549.1.7.3) - Enveloped-data content type.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-6
     */
    public const string ENVELOPED_DATA = "\x2a\x86\x48\x86\xf7\x0d\x01\x07\x03";

    /**
     * id-sha1 (1.3.14.3.2.26) - SHA-1 message digest algorithm.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3370#section-2.1
     */
    public const string SHA1 = "\x2b\x0e\x03\x02\x1a";

    /**
     * id-sha256 (2.16.840.1.101.3.4.2.1) - SHA-256 message digest algorithm.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5754#section-2
     */
    public const string SHA256 = "\x60\x86\x48\x01\x65\x03\x04\x02\x01";

    /**
     * id-sha384 (2.16.840.1.101.3.4.2.2) - SHA-384 message digest algorithm.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5754#section-2
     */
    public const string SHA384 = "\x60\x86\x48\x01\x65\x03\x04\x02\x02";

    /**
     * id-sha512 (2.16.840.1.101.3.4.2.3) - SHA-512 message digest algorithm.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5754#section-2
     */
    public const string SHA512 = "\x60\x86\x48\x01\x65\x03\x04\x02\x03";

    /**
     * rsaEncryption (1.2.840.113549.1.1.1) - RSA encryption (key transport).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3370#section-4.1
     */
    public const string RSA_ENCRYPTION = "\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01";

    /**
     * sha256WithRSAEncryption (1.2.840.113549.1.1.11) - SHA-256 with RSA signature.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc4055#section-5
     */
    public const string SHA256_WITH_RSA = "\x2a\x86\x48\x86\xf7\x0d\x01\x01\x0b";

    /**
     * id-aes128-CBC (2.16.840.1.101.3.4.1.2) - AES-128 in CBC mode.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3565#section-4.1
     */
    public const string AES_128_CBC = "\x60\x86\x48\x01\x65\x03\x04\x01\x02";

    /**
     * id-aes192-CBC (2.16.840.1.101.3.4.1.22) - AES-192 in CBC mode.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3565#section-4.1
     */
    public const string AES_192_CBC = "\x60\x86\x48\x01\x65\x03\x04\x01\x16";

    /**
     * id-aes256-CBC (2.16.840.1.101.3.4.1.42) - AES-256 in CBC mode.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3565#section-4.1
     */
    public const string AES_256_CBC = "\x60\x86\x48\x01\x65\x03\x04\x01\x2a";

    /**
     * id-contentType (1.2.840.113549.1.9.3) - Content type signed attribute.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-11.1
     */
    public const string CONTENT_TYPE = "\x2a\x86\x48\x86\xf7\x0d\x01\x09\x03";

    /**
     * id-messageDigest (1.2.840.113549.1.9.4) - Message digest signed attribute.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-11.2
     */
    public const string MESSAGE_DIGEST = "\x2a\x86\x48\x86\xf7\x0d\x01\x09\x04";

    /**
     * id-signingTime (1.2.840.113549.1.9.5) - Signing time signed attribute.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5652#section-11.3
     */
    public const string SIGNING_TIME = "\x2a\x86\x48\x86\xf7\x0d\x01\x09\x05";
}
