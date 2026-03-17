<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\URI;

$base = URI\parse('https://example.com/a/b/c');

// "https://example.com/a/d"
IO\write_line('%s', URI\resolve($base, URI\parse('../d'))->toString());
// "https://other/x"
IO\write_line('%s', URI\resolve($base, URI\parse('//other/x'))->toString());
// "https://example.com/a/b/c?q=1"
IO\write_line('%s', URI\resolve($base, URI\parse('?q=1'))->toString());
