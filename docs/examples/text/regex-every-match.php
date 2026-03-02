<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Regex;

$matches = Regex\every_match('prices: $10, $20, $30', '/\$(\d+)/');

// [['$10', '10'], ['$20', '20'], ['$30', '30']]
