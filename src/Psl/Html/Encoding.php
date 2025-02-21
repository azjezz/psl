<?php

declare(strict_types=1);

namespace Psl\Html;

use Psl\Default\DefaultInterface;

/**
 * Enumerates supported character encodings for HTML content.
 *
 * This enum defines a set of character encodings commonly used in the context of
 * HTML documents and web development. It includes various Unicode, Western European,
 * Cyrillic, Chinese, Japanese, and other character sets to support internationalization
 * and localization of web content.
 */
enum Encoding: string implements DefaultInterface
{
    /**
     * ASCII compatible multi-byte 8-bit Unicode.
     */
    case Utf8 = 'UTF-8';

    /**
     * Western European, Latin-1.
     */
    case Iso88591 = 'ISO-8859-1';

    /**
     * Western European, Latin-9.
     */
    case Iso885915 = 'ISO-8859-15';

    /**
     * Cyrillic charset (Latin/Cyrillic).
     */
    case Iso88595 = 'ISO-8859-5';

    /**
     * DOS-specific Cyrillic charset.
     */
    case Cp866 = 'cp866';

    /**
     * Windows-specific Cyrillic charset.
     */
    case Cp1251 = 'cp1251';

    /**
     * Windows specific charset for Western European.
     */
    case Cp1252 = 'cp1252';

    /**
     * Russian.
     */
    case Koi8R = 'KOI8-R';

    /**
     * Traditional Chinese.
     */
    case Big5 = 'BIG5';

    /**
     * Simplified Chinese, national standard character set.
     */
    case Gb2312 = 'GB2312';

    /**
     * Traditional Chinese ( Big5 with Hong Kong extensions ).
     */
    case Big5Hkscs = 'BIG5-HKSCS';

    /**
     * Japanese.
     */
    case ShiftJis = 'Shift_JIS';

    /**
     * Japanese.
     */
    case EucJp = 'EUC-JP';

    /**
     * Charset that was used by macOS.
     */
    case MacRoman = 'MacRoman';

    /**
     * Provides the default character encoding.
     *
     * UTF-8 is returned as the default encoding due to its comprehensive support for Unicode,
     * making it a versatile choice for web content that may include a wide variety of characters
     * from different languages. It is recommended for new web projects and content to ensure
     * maximum compatibility and inclusivity.
     *
     * @return static The UTF-8 encoding instance, representing the default encoding.
     */
    #[\Override]
    public static function default(): static
    {
        return self::Utf8;
    }
}
