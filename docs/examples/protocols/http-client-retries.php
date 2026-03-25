<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime\Duration;
use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\URL;

$client = new Client\RetryClient(
    new Client\RedirectClient(new Client\Client()),
    maxAttempts: 3,
    backoff: Duration::milliseconds(200),
    backoffMultiplier: 2,
);

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://example.com/api/data'));

$tx = $client->send($request);

$tx->response->status;
