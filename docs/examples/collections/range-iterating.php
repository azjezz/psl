<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Range;

$range = Range\between(0, 5);

$range = Range\between(0, 5, true);

// FromRange is iterable but infinite -- use with care
$range = Range\from(0);
foreach ($range as $value) {
    // $value is 0, 1, 2, 3, ...
    if ($value > 100) {
        break;
    }
}
