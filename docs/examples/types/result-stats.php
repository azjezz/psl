<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Json;
use Psl\Result;
use Psl\Vec;

$inputs = ['{"a":1}', 'invalid', '{"b":2}', '{bad}', '{"c":3}'];

$results = Vec\map::<int, string, Result\ResultInterface<mixed>>($inputs, fn(string $input) => Result\wrap::<mixed>(fn() => Json\decode($input)));

$stats = Result\collect_stats::<mixed>($results);
$stats->total(); // total number of results
$stats->succeeded(); // number of successes
$stats->failed(); // number of failures
