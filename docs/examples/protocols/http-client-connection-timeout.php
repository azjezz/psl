<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\URL;

$client = new Client\Client();

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://example.com'));

// Limit the connection phase (TCP + TLS handshake) to 5 seconds.
// The overall request cancellation token still applies independently.
try {
    $tx = $client->send($request, new Client\SendConfiguration(connectionTimeout: Duration::seconds(5)));

    $tx->response->status; // 200
} catch (Async\Exception\CancelledException) {
    // @mago-expect lint:no-empty-catch-clause - Connection took longer than 5 seconds
}
