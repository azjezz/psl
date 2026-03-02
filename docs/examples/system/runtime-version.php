<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Runtime;

$version = Runtime\get_version();
IO\write_line('PHP version: %s', $version);

$id = Runtime\get_version_id();
IO\write_line('Version ID: %d', $id);

$details = Runtime\get_version_details();
IO\write_line('Major: %d, Minor: %d, Release: %d', $details['major'], $details['minor'], $details['release']);
