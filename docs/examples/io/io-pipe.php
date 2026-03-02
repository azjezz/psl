<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

[$reader, $writer] = IO\pipe();
$writer->writeAll('hello');
$writer->close();

$result = $reader->readAll(); // 'hello'
$reader->close();

IO\write_line('Read from pipe: %s', $result);
