<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Message;
use Psl\MIME\MediaType;
use Psl\MIME\MultiPart;
use Psl\MIME\Part;

// Text + HTML alternatives
$alt = new MultiPart\Alternative();
$alt->addPart(new Part\Text(new IO\MemoryHandle('Plain text version')));
$alt->addPart(new Part\Text(new IO\MemoryHandle('<h1>HTML version</h1>'), 'html'));

// Wrap with an attachment
$mixed = new MultiPart\Composite($alt);
$mixed->addPart(
    new Part\Data(
        new IO\MemoryHandle('pdf content'),
        filename: 'report.pdf',
        mediaType: MediaType::parse('application/pdf'),
    ),
);

$message = new Message\Message()
    ->withFrom('alice@example.com')
    ->withTo('bob@example.com')
    ->withSubject('Report attached')
    ->withBody($mixed);
