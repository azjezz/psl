<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Locale\Locale;

$locale = Locale::default();
IO\write_line('Default locale: %s', $locale->value);
