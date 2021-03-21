<?php

use Psl\Filesystem;
use Psl\IO;
use Psl\Str;
use Psl\Asio;

require_once __DIR__ . '/../vendor/autoload.php';

$file = Filesystem\open_file_read_write(__DIR__ . '/example.txt');
$file->writeAll('Hello, World!');

Psl\invariant($file->tell() === 13, 'invalid position.');

$file->writeAll("\n");
$file->writeAll('Hello, World!');

Psl\invariant($file->tell() === 27, 'invalid position.');

$file->seek(0);

Psl\invariant($file->tell() === 0, 'invalid position.');

$content = $file->readFixedSize(13);

Psl\invariant($content === 'Hello, World!', 'invalid content.');

$file->seek($file->tell() + 1);

$content = $file->readFixedSize(13);

Psl\invariant($content === 'Hello, World!', 'invalid content.');

Psl\invariant(Str\Byte\ends_with($file->getPath(), 'example.txt'), 'Invalid path');

$file->seek(314);
$file->writeAll('x');
$file->seek(300);

$content = $file->readAll();

Psl\invariant(Str\Byte\length($content) === 15, 'unexpected error.');

Psl\invariant(Str\Byte\starts_with($content, Str\repeat("\0", 14)), 'content is not prefixed with NUL byte');

Psl\invariant(Str\Byte\ends_with($content, 'x'), 'content does not contain x');
