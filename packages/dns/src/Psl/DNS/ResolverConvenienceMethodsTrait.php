<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS\Record\RecordType;
use Psl\IP\Address;

/**
 * Provides a default implementation of reverseQuery() that constructs the
 * appropriate .arpa domain name and delegates to query().
 *
 * @api
 *
 * @require-implements ResolverInterface
 */
trait ResolverConvenienceMethodsTrait
{
    /**
     * {@inheritDoc}
     */
    public function reverseQuery(
        Address $ip,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
        array $ednsOptions = [],
    ): Response {
        return $this->query($ip->toArpaName(), RecordType::PTR, $cancellation, $ednsOptions);
    }
}
