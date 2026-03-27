<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Client;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Message;
use Psl\URL;

$client = new Client\Client();

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://example.com'));

$tx = $client->send(
    $request,
    new Client\SendConfiguration(
        onConnection: static function (ConnectionMetadata $metadata): void {
            // Peer address is the resolved IP after DNS resolution
            $metadata->peerAddress->host; // e.g. "93.184.216.34"
            $metadata->peerAddress->port; // e.g. 443

            // Local address is the ephemeral socket on this machine
            $metadata->localAddress->host; // e.g. "192.168.1.100"
            $metadata->localAddress->port; // e.g. 52431

            // TLS state is available for HTTPS connections
            if ($metadata->tlsState !== null) {
                $metadata->tlsState->version; // e.g. TLS\Version::Tls13
                $metadata->tlsState->cipherName; // e.g. "TLS_AES_256_GCM_SHA384"
                $metadata->tlsState->alpnProtocol; // e.g. "h2"
                $metadata->tlsState->peerCertificate; // server certificate
            }
        },
        onInformationalResponse: static function (Message\Response $response): void {
            // Fires for each 1xx response (100 Continue, 103 Early Hints, etc.)
            $response->status; // e.g. 103
            $response->headers->getAll('link'); // e.g. ["</style.css>; rel=preload; as=style"]
        },
    ),
);

$tx->response->status;
