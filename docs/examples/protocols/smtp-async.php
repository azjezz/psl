<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\IO;
use Psl\Message\Address\Mailbox;
use Psl\Message\Envelope;
use Psl\Message\Message;
use Psl\MIME\Part;
use Psl\SMTP\Client\Transport;
use Psl\SMTP\Client\TransportConfiguration;
use Psl\SMTP\Security;
use Psl\Vec;

$transport = new Transport(
    TransportConfiguration::default()->withHost('smtp.example.com')->withSecurity(Security::TLS),
);

$sender = new Mailbox('noreply', 'example.com');
$recipients = ['alice@example.com', 'bob@example.com', 'carol@example.com'];

// Send to all recipients concurrently, reusing pooled connections
Async\concurrently::<int, void>(Vec\map::<int, string, Closure>($recipients, static fn(string $address): Closure => static function () use (
    $transport,
    $sender,
    $address,
): void {
    $recipient = Mailbox::parse($address);
    $message = new Message()
        ->withFrom($sender)
        ->withTo($recipient)
        ->withSubject('Hello!')
        ->withContent(new Part\Text(new IO\MemoryHandle('Hi there!')));

    $transport->send(Envelope::fromMessage($message), $message);
}));

// Send with a timeout using a cancellation token
$message = new Message()
    ->withFrom($sender)
    ->withTo('dave@example.com')
    ->withSubject('Urgent')
    ->withContent(new Part\Text(new IO\MemoryHandle('Time-sensitive!')));

$transport->send(
    Envelope::fromMessage($message),
    $message,
    cancellation: new Async\TimeoutCancellationToken(Psl\DateTime\Duration::seconds(10)),
);

$transport->close();
