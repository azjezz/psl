<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\MIME\MediaType;
use Psl\MIME\MultiPart;
use Psl\MIME\Part;

// multipart/alternative: text + HTML versions
$alt = new MultiPart\Alternative();
$alt->addPart(new Part\Text(new IO\MemoryHandle('Plain text')));
$alt->addPart(new Part\Text(new IO\MemoryHandle('<h1>HTML</h1>'), subtype: 'html'));

// multipart/mixed: body + attachments
$mixed = new MultiPart\Composite($alt);
$mixed->addPart(
    new Part\Data(
        new IO\MemoryHandle('pdf content'),
        filename: 'doc.pdf',
        mediaType: MediaType::parse('application/pdf'),
    ),
);

// Nested multipart is just $mixed->body() - a streaming read handle
// IO\copy($mixed->body(), $outputHandle);
