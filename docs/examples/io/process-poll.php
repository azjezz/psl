<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Process\Command;
use Psl\Process\Stdio;

$child = Command::create('sleep')->withArgument('0.1')->withStdout(Stdio::null())->withStderr(Stdio::null())->spawn();

$status = $child->tryWait(); // null (still running)
IO\write_line('Still running: %s', $status === null ? 'true' : 'false');

// ... do other work ...

$status = $child->wait(); // blocks until done
IO\write_line('Finished with code: %d', $status->getCode());
