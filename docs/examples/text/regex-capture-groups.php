<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Regex;

$match = Regex\first_match('Hello World', '/(Hello) (World)/', Regex\capture_groups([1, 2]));

// $match[0] === 'Hello World', $match[1] === 'Hello', $match[2] === 'World'
