<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\File;
use Psl\Result;
use Psl\Str;

$lines = Result\wrap(fn() => File\read(__FILE__))
    ->map(fn(string $content) => Str\split($content, "\n"))
    ->catch(fn(Throwable $_e) => []);

// Success: maps content to lines
// Failure: recovers with empty array
