<?php

declare(strict_types=1);

namespace Psl\DateTime;

/**
 * An enumeration of common date format strings.
 *
 * This enum provides a collection of standardized date format strings for various protocols
 * and standards, such as RFC 2822, ISO 8601, HTTP headers, and more.
 */
enum DateFormat: string
{
    case Rfc2822 = 'EEE, dd MMM yyyy HH:mm:ss Z';
    case Iso8601 = 'yyyy-MM-dd\'T\'HH:mm:ssXXX';
    case Http = 'EEE, dd MMM yyyy HH:mm:ss zzz';
    case Cookie = 'EEEE, dd-MMM-yyyy HH:mm:ss zzz';
    case Sql = 'yyyy-MM-dd HH:mm:ss';
    case XmlRpc = 'yyyyMMdd\'T\'HH:mm:ss';
    case IsoWeekDate = 'Y-ww-E';
    case IsoOrdinalDate = 'yyyy-DDD';
    case JulianDay = 'yyyy DDD';
}