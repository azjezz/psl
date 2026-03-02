<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Runtime;

IO\write_line('Debug build: %s', Runtime\is_debug() ? 'yes' : 'no');
IO\write_line('Thread safe: %s', Runtime\is_thread_safe() ? 'yes' : 'no');
