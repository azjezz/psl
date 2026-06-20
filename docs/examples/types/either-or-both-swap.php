<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\EitherOrBoth;

$left = new EitherOrBoth\Left::<string>('hello');
$swappedLeft = $left->swap(); // Right('hello')

$right = new EitherOrBoth\Right::<string>('world');
$swappedRight = $right->swap(); // Left('world')

$both = new EitherOrBoth\Both::<string, string>('l', 'r');
$swappedBoth = $both->swap(); // Both('r', 'l')
