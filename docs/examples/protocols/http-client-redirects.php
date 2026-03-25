<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\URL;

$client = new Client\RedirectClient(new Client\Client(), maxRedirects: 5, autoReferrer: true);

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://httpbin.org/redirect/3'));

$tx = $client->send($request);

$tx->response->status;
