<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Message\FieldMap;
use Psl\IO;

$headers = FieldMap::from([
    ['Content-Type', 'application/json'],
    ['Accept',       'text/html'],
    ['Accept',       'application/json'],
    ['X-Request-Id', 'abc-123'],
]);

$headers->get('content-type'); // "application/json" (case-insensitive)
$headers->getAll('Accept'); // ["text/html", "application/json"]
$headers->has('X-Request-Id'); // true

$updated = $headers
    ->with('Content-Type', 'text/plain')
    ->withAdded('Cache-Control', 'no-cache')
    ->without('X-Request-Id');

$updated->get('Content-Type'); // "text/plain"
$updated->has('X-Request-Id'); // false

foreach ($headers as [$name, $value]) {
    // Iterates in insertion order, preserving original casing
    IO\write_line('%s: %s', $name, $value);
}
