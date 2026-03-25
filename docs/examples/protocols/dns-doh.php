<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Cache;
use Psl\DNS;
use Psl\HTTP\Client;

// Default client — creates one internally
$resolver = new DNS\HTTPSResolver('https://1.1.1.1/dns-query');

// Bring your own client for shared connection pooling
$client = new Client\Client();
$cloudflare = new DNS\HTTPSResolver('https://1.1.1.1/dns-query', $client);
$google = new DNS\HTTPSResolver('https://dns.google/dns-query', $client);

// Race two DoH providers
$racing = new DNS\RacingResolver([$cloudflare, $google]);

// Compose with caching
$cache = new Cache\LocalStore();
$cached = new DNS\CachedResolver($racing, $cache);

$response = $cached->query('example.com', DNS\Record\RecordType::A);
