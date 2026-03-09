<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Regex;
use Psl\Type;

$pattern = '/(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})/';

$match = Regex\first_match('Date: 2025-03-15', $pattern, Type\shape([
    0 => Type\string(),
    'year' => Type\string(),
    'month' => Type\string(),
    'day' => Type\string(),
]));

// $match['year'] === '2025', $match['month'] === '03', $match['day'] === '15'
