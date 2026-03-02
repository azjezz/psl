<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Option;

$a = Option\some(1);
$b = Option\some(2);
$none = Option\none();

$a->and($b); // Some(2) -- returns second if both Some
$none->or($b); // Some(2) -- returns first Some found
$none->orElse(fn() => Option\some(99)); // Some(99)

// Zip: combine into a tuple
$a->zip($b); // Some([1, 2])
$a->zip($none); // None

// ZipWith: combine with a function
$a->zipWith($b, fn(int $x, int $y) => $x + $y); // Some(3)

// Unzip: split a tuple Option
Option\some([1, 2])->unzip(); // [Some(1), Some(2)]
Option\none()->unzip(); // [None, None]
