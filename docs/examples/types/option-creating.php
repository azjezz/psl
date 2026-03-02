<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Option;

$present = Option\some(42);
$absent = Option\none();

// Bridge from nullable values
$option = Option\from_nullable('hello');
// null becomes None, anything else becomes Some

$fromNull = Option\from_nullable(null);

// $fromNull is None
