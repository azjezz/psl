<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\TCP;
use Psl\TLS;

// Note: This example requires valid TLS certificate files to run.
// Replace the paths below with actual certificate and key files.
$listener = TCP\listen('0.0.0.0', 2525);
$cert = TLS\Certificate::create('/etc/ssl/certs/mail.pem', '/etc/ssl/private/mail.key');
$acceptor = new TLS\Acceptor(TLS\ServerConfig::create($cert));

$stream = $listener->accept();

// Plaintext phase
$reader = new IO\Reader($stream);
$line = $reader->readLine(); // "EHLO client.example.com"
$stream->writeAll("250-mail.example.com\r\n250 STARTTLS\r\n");

$line = $reader->readLine(); // "STARTTLS"
$stream->writeAll("220 Ready to start TLS\r\n");

// Upgrade to TLS
$tls = $acceptor->accept($stream);

// ... continue with encrypted SMTP
