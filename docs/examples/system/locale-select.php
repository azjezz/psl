<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Locale\Locale;

$locale = Locale::EnglishUnitedStates; // 'en_US'
IO\write_line('en_US: %s', $locale->value);

$locale = Locale::FrenchFrance; // 'fr_FR'
IO\write_line('fr_FR: %s', $locale->value);

$locale = Locale::Japanese; // 'ja'
IO\write_line('ja: %s', $locale->value);

$locale = Locale::ChineseSimplified; // 'zh_Hans'
IO\write_line('zh_Hans: %s', $locale->value);
