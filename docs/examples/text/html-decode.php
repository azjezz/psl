<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Html;

Html\decode('&lt;p&gt;Hello&lt;/p&gt;');
// '<p>Hello</p>'

Html\decode_special_characters('&lt;p&gt;Hello&lt;/p&gt;');

// '<p>Hello</p>'
