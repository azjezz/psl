<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;
use Psl\Locale\Locale;

$now = DateTime\DateTime::now();
$formatted = $now->format('EEEE, MMMM d', locale: Locale::GermanGermany);
IO\write_line('German date: %s', $formatted);
