<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Env;
use Psl\Filesystem;
use Psl\Unix;

$path = Filesystem\create_temporary_file(Env\temp_dir(), 'psl-unix-concurrent-');

$listener = Unix\listen($path);

Async\concurrently([
    'server' => static function () use ($listener): void {
        // Accept multiple clients concurrently
        for ($i = 0; $i < 3; $i++) {
            $connection = $listener->accept();
            Async\run(static function () use ($connection): void {
                $request = $connection->readAll();
                $connection->writeAll("handled: {$request}");
                $connection->close();
            })->ignore();
        }

        $listener->close();
    },
    'clients' => static function () use ($path): void {
        // Spawn 3 clients concurrently
        Async\concurrently([
            static function () use ($path): void {
                $client = Unix\connect($path);
                $client->writeAll('request-1');
                $client->shutdown();
                $client->readAll();
                $client->close();
            },
            static function () use ($path): void {
                $client = Unix\connect($path);
                $client->writeAll('request-2');
                $client->shutdown();
                $client->readAll();
                $client->close();
            },
            static function () use ($path): void {
                $client = Unix\connect($path);
                $client->writeAll('request-3');
                $client->shutdown();
                $client->readAll();
                $client->close();
            },
        ]);
    },
]);

@unlink($path);
