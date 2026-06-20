<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Env;
use Psl\Filesystem;
use Psl\Unix;

$path = Filesystem\create_temporary_file(Env\temp_dir(), 'psl-unix-server-');

$listener = Unix\listen($path);

Async\concurrently::<string, void>([
    'server' => static function () use ($listener, $path): void {
        echo "Listening on {$path}\n";

        // Accept one connection then shut down
        $connection = $listener->accept();
        Async\run::<void>(static function () use ($connection): void {
            $data = $connection->readAll();
            $connection->writeAll($data);
            $connection->close();
        })->await();

        $listener->close();
    },
    'client' => static function () use ($path): void {
        $client = Unix\connect($path);
        $client->writeAll('hello from client');
        $client->shutdown();
        $response = $client->readAll();
        echo "Got: {$response}\n";
        $client->close();
    },
]);

@unlink($path);
