<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

// Create two pipes to simulate concurrent streams
[$outReader, $outWriter] = IO\pipe();
[$errReader, $errWriter] = IO\pipe();

// Write data to both pipes
$outWriter->writeAll('stdout output');
$outWriter->close();
$errWriter->writeAll('stderr output');
$errWriter->close();

foreach (IO\streaming(['out' => $outReader, 'err' => $errReader]) as $name => $chunk) {
    IO\write_line('[%s] %s', $name, $chunk);
}

$outReader->close();
$errReader->close();
