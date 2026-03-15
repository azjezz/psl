<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\TCP;

// Default backlog of 512
$listener = TCP\listen('127.0.0.1', 8080);

// High-throughput server with larger backlog
$listener = TCP\listen('127.0.0.1', 8080, new TCP\ListenConfiguration(backlog: 4096));
