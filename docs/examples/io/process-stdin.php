<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Process\Command;
use Psl\Process\Stdio;

$child = Command::create('cat')->withStdin(Stdio::piped())->spawn();

$child->getStdin()->writeAll("hello\n");
$child->getStdin()->close();

$output = $child->getStdout()->readAll();
$child->wait();

IO\write_line('Read from child: %s', trim($output));
