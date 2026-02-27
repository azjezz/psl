<?php

declare(strict_types=1);

namespace Psl\TLS\Internal;

use OpenSSLCertificate;
use Psl\DateTime\Timestamp;
use Psl\TLS;

use function openssl_x509_fingerprint;
use function openssl_x509_parse;

/**
 * Convert an OpenSSLCertificate into a PeerCertificate value object.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function parse_peer_certificate(OpenSSLCertificate $certificate): TLS\PeerCertificate
{
    /** @var array{subject: array{CN?: string, ...}, issuer: array{CN?: string, ...}, serialNumberHex?: string, validFrom_time_t: int, validTo_time_t: int, ...} $parsed */
    $parsed = openssl_x509_parse($certificate);

    $subject = $parsed['subject']['CN'] ?? '';
    $issuer = $parsed['issuer']['CN'] ?? '';
    $serialNumber = $parsed['serialNumberHex'] ?? '';
    $validFrom = Timestamp::fromParts((int) $parsed['validFrom_time_t']);
    $validTo = Timestamp::fromParts((int) $parsed['validTo_time_t']);
    $fingerprint = openssl_x509_fingerprint($certificate, 'sha256');
    if ($fingerprint === false) {
        $fingerprint = '';
    }

    return new TLS\PeerCertificate($subject, $issuer, $serialNumber, $validFrom, $validTo, $fingerprint);
}
