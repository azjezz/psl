<?php

declare(strict_types=1);

namespace Psl\HPACK\Internal;

use function array_key_exists;

/**
 * HPACK static table per RFC 7541 Appendix A.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7541#appendix-A
 *
 * @internal
 */
final class StaticTable
{
    /**
     * @var list<array{non-empty-lowercase-string, string}>
     */
    private const array ENTRIES = [
        [':authority',                  ''],
        [':method',                     'GET'],
        [':method',                     'POST'],
        [':path',                       '/'],
        [':path',                       '/index.html'],
        [':scheme',                     'http'],
        [':scheme',                     'https'],
        [':status',                     '200'],
        [':status',                     '204'],
        [':status',                     '206'],
        [':status',                     '304'],
        [':status',                     '400'],
        [':status',                     '404'],
        [':status',                     '500'],
        ['accept-charset',              ''],
        ['accept-encoding',             'gzip, deflate'],
        ['accept-language',             ''],
        ['accept-ranges',               ''],
        ['accept',                      ''],
        ['access-control-allow-origin', ''],
        ['age',                         ''],
        ['allow',                       ''],
        ['authorization',               ''],
        ['cache-control',               ''],
        ['content-disposition',         ''],
        ['content-encoding',            ''],
        ['content-language',            ''],
        ['content-length',              ''],
        ['content-location',            ''],
        ['content-range',               ''],
        ['content-type',                ''],
        ['cookie',                      ''],
        ['date',                        ''],
        ['etag',                        ''],
        ['expect',                      ''],
        ['expires',                     ''],
        ['from',                        ''],
        ['host',                        ''],
        ['if-match',                    ''],
        ['if-modified-since',           ''],
        ['if-none-match',               ''],
        ['if-range',                    ''],
        ['if-unmodified-since',         ''],
        ['last-modified',               ''],
        ['link',                        ''],
        ['location',                    ''],
        ['max-forwards',                ''],
        ['proxy-authenticate',          ''],
        ['proxy-authorization',         ''],
        ['range',                       ''],
        ['referer',                     ''],
        ['refresh',                     ''],
        ['retry-after',                 ''],
        ['server',                      ''],
        ['set-cookie',                  ''],
        ['strict-transport-security',   ''],
        ['transfer-encoding',           ''],
        ['user-agent',                  ''],
        ['vary',                        ''],
        ['via',                         ''],
        ['www-authenticate',            ''],
    ];

    /**
     * @var null|array<string, list<int>>
     */
    private static null|array $nameIndex = null;

    /**
     * @param int<1, max> $index
     *
     * @return null|array{non-empty-lowercase-string, string}
     */
    public static function get(int $index): null|array
    {
        return self::ENTRIES[$index - 1] ?? null;
    }

    /**
     * @return null|array{int, bool} [1-based index, full match]
     */
    public static function search(string $name, string $value): null|array
    {
        $nameIndex = self::getNameIndex();

        if (!array_key_exists($name, $nameIndex)) {
            return null;
        }

        $nameMatch = null;
        foreach ($nameIndex[$name] as $i) {
            /** @mago-expect analysis:mismatched-array-index */
            if (self::ENTRIES[$i][1] === $value) {
                return [$i + 1, true];
            }

            $nameMatch ??= $i + 1;
        }

        return $nameMatch !== null ? [$nameMatch, false] : null;
    }

    /**
     * @return array<string, list<int>>
     */
    private static function getNameIndex(): array
    {
        if (self::$nameIndex === null) {
            self::$nameIndex = [];
            foreach (self::ENTRIES as $i => [$name, $_]) {
                self::$nameIndex[$name][] = $i;
            }
        }

        return self::$nameIndex;
    }
}
