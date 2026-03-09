<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

$stdin = IO\input_handle(); // ReadHandleInterface  (STDIN in CLI)
$stdout = IO\output_handle(); // WriteHandleInterface (STDOUT in CLI)
$stderr = IO\error_handle(); // WriteHandleInterface (STDERR in CLI, null otherwise)
