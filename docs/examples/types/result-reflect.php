<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Json;
use Psl\Result;

$safe_parse = Result\reflect(fn() => Json\decode('{"key":"value"}'));
$result = $safe_parse(); // ResultInterface, never throws
