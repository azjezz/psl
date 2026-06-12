<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\EitherOrBoth;

// Via constructors
$onlyNew = new EitherOrBoth\Left('new-record');
$onlyOld = new EitherOrBoth\Right('old-record');
$conflict = new EitherOrBoth\Both('new-version', 'old-version');

// Via free functions
$onlyNew = EitherOrBoth\left('new-record');
$onlyOld = EitherOrBoth\right('old-record');
$conflict = EitherOrBoth\both('new-version', 'old-version');
