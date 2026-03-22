<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Message;
use Psl\Message\Envelope;
use Psl\MIME\Part;

$message = new Message\Message()
    ->withFrom('alice@example.com')
    ->withTo('bob@example.com')
    ->withCc('carol@example.com')
    ->withBcc('dave@example.com')
    ->withContent(new Part\Text(new IO\MemoryHandle('Hello')));

// Derive SMTP envelope from message headers
$envelope = Envelope::fromMessage($message);

$envelope->sender; // Mailbox("alice@example.com")
$envelope->recipients; // [bob, carol, dave] (To + Cc + Bcc flattened)
