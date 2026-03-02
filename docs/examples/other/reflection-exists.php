<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Class;
use Psl\IO;

// Triggers autoloading if needed
IO\write_line('stdClass exists: %s', Class\exists(stdClass::class) ? 'yes' : 'no');

// Only checks already-loaded classes (no autoloading)
IO\write_line('stdClass defined: %s', Class\defined(stdClass::class) ? 'yes' : 'no');
