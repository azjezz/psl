<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

/**
 * Output encoding formats for serializing and deserializing CMS structures.
 *
 * Controls how the binary CMS data produced by {@see Signer}, {@see Encryptor}, and related
 * classes is encoded for transport, and how it is decoded by {@see Verifier} and {@see Decryptor}.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @api
 */
enum Encoding: string
{
    /**
     * S/MIME multipart format with MIME headers, suitable for email transport.
     */
    case SMIME = 'smime';

    /**
     * Raw binary DER (Distinguished Encoding Rules) encoding.
     */
    case DER = 'der';

    /**
     * Base64-encoded PEM (Privacy-Enhanced Mail) encoding with header/footer lines.
     */
    case PEM = 'pem';
}
