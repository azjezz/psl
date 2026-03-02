<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Interface;
use Psl\IO;
use Psl\Trait;

IO\write_line('JsonSerializable exists: %s', Interface\exists(JsonSerializable::class) ? 'yes' : 'no');
IO\write_line('JsonSerializable defined: %s', Interface\defined(JsonSerializable::class) ? 'yes' : 'no');

// Check a non-existent trait
IO\write_line('NonExistentTrait exists: %s', Trait\exists('NonExistentTrait') ? 'yes' : 'no');
