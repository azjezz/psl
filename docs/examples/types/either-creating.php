<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Either;

$left = new Either\Left::<string>('not found');
$right = new Either\Right::<int>(42);

$right->isRight(); // true
$left->isLeft(); // true
