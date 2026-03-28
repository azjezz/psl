<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

// Wrap a generator as a streaming read handle
$handle = new IO\IterableReadHandle(
    (function () {
        yield 'first chunk';
        yield 'second chunk';
        yield 'third chunk';
    })(),
);

$handle->read(); // 'first chunk'
$handle->read(); // 'second chunk'
$handle->read(); // 'third chunk'
$handle->read(); // '' (EOF)
$handle->reachedEndOfDataSource(); // true

// Also works with arrays
$handle = new IO\IterableReadHandle(['hello', ' ', 'world']);
$handle->readAll(); // 'hello world'
