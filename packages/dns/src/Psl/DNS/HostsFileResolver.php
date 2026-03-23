<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DateTime\Duration;
use Psl\DNS\Record\AAAARecord;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\RecordInterface;
use Psl\DNS\Record\RecordType;
use Psl\DNS\System\HostsFile\HostsFile;
use Psl\IP\Family;

/**
 * Resolves hostnames from a parsed hosts file before delegating to DNS.
 *
 * Only intercepts A and AAAA queries. All other record types and reverse
 * lookups pass through to the inner resolver.
 *
 * When the hosts file contains matching entries, a synthetic {@see Response}
 * is returned without any network I/O.
 *
 * @api
 */
final readonly class HostsFileResolver implements ResolverInterface
{
    use ResolverConvenienceMethodsTrait;

    /**
     * @param ResolverInterface $inner The underlying resolver for non-matching queries.
     * @param HostsFile $hostsFile The parsed hosts file to consult for A and AAAA lookups.
     */
    public function __construct(
        private ResolverInterface $inner,
        private HostsFile $hostsFile,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function query(
        string $name,
        RecordType $type,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
        array $ednsOptions = [],
    ): Response {
        if ($type !== RecordType::A && $type !== RecordType::AAAA) {
            return $this->inner->query($name, $type, $cancellation, $ednsOptions);
        }

        $addresses = $this->hostsFile->lookup($name);
        if ($addresses === []) {
            return $this->inner->query($name, $type, $cancellation, $ednsOptions);
        }

        $targetFamily = $type === RecordType::A ? Family::V4 : Family::V6;

        /** @var list<RecordInterface> $records */
        $records = [];
        foreach ($addresses as $address) {
            if ($address->family !== $targetFamily) {
                continue;
            }

            $records[] = $type === RecordType::A
                ? new ARecord($name, Duration::hours(24), $address)
                : new AAAARecord($name, Duration::hours(24), $address);
        }

        if ($records === []) {
            return $this->inner->query($name, $type, $cancellation, $ednsOptions);
        }

        return new Response(0, ResponseCode::NoError, $records, [], []);
    }
}
