<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Regex;

Regex\split('one, two,  three', '/,\s*/');
// ['one', 'two', 'three']

Regex\split('a-b-c-d', '/-/', 2);

// ['a', 'b-c-d']
