<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Either;

$left = new Either\Left('hello');
$right = $left->swap(); // Right('hello')
