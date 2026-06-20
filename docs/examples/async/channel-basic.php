<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Channel;

// Bounded: holds at most 10 messages
[$receiver, $sender] = Channel\bounded::<string>(10);

// Unbounded: no capacity limit
[$receiver, $sender] = Channel\unbounded::<string>();
