<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\OS\OperatingSystemFamily;

$result = match (OperatingSystemFamily::default()) {
    OperatingSystemFamily::Windows => 'Configured for Windows',
    OperatingSystemFamily::Darwin => 'Configured for macOS',
    OperatingSystemFamily::Linux => 'Configured for Linux',
    OperatingSystemFamily::BSD => 'Configured for BSD',
    default => 'Using default configuration',
};

IO\write_line('%s', $result);
