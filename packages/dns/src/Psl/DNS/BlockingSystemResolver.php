<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DateTime\Duration;
use Psl\DNS\Record\AAAARecord;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\CAARecord;
use Psl\DNS\Record\CNAMERecord;
use Psl\DNS\Record\MXRecord;
use Psl\DNS\Record\NAPTRRecord;
use Psl\DNS\Record\NSRecord;
use Psl\DNS\Record\PTRRecord;
use Psl\DNS\Record\RecordInterface;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\SOARecord;
use Psl\DNS\Record\SRVRecord;
use Psl\DNS\Record\TXTRecord;
use Psl\IP\Address;

use function dns_get_record;
use function strtolower;

use const DNS_A;
use const DNS_AAAA;
use const DNS_CAA;
use const DNS_CNAME;
use const DNS_MX;
use const DNS_NAPTR;
use const DNS_NS;
use const DNS_PTR;
use const DNS_SOA;
use const DNS_SRV;
use const DNS_TXT;

/**
 * DNS resolver that delegates to PHP's built-in `dns_get_record()` function.
 *
 * This resolver performs synchronous, blocking DNS lookups using the operating
 * system's native resolver. It has zero overhead beyond the DNS query itself:
 * no event loop involvement, no fiber suspension, no connection pooling.
 *
 * ## When to use
 *
 * - As a lightweight fallback resolver behind a {@see StaticResolver} in a
 *   {@see FallbackResolver} chain.
 * - In environments where async DNS resolution is unnecessary or undesirable.
 * - As the default resolver for applications that do not need DNS-over-TLS,
 *   DNS-over-HTTPS, or EDNS0 features.
 *
 * ## Limitations
 *
 * - Blocks the calling fiber for the duration of the DNS query. This is
 *   acceptable for most use cases since OS-level DNS caching (nscd, systemd-resolved,
 *   mDNSResponder) keeps resolution times low.
 * - EDNS0 options are ignored. PHP's `dns_get_record()` does not support EDNS0.
 * - Only a subset of record types is supported: A, AAAA, CNAME, MX, NS, PTR,
 *   SOA, SRV, TXT, CAA, and NAPTR. Queries for unsupported types return an
 *   NXDOMAIN response.
 * - The cancellation token is checked before the query but cannot interrupt a
 *   query in progress, as `dns_get_record()` is not cancellable.
 *
 * @link https://www.php.net/manual/en/function.dns-get-record.php
 *
 * @codeCoverageIgnore
 *
 * @api
 */
final class BlockingSystemResolver implements ResolverInterface
{
    use ResolverConvenienceMethodsTrait;

    /**
     * {@inheritDoc}
     *
     * Delegates to PHP's `dns_get_record()` and converts the result into a
     * {@see Response} with typed record objects. EDNS0 options are ignored.
     *
     * The cancellation token is checked before the query starts but cannot
     * interrupt a query already in progress.
     */
    public function query(
        string $name,
        RecordType $type,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
        array $ednsOptions = [],
    ): Response {
        $cancellation->throwIfCancelled();

        $phpType = self::toPHPType($type);
        if ($phpType === null) {
            return new Response(0, ResponseCode::NonExistentDomain, [], [], []);
        }

        $rawAuthority = [];
        $rawAdditional = [];
        /** @var false|list<array{host?: string, type?: string, ttl?: int, ip: non-empty-string, ipv6: non-empty-string, ...<string, mixed>}> $rawRecords */
        $rawRecords = @dns_get_record($name, $phpType, $rawAuthority, $rawAdditional);
        if ($rawRecords === false || $rawRecords === []) {
            return new Response(0, ResponseCode::NonExistentDomain, [], [], []);
        }

        /** @var list<RecordInterface> $answers */
        $answers = [];
        foreach ($rawRecords as $raw) {
            $record = self::convertRecord($raw);
            if ($record !== null) {
                $answers[] = $record;
            }
        }

        /** @var list<RecordInterface> $authority */
        $authority = [];
        /** @var array{host?: string, type?: string, ttl?: int, ip: non-empty-string, ipv6: non-empty-string, ...<string, mixed>} $raw */
        foreach ($rawAuthority as $raw) {
            $record = self::convertRecord($raw);
            if ($record !== null) {
                $authority[] = $record;
            }
        }

        /** @var list<RecordInterface> $additional */
        $additional = [];
        /** @var array{host?: string, type?: string, ttl?: int, ip: non-empty-string, ipv6: non-empty-string, ...<string, mixed>} $raw */
        foreach ($rawAdditional as $raw) {
            $record = self::convertRecord($raw);
            if ($record !== null) {
                $additional[] = $record;
            }
        }

        return new Response(
            0,
            $answers === [] ? ResponseCode::NonExistentDomain : ResponseCode::NoError,
            $answers,
            $authority,
            $additional,
        );
    }

    /**
     * Map a PSL RecordType to a PHP DNS_* constant.
     *
     * @return null|int Null if the record type is not supported by dns_get_record().
     */
    private static function toPHPType(RecordType $type): null|int
    {
        return match ($type) {
            RecordType::A => DNS_A,
            RecordType::AAAA => DNS_AAAA,
            RecordType::CNAME => DNS_CNAME,
            RecordType::MX => DNS_MX,
            RecordType::NS => DNS_NS,
            RecordType::PTR => DNS_PTR,
            RecordType::SOA => DNS_SOA,
            RecordType::SRV => DNS_SRV,
            RecordType::TXT => DNS_TXT,
            RecordType::CAA => DNS_CAA,
            RecordType::NAPTR => DNS_NAPTR,
            default => null,
        };
    }

    /**
     * Convert a raw dns_get_record() array entry to a typed record object.
     *
     * Detects the record type from the 'type' field in the raw array, which
     * allows this method to handle answer, authority, and additional section
     * records uniformly.
     *
     * @param array{host?: string, type?: string, ttl?: int, ip: non-empty-string, ipv6: non-empty-string, ...<string, mixed>} $raw A single entry from dns_get_record().
     *
     * @return null|RecordInterface Null if the record type is unsupported.
     */
    private static function convertRecord(array $raw): null|RecordInterface
    {
        $name = strtolower($raw['host'] ?? '');
        $ttl = Duration::seconds($raw['ttl'] ?? 0);
        $type = self::detectRecordType($raw['type'] ?? '');
        if ($type === null) {
            return null;
        }

        return match ($type) {
            RecordType::A => new ARecord($name, $ttl, Address::parse($raw['ip'])),
            RecordType::AAAA => new AAAARecord($name, $ttl, Address::parse($raw['ipv6'])),
            RecordType::CNAME => new CNAMERecord($name, $ttl, (string) $raw['target']),
            RecordType::MX => new MXRecord($name, $ttl, (int) ($raw['pri'] ?? 0), (string) ($raw['target'] ?? '')),
            RecordType::NS => new NSRecord($name, $ttl, (string) ($raw['target'] ?? '')),
            RecordType::PTR => new PTRRecord($name, $ttl, (string) $raw['target']),
            RecordType::SOA => new SOARecord(
                $name,
                $ttl,
                (string) ($raw['mname'] ?? ''),
                (string) ($raw['rname'] ?? ''),
                (int) ($raw['serial'] ?? 0),
                Duration::seconds((int) ($raw['refresh'] ?? 0)),
                Duration::seconds((int) ($raw['retry'] ?? 0)),
                Duration::seconds((int) ($raw['expire'] ?? 0)),
                Duration::seconds((int) ($raw['minimum-ttl'] ?? 0)),
            ),
            RecordType::SRV => new SRVRecord(
                $name,
                $ttl,
                (int) ($raw['pri'] ?? 0),
                (int) ($raw['weight'] ?? 0),
                (int) ($raw['port'] ?? 0),
                (string) ($raw['target'] ?? ''),
            ),
            RecordType::TXT => new TXTRecord($name, $ttl, [(string) ($raw['txt'] ?? '')]),
            RecordType::CAA => new CAARecord(
                $name,
                $ttl,
                (int) ($raw['flags'] ?? 0),
                (string) ($raw['tag'] ?? ''),
                (string) ($raw['value'] ?? ''),
            ),
            RecordType::NAPTR => new NAPTRRecord(
                $name,
                $ttl,
                (int) ($raw['order'] ?? 0),
                (int) ($raw['pref'] ?? 0),
                (string) ($raw['flags'] ?? ''),
                (string) ($raw['services'] ?? ''),
                (string) ($raw['regex'] ?? ''),
                (string) ($raw['replacement'] ?? ''),
            ),
            default => null,
        };
    }

    /**
     * Detect the PSL RecordType from the 'type' string in dns_get_record() output.
     *
     * @return null|RecordType Null if the type string is not recognized.
     */
    private static function detectRecordType(string $typeString): null|RecordType
    {
        return match ($typeString) {
            'A' => RecordType::A,
            'AAAA' => RecordType::AAAA,
            'CNAME' => RecordType::CNAME,
            'MX' => RecordType::MX,
            'NS' => RecordType::NS,
            'PTR' => RecordType::PTR,
            'SOA' => RecordType::SOA,
            'SRV' => RecordType::SRV,
            'TXT' => RecordType::TXT,
            'CAA' => RecordType::CAA,
            'NAPTR' => RecordType::NAPTR,
            default => null,
        };
    }
}
