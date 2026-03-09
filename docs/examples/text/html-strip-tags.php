<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Html;

Html\strip_tags('<p>Hello <b>World</b></p>');
// 'Hello World'

Html\strip_tags('<p>Hello <b>World</b></p>', ['b']);

// 'Hello <b>World</b>'
