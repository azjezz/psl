<?php

declare(strict_types=1);

namespace Psl\Example\TCP;

use Psl\Async;
use Psl\IO;
use Psl\TCP;

require __DIR__ . '/../../vendor/autoload.php';

function fetch(string $host, string $path): string
{
    $client = TCP\connect($host, 80);
    $client->writeAll("GET {$path} HTTP/1.1\r\nHost: $host\r\nConnection: close\r\n\r\n");
    $response = $client->readAll();
    $client->close();

    return $response;
}

Async\main(static function (): int {
    $response = fetch('example.com', '/');

    IO\write_error_line($response);

    return 0;
});
