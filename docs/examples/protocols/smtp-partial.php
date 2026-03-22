<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Message\Address\Mailbox;
use Psl\Message\Envelope;
use Psl\Message\Message;
use Psl\MIME\Part;
use Psl\SMTP\Client\Transport;
use Psl\SMTP\Client\TransportConfiguration;
use Psl\SMTP\Security;

// Enable partial success to deliver to accepted recipients
// even when some are rejected
$transport = new Transport(
    TransportConfiguration::default()
        ->withHost('smtp.example.com')
        ->withSecurity(Security::TLS)
        ->withAllowPartialSuccess(true),
);

$sender = new Mailbox('noreply', 'example.com');
$message = new Message()
    ->withFrom($sender)
    ->withTo('alice@example.com, bob@invalid.test, carol@example.com')
    ->withSubject('Team Update')
    ->withContent(new Part\Text(new IO\MemoryHandle('Hello team!')));

$report = $transport->send(Envelope::fromMessage($message), $message);

// Check for rejected recipients
if ($report->hasRejections()) {
    foreach ($report->rejectedRecipients as [$mailbox, $reply]) {
        // $mailbox->address: "bob@invalid.test"
        // $reply->code: 550
        // $reply->message: "User not found"
        IO\write_line($reply->message);
    }
}

$transport->close();
