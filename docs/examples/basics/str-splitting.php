<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;

Str\split('one,two,three', ','); // ['one', 'two', 'three']
Str\split('one,two,three', ',', 2); // ['one', 'two,three']

Str\join(['one', 'two', 'three'], ', '); // 'one, two, three'
