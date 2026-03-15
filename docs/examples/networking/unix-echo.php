<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Env;
use Psl\Filesystem;
use Psl\Unix;

$path = Filesystem\create_temporary_file(Env\temp_dir(), 'psl-unix-echo-sock-');

$listener = Unix\listen($path);

Async\concurrently([
    'server' => static function () use ($listener): void {
        $connection = $listener->accept();
        $data = $connection->readAll();
        $connection->writeAll("echo: {$data}");
        $connection->close();
        $listener->close();
    },
    'client' => static function () use ($path): void {
        $client = Unix\connect($path);
        $client->writeAll('hello');
        $client->shutdown();
        $_ = $client->readAll();
        $client->close();
    },
]);

@unlink($path);
