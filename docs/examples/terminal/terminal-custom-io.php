<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Terminal;

final class RemoteState {}

$sshInputStream = IO\input_handle();
$sshOutputStream = IO\output_handle();

$app = Terminal\Application::custom::<RemoteState>(
    state: new RemoteState(),
    input: $sshInputStream,
    output: $sshOutputStream,
    width: 80,
    height: 24,
    title: 'Remote App',
);
