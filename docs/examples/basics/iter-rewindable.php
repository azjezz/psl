<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;

$gen = (function () {
    yield 'a';
    yield 'b';
    yield 'c';
})();

$iterator = Iter\rewindable::<int, string>($gen);

Iter\count::<string>($iterator); // 3
$iterator->rewind();
Iter\first::<string>($iterator); // 'a' -- still accessible after counting
