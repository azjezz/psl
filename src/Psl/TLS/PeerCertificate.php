<?php

declare(strict_types=1);

namespace Psl\TLS;

use Psl\DateTime;

/**
 * Immutable representation of a peer's X.509 certificate.
 *
 * Wraps parsed certificate data without exposing the underlying OpenSSL extension types.
 *
 * @psalm-immutable
 */
final readonly class PeerCertificate
{
    /**
     * @internal
     */
    public function __construct(
        public string $subject,
        public string $issuer,
        public string $serialNumber,
        public DateTime\Timestamp $validFrom,
        public DateTime\Timestamp $validTo,
        public string $fingerprint,
    ) {}
}
