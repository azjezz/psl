<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Internal\DS;

use Psl\DNS\DNSSEC\DigestAlgorithm;
use Psl\DNS\Exception\NameEncodingException;
use Psl\DNS\Internal\Encoder;
use Psl\DNS\Record\DNSKEYRecord;
use Psl\DNS\Record\DSRecord;
use Psl\Hash;
use Psl\Hash\Exception\RuntimeException;

use function pack;
use function strtolower;

/**
 * Verifies that a DS record's digest matches a DNSKEY record.
 *
 * The digest is computed over the wire-format owner name concatenated with
 * the DNSKEY RDATA (flags + protocol + algorithm + public key).
 *
 * @see https://datatracker.ietf.org/doc/html/rfc4034#section-5
 *
 * @internal
 */
final class DSVerifier
{
    /**
     * Verify that a DS record matches the given DNSKEY record.
     *
     * @param string $ownerName The domain name that owns the DNSKEY record.
     *
     * @throws NameEncodingException If the owner name cannot be encoded to wire format.
     * @throws RuntimeException If the hash computation fails.
     */
    public static function verify(DSRecord $ds, DNSKEYRecord $dnskey, string $ownerName): bool
    {
        $hashAlgorithm = match ($ds->digestType) {
            DigestAlgorithm::SHA1 => Hash\Algorithm::Sha1,
            DigestAlgorithm::SHA256 => Hash\Algorithm::Sha256,
            DigestAlgorithm::SHA384 => Hash\Algorithm::Sha384,
        };

        $ownerWire = Encoder::encodeName(strtolower($ownerName));
        $dnskeyRdata = pack('nCC', $dnskey->flags, $dnskey->protocol, $dnskey->algorithm->value) . $dnskey->publicKey;

        $digest = Hash\hash($ownerWire . $dnskeyRdata, $hashAlgorithm);

        return Hash\equals(strtolower($digest), strtolower($ds->digest));
    }
}
