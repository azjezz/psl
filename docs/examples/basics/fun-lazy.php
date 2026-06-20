<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Fun;
use Psl\Vec;

$config = Fun\lazy::<array>(static fn(): array => Vec\fill::<string>(1000, 'value'));

$config(); // computes the array
$config(); // returns cached result
