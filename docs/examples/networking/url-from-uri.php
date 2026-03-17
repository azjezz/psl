<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\URI;
use Psl\URL;

$uri = URI\parse('https://example.com/path');
$url = URL\from_uri($uri);

// "https://example.com/path"
IO\write_line('%s', $url->toString());
