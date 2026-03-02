<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\IO;

$dt = DateTime\DateTime::now(DateTime\Timezone::UTC);

// ICU pattern
IO\write_line('ICU: %s', $dt->format('yyyy-MM-dd HH:mm:ss'));

// Predefined patterns
IO\write_line('ISO 8601: %s', $dt->format(DateTime\FormatPattern::Iso8601));
IO\write_line('SQL: %s', $dt->format(DateTime\FormatPattern::SqlDateTime));

// Style-based (locale-aware)
IO\write_line('Long/Short: %s', $dt->toString(DateTime\DateStyle::Long, DateTime\TimeStyle::Short));

// RFC 3339 for APIs
IO\write_line('RFC 3339: %s', $dt->toRfc3339());

// Parsing
$parsed = DateTime\DateTime::parse('2025-03-15 12:00:00', 'yyyy-MM-dd HH:mm:ss', DateTime\Timezone::UTC);
IO\write_line('Parsed: %s', $parsed->toRfc3339());
