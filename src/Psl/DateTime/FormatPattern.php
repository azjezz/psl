<?php

declare(strict_types=1);

namespace Psl\DateTime;

use Psl\Default\DefaultInterface;

/**
 * An enum of common date pattern strings.
 *
 * This enum provides a collection of standardized date pattern strings for various protocols
 * and standards, such as RFC 2822, ISO 8601, HTTP headers, and more.
 */
enum FormatPattern: string implements DefaultInterface
{
    case Rfc2822 = 'EEE, dd MMM yyyy HH:mm:ss Z';
    case Iso8601 = 'yyyy-MM-dd\'T\'HH:mm:ss.SSSXXX';
    case Iso8601WithoutMicroseconds = 'yyyy-MM-dd\'T\'HH:mm:ssXXX';
    case Http = 'EEE, dd MMM yyyy HH:mm:ss zzz';
    case Cookie = 'EEEE, dd-MMM-yyyy HH:mm:ss zzz';
    case SqlDate = 'yyyy-MM-dd';
    case SqlDateTime = 'yyyy-MM-dd HH:mm:ss';
    case XmlRpc = 'yyyyMMdd\'T\'HH:mm:ss';
    case IsoWeekDate = 'Y-ww-E';
    case IsoOrdinalDate = 'yyyy-DDD';
    case JulianDay = 'yyyy DDD';
    case Rfc3339 = 'yyyy-MM-dd\'T\'HH:mm:ss.SSSZZZZZ';
    case Rfc3339WithoutMicroseconds = 'yyyy-MM-dd\'T\'HH:mm:ssZZZZZ';
    case UnixTimestamp = 'U';
    case SimpleDate = 'dd/MM/yyyy';
    case American = 'MM/dd/yyyy';
    case WeekdayMonthDayYear = 'EEE, MMM dd, yyyy';
    case Email = 'EEE, dd MMM yyyy HH:mm:ss ZZZZ';
    case LogTimestamp = 'yyyy-MM-dd HH:mm:ss,SSS';
    case FullDateTime = 'EEEE, MMMM dd, yyyy HH:mm:ss';

    #[\Override]
    public static function default(): static
    {
        return static::Iso8601;
    }
}
