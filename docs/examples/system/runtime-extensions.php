<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Runtime;

if (Runtime\has_extension('mbstring')) {
    IO\write_line('mbstring is available.');
}

$extensions = Runtime\get_extensions();
IO\write_line('Loaded extensions: %d', count($extensions));

$zendExtensions = Runtime\get_zend_extensions();
IO\write_line('Zend extensions: %d', count($zendExtensions));
