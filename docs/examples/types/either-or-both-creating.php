<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\EitherOrBoth;

// Via constructors
$onlyNew = new EitherOrBoth\Left::<string>('new-record');
$onlyOld = new EitherOrBoth\Right::<string>('old-record');
$conflict = new EitherOrBoth\Both::<string, string>('new-version', 'old-version');

// Via free functions
$onlyNew = EitherOrBoth\left::<string>('new-record');
$onlyOld = EitherOrBoth\right::<string>('old-record');
$conflict = EitherOrBoth\both::<string, string>('new-version', 'old-version');
