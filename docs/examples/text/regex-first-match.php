<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Regex;

$match = Regex\first_match('Order #12345', '/Order #(\d+)/');

// ['Order #12345', '12345']
