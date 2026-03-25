<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\Socks;
use Psl\URL;

$configuration = new Client\ClientConfiguration(proxy: new Socks\Configuration(
    proxyHost: 'proxy.example.com',
    proxyPort: 1080,
    username: 'user',
    password: 'secret', // @mago-expect lint:no-literal-password
));

$client = new Client\Client(configuration: $configuration);

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://example.com'));

$tx = $client->send($request);

$tx->response->status;
