<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

Type\literal_scalar('hello')->assert('hello');
Type\literal_scalar(42)->assert(42);
