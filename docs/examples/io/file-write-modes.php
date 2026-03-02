<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\File;
use Psl\Filesystem;
use Psl\IO;

$logFile = Filesystem\create_temporary_file(prefix: 'psl_log_');
$lockFile = sys_get_temp_dir() . '/psl-session-' . uniqid() . '.lock';

// Append a log line
File\write($logFile, "event occurred\n", File\WriteMode::Append);
File\write($logFile, "another event\n", File\WriteMode::Append);
IO\write_line('Log contents: %s', File\read($logFile));

// Ensure we are creating a fresh file
$id = 'session-abc123';
File\write($lockFile, $id, File\WriteMode::MustCreate);
IO\write_line('Lock file contents: %s', File\read($lockFile));

Filesystem\delete_file($logFile);
Filesystem\delete_file($lockFile);
