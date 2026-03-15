<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Json;
use Psl\Result;

$_ = Result\try_catch(fn() => Json\decode('not valid json'), fn(Throwable $e) => []);
