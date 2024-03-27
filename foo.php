<?php

declare(strict_types=1);

use Psl\File;
use Psl\Async;
use Psl\IO;

require 'vendor/autoload.php';

Async\main(function (): void {
    $file = File\open_read_only(__FILE__);
    $bytes = 0;
    while (!($x = $file->reachedEndOfDataSource())) {
        var_dump($x);
        $byte = $file->readFixedSize(1);
        $bytes += strlen($byte);

        IO\write_line('read %d bytes.', $bytes);
        var_dump($file->reachedEndOfDataSource());
    }

    $file->close();
});
