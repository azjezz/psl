<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Locale\Locale;

$locale = Locale::FrenchCanada;

IO\write_line('Value: %s', $locale->value); // 'fr_CA'
IO\write_line('Language: %s', $locale->getLanguage()); // 'fr'
IO\write_line('Region: %s', $locale->getRegion() ?? '(none)'); // 'CA'
IO\write_line('Display name: %s', $locale->getDisplayName()); // 'French (Canada)'
IO\write_line('Display language: %s', $locale->getDisplayLanguage()); // 'French'
IO\write_line('Display region: %s', $locale->getDisplayRegion() ?? '(none)'); // 'Canada'
IO\write_line('Has region: %s', $locale->hasRegion() ? 'yes' : 'no'); // true
IO\write_line('Has script: %s', $locale->hasScript() ? 'yes' : 'no'); // false
