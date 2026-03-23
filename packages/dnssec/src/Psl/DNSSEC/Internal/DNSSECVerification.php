<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Internal;

use Psl\DNS\Record\RecordInterface;
use Psl\DNS\Record\RecordType;

use function array_filter;
use function array_values;

/**
 * Shared DNSSEC verification utilities for filtering DNS records by kind.
 *
 * @internal
 */
final class DNSSECVerification
{
    /**
     * Filter records by kind.
     *
     * @param list<RecordInterface> $records
     *
     * @return list<RecordInterface>
     */
    public static function filterRecords(array $records, RecordType $kind): array
    {
        return array_values(array_filter($records, static fn(RecordInterface $r): bool => $r->kind === $kind));
    }
}
