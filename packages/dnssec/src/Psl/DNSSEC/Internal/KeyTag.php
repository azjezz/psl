<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Internal;

use Psl\DNS\Record\DNSKEYRecord;

use function ord;
use function pack;
use function strlen;

/**
 * Computes a DNSKEY key tag per RFC 4034 Appendix B.
 *
 * The key tag is a non-cryptographic checksum used to efficiently match
 * RRSIG records to DNSKEY records without performing full signature verification.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc4034#appendix-B
 *
 * @internal
 */
final class KeyTag
{
    /**
     * Compute the key tag for a DNSKEY record.
     *
     * The algorithm sums all 16-bit words of the DNSKEY RDATA (flags, protocol,
     * algorithm, and public key), handling odd-length data by treating the last
     * byte as the high byte of a 16-bit word.
     *
     * @return int The computed 16-bit key tag value.
     */
    public static function compute(DNSKEYRecord $dnskey): int
    {
        $rdata = pack('nCC', $dnskey->flags, $dnskey->protocol, $dnskey->algorithm->value) . $dnskey->publicKey;

        $length = strlen($rdata);
        $ac = 0;

        for ($i = 0; $i < $length; $i++) {
            if ($i & 1) {
                $ac += ord($rdata[$i]);
            } else {
                $ac += ord($rdata[$i]) << 8;
            }
        }

        $ac += ($ac >> 16) & 0xFFFF;

        return $ac & 0xFFFF;
    }
}
