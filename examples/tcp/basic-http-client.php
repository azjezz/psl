<?php

declare(strict_types=1);

use Psl\Async;
use Psl\IO;
use Psl\Str;
use Psl\TCP;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function(): void {
    [$headers, $content] = fetch('https://psalm.dev/');

    $output = IO\error_handle() ?? IO\output_handle();

    $output->writeAll($headers);
    $output->writeAll("\n");
    $output->writeAll($content);
});

function fetch(string $url): array
{
    $parsed_url = parse_url($url);
    $host = $parsed_url['host'];
    $port = $parsed_url['port'] ?? ($parsed_url['scheme'] === 'https' ? 443 : 80);
    $path = $parsed_url['path'] ?? '/';

    $options = TCP\ConnectOptions::create();
    if ($parsed_url['scheme'] === 'https') {
        $options = $options->withTLSConnectOptions(
            TCP\TLS\ConnectOptions::default()->withPeerName($host),
        );
    }

    $client = TCP\connect($host, $port, $options);
    $client->writeAll("GET $path HTTP/1.1\r\nHost: $host\r\nConnection: close\r\n\r\n");

    $response = $client->readAll(20142);

    var_dump($client->reachedEndOfDataSource());die();

    $position = Str\search($response, "\r\n\r\n");
    $headers = Str\slice($response, 0, $position);
    $content = Str\slice($response, $position + 4);

    return [$headers, $content];
}
