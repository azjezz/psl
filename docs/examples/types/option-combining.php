<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Option;

$a = Option\some::<int>(1);
$b = Option\some::<int>(2);
$none = Option\none();

$a->and::<int>($b); // Some(2) -- returns second if both Some
$none->or::<int>($b); // Some(2) -- returns first Some found
$none->orElse::<int>(fn() => Option\some::<int>(99)); // Some(99)

// Zip: combine into a tuple
$a->zip::<int>($b); // Some([1, 2])
$a->zip::<int>($none); // None

// ZipWith: combine with a function
$a->zipWith::<int, int>($b, fn(int $x, int $y) => $x + $y); // Some(3)

// Unzip: split a tuple Option
Option\some::<array>([1, 2])->unzip::<int, int>(); // [Some(1), Some(2)]
Option\none()->unzip::<int, int>(); // [None, None]
