<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Message;
use Psl\MIME\Part;

$message = new Message\Message()
    ->withFrom('alice@example.com')
    ->withTo('bob@example.com')
    ->withSubject('Round trip')
    ->withBody(new Part\Text(new IO\MemoryHandle('Hello!')));

// Serialize to a streaming handle (headers + body)
$handle = Message\serialize($message);

// Parse back from a string or handle
$parsed = Message\parse($handle);

$parsed->subject; // "Round trip"
$parsed->from?->mailboxes()[0]?->address; // "alice@example.com"
$parsed->content->body()->readAll(); // encoded body content
