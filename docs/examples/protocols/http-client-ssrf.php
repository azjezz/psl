<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\URL;

$client = new Client\Client(middleware: [Client\Middleware\DeniedDestinationsMiddleware::forPrivateNetworkRanges()]);

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://example.com'));
$tx = $client->send($request);

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('http://169.254.169.254/metadata'));
$tx = $client->send($request);
