<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\DNS\EDNS\OptionInterface;
use Psl\DNS\Record\RecordType;
use Psl\IP\Address;

/**
 * Contract for DNS resolvers capable of querying nameservers and performing reverse lookups.
 *
 * @api
 */
interface ResolverInterface
{
    /**
     * Query a DNS nameserver for records of a given kind.
     *
     * @param string                     $name         The domain name to query.
     * @param RecordType                 $type         The type of DNS record to request.
     * @param CancellationTokenInterface $cancellation Token to cancel the query.
     * @param list<OptionInterface>      $ednsOptions  EDNS0 options to include in the query.
     *
     * @throws CancelledException If the cancellation token is cancelled before the operation is finished.
     * @throws Exception\RuntimeException If the query fails due to a transport error.
     * @throws Exception\InvalidArgumentException If the query name or options are invalid.
     * @throws Exception\ProtocolException If the response is malformed or violates the DNS protocol.
     */
    public function query(
        string $name,
        RecordType $type,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
        array $ednsOptions = [],
    ): Response;

    /**
     * Perform a reverse DNS lookup for an IP address.
     *
     * Converts the address to its .arpa domain name and queries for PTR records.
     *
     * @param Address $ip The IP address to look up.
     * @param CancellationTokenInterface $cancellation Token to cancel the query.
     * @param list<EDNS\OptionInterface> $ednsOptions EDNS0 options to include in the query.
     *
     * @throws Exception\RuntimeException If the query fails due to a transport error.
     * @throws Exception\InvalidArgumentException If the query name or options are invalid.
     * @throws Exception\ProtocolException If the response is malformed or violates the DNS protocol.
     */
    public function reverseQuery(
        Address $ip,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
        array $ednsOptions = [],
    ): Response;
}
