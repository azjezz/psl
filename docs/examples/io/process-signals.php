<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Process\Command;
use Psl\Process\Signal;
use Psl\Process\Stdio;

$child = Command::create('sleep')->withArgument('60')->withStdout(Stdio::null())->withStderr(Stdio::null())->spawn();

// Allow the child process to start before signaling
usleep(50_000);

$child->signal(Signal::Terminate);
$status = $child->wait();

IO\write_line('Has been signaled: %s', $status->hasBeenSignaled() ? 'true' : 'false');
$signal = $status->getTerminationSignal();
IO\write_line('Termination signal: %s', $signal !== null ? $signal->name : 'none');
