<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\MIME\Headers;
use Psl\MIME\MediaType;
use Psl\MIME\Part;

// Raw part from headers + body
$raw = new Part\Part(Headers::fromPairs([['Content-Type', 'text/plain']]), new IO\MemoryHandle('Hello'));

// Text part with automatic transfer encoding (quoted-printable by default)
$text = new Part\Text(new IO\MemoryHandle('Hello, World!'));
$text->mediaType; // text/plain; charset=utf-8

// Binary attachment with base64 encoding
$data = new Part\Data(
    new IO\MemoryHandle('binary content'),
    filename: 'report.pdf',
    mediaType: MediaType::parse('application/pdf'),
);

// Convert attachment to inline with Content-ID
$inline = $data->asInline(Psl\MIME\ContentId::generate());
