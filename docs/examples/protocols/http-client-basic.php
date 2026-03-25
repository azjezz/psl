<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\URL;

$client = new Client\Client();

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://example.com'));

$tx = $client->send($request);

$tx->response->status;
$tx->response->protocolVersion;
$tx->response->headers->get('content-type');
$tx->response->body?->readAll();
