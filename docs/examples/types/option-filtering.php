<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Option;

Option\some::<int>(42)->filter(fn(int $v) => $v > 10); // Some(42)
Option\some::<int>(42)->filter(fn(int $v) => $v > 100); // None
