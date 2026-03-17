<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\URL;

$url = URL\parse('https://example.com/path');
$uri = $url->toURI();

// "https://example.com/path"
IO\write_line('%s', $uri->toString());
