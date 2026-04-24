<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\EitherOrBoth;

use function Psl\EitherOrBoth\both;
use function Psl\EitherOrBoth\left;
use function Psl\EitherOrBoth\right;

// Via constructors
$onlyNew = new EitherOrBoth\Left('new-record');
$onlyOld = new EitherOrBoth\Right('old-record');
$conflict = new EitherOrBoth\Both('new-version', 'old-version');

// Via free functions
$onlyNew = left('new-record');
$onlyOld = right('old-record');
$conflict = both('new-version', 'old-version');
