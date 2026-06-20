<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Json;
use Psl\Result;

$safeParse = Result\reflect::<mixed>(fn() => Json\decode('{"key":"value"}'));
$result = $safeParse(); // ResultInterface, never throws
