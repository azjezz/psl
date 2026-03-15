<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Html;

Html\encode('&amp; is an ampersand', doubleEncoding: false);

// '&amp; is an ampersand' (not double-encoded to '&amp;amp;')
