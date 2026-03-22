<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\SMTP\Client\Connection;
use Psl\SMTP\Command;
use Psl\TCP;

// Low-level connection for direct SMTP protocol interaction
$stream = TCP\connect('smtp.example.com', 25);
$connection = new Connection($stream);

// Read the server greeting
$greeting = $connection->readGreeting();
// $greeting->code: 220
// $greeting->message: "smtp.example.com ESMTP"

// EHLO to discover capabilities
$ehlo = $connection->ehlo('client.example.com');

// Check capabilities
$connection->supportsCapability('PIPELINING'); // bool
$connection->supportsCapability('8BITMIME'); // bool
$connection->getCapabilityValue('AUTH'); // "PLAIN LOGIN" or null
$connection->maxSize; // int or null

// Send arbitrary commands
$reply = $connection->sendCommand(new Command('NOOP'));
// $reply->code: 250
// $reply->isPositiveCompletion(): true

// Or send without reading reply (for pipelining)
$connection->writeCommand(new Command('NOOP'));
$reply = $connection->readReply();

$connection->close();
