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
 *
 * Implementing the DefaultInterface, it provides a method to obtain a default encoding,
 * which is UTF-8 due to its wide compatibility and support for a vast range of characters.
 */
enum Encoding: string implements DefaultInterface
{
    // ASCII compatible multi-byte 8-bit Unicode.
    case UTF_8 = 'UTF-8';

    // Western European, Latin-1.
    case ISO_8859_1 = 'ISO-8859-1';

    // Western European, Latin-9.
    case ISO_8859_15 = 'ISO-8859-15';

    // Cyrillic charset (Latin/Cyrillic).
    case ISO_8859_5 = 'ISO-8859-5';

    // DOS-specific Cyrillic charset.
    case CP_866 = 'cp866';

    // Windows-specific Cyrillic charset.
    case CP_1251 = 'cp1251';

    // Windows specific charset for Western European.
    case CP_1252 = 'cp1252';

    // Russian.
    case KOI8_R = 'KOI8-R';

    // Traditional Chinese, mainly used in Taiwan.
    case BIG5 = 'BIG5';

    // Simplified Chinese, national standard character set.
    case GB2312 = 'GB2312';

    // Big5 with Hong Kong extensions, Traditional Chinese.
    case BIG5_HKSCS = 'BIG5-HKSCS';

    // Japanese
    case SHIFT_JIS = 'Shift_JIS';

    // Japanese
    case EUC_JP = 'EUC-JP';

    // Charset that was used by Mac OS.
    case MAC_ROMAN = 'MacRoman';

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
    public static function default(): static
    {
        return self::UTF_8;
    }
}
