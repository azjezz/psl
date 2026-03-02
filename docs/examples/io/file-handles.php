<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\File;
use Psl\Filesystem;
use Psl\IO;
use Psl\Str;

$csvFile = Filesystem\create_temporary_file(prefix: 'psl_csv_');
$outputFile = Filesystem\create_temporary_file(prefix: 'psl_out_');
$datFile = Filesystem\create_temporary_file(prefix: 'psl_dat_');

// Prepare CSV data
File\write($csvFile, "name,age\nAlice,30\nBob,25\n");

// Read-only
$handle = File\open_read_only($csvFile);
$all = $handle->readAll();
$handle->close();
IO\write_line('CSV data: %s', Str\trim_right($all));

// Write-only with truncation
$handle = File\open_write_only($outputFile, File\WriteMode::Truncate);
$handle->writeAll("line 1\nline 2\n");
$handle->close();
IO\write_line('Output: %s', Str\trim_right(File\read($outputFile)));

// Read-write
File\write($datFile, 'hello world');
$handle = File\open_read_write($datFile);
$existing = $handle->readAll();
$handle->seek(0);
$handle->writeAll(Str\uppercase($existing));
$handle->close();
IO\write_line('Uppercased: %s', File\read($datFile));

Filesystem\delete_file($csvFile);
Filesystem\delete_file($outputFile);
Filesystem\delete_file($datFile);
