<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Locale\Locale;

$locale = Locale::SerbianLatin;

IO\write_line('Script: %s', $locale->getScript() ?? '(none)'); // 'Latn'
IO\write_line('Has script: %s', $locale->hasScript() ? 'yes' : 'no'); // true
