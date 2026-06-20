<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Fun;

$fn = Fun\identity::<int>();
$fn(42); // 42
