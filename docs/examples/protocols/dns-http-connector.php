<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Cache;
use Psl\DNS;
use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\URL;

// Build a resolver chain: cached async UDP resolution with TCP fallback on truncation.
$resolver = new DNS\CachedResolver(new DNS\FallbackResolver([
    new DNS\UDPResolver(host: '1.1.1.1'),
    new DNS\TCPResolver(host: '1.1.1.1'),
]), new Cache\LocalStore());

// Wrap the pooled connector with DNS resolution.
// All HTTP requests will resolve hostnames through the resolver above,
// benefiting from caching, async I/O, and custom nameserver selection.
$connector = new DNS\HTTP\Connector(new Client\Connection\PooledConnector(), $resolver);

$client = new Client\Client(connector: $connector);

$tx = $client->send(new Message\Request(method: 'GET', url: URL\parse('https://example.com')));

$tx->response->status;
$tx->response->body?->readAll();
