<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Message;
use Psl\Message\Address\Mailbox;
use Psl\MIME\Part;

$original = Message\parse(
    "From: alice@example.com\r\nTo: bob@example.com\r\nSubject: Hello\r\nMessage-ID: <msg-001@example.com>\r\n\r\nHi Bob!",
);

$me = Mailbox::parse('bob@example.com');

// Reply sets To from original From, prefixes subject, sets threading headers
$reply = Message\Message::reply($original, $me)->withContent(new Part\Text(new IO\MemoryHandle('Thanks Alice!')));

$reply->subject; // "Re: Hello"
$reply->inReplyTo; // [MessageId("msg-001@example.com")]
$reply->references; // [MessageId("msg-001@example.com")]

// Also available: Message::replyAll() and Message::forward()
