<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS\Record\RecordType;

use function str_ends_with;
use function substr_count;

/**
 * Appends search domains to short query names before resolving.
 *
 * When the query name contains fewer dots than the $numberOfDots threshold
 * (default 1), each search domain is appended and tried in order.
 * The first non-NXDOMAIN response is returned. If all expanded names
 * fail, the original name is tried as a last resort.
 *
 * Names that meet or exceed the $numberOfDots threshold are queried as-is.
 *
 * @api
 */
final readonly class SearchDomainResolver implements ResolverInterface
{
    use ResolverConvenienceMethodsTrait;

    /**
     * @param ResolverInterface $inner The underlying resolver to delegate expanded queries to.
     * @param list<string> $searchDomains Search domains to append.
     * @param int<1, max> $numberOfDots Minimum dots in a name to skip search domain expansion.
     */
    public function __construct(
        private ResolverInterface $inner,
        private array $searchDomains,
        private int $numberOfDots = 1,
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
        if ($this->searchDomains === [] || $this->isFullyQualified($name)) {
            return $this->inner->query($name, $type, $cancellation, $ednsOptions);
        }

        foreach ($this->searchDomains as $domain) {
            $expanded = $name . '.' . $domain;
            $response = $this->inner->query($expanded, $type, $cancellation, $ednsOptions);

            if ($response->code !== ResponseCode::NonExistentDomain) {
                return $response;
            }
        }

        return $this->inner->query($name, $type, $cancellation, $ednsOptions);
    }

    /**
     * Determine whether the name is fully qualified (trailing dot or enough dots).
     */
    private function isFullyQualified(string $name): bool
    {
        if (str_ends_with($name, '.')) {
            return true;
        }

        return substr_count($name, '.') >= $this->numberOfDots;
    }
}
