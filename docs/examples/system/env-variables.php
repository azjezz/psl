<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Env;
use Psl\IO;

// Read -- returns null if not set
$home = Env\get_var('HOME');
IO\write_line('HOME: %s', $home ?? '(not set)');

// Write
Env\set_var('APP_ENV', 'production');
IO\write_line('APP_ENV: %s', Env\get_var('APP_ENV') ?? '(not set)');

// Remove
Env\remove_var('APP_ENV');
IO\write_line('APP_ENV after remove: %s', Env\get_var('APP_ENV') ?? '(not set)');

// Get all environment variables as an associative array
$allVars = Env\get_vars();
IO\write_line('Total env vars: %d', count($allVars));
