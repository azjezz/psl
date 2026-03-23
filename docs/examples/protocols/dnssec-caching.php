<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Cache;
use Psl\DNS;
use Psl\DNS\Record\RecordType;
use Psl\DNSSEC;

$cache = new Cache\LocalStore();

// The inner resolver must request DNSSEC records
$dnsResolver = new DNS\SystemResolver(dnssec: true);

// Cache trust chain results to avoid repeated root-to-leaf walks
// Without caching, every query triggers DS/DNSKEY lookups for each zone
$trustChain = new DNSSEC\CachedTrustChainResolver(new DNSSEC\TrustChainResolver($dnsResolver), $cache);

// SecureResolver validates every response using the cached trust chain
$secureResolver = new DNSSEC\SecureResolver($dnsResolver, $trustChain);

// Cache the validated DNS responses on top
$resolver = new DNS\CachedResolver($secureResolver, $cache);

// First query: full trust chain walk (root -> com. -> example.com.)
$resolver->query('example.com', RecordType::A);

// Second query for same TLD: trust chain for "com." is cached
$resolver->query('another.com', RecordType::A);

// Third query for same domain: entire response is cached
$resolver->query('example.com', RecordType::A);
