<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Default\DefaultInterface;

/**
 * Enumerates supported character encodings for string operations.
 *
 * This enum defines a comprehensive list of character encodings supported for various string manipulation
 * and processing tasks. It includes encodings from multiple languages and regions, ensuring wide-ranging
 * internationalization support across different platforms and systems.
 */
enum Encoding: string implements DefaultInterface
{
    case Base64 = 'BASE64';
    case Uuencode = 'UUENCODE';
    case HtmlEntities = 'HTML-ENTITIES';
    case QuotedPrintable = 'Quoted-Printable';
    case Ascii7bit = '7bit';
    case Ascii8bit = '8bit';
    case Ucs4 = 'UCS-4';
    case Ucs4be = 'UCS-4BE';
    case Ucs4le = 'UCS-4LE';
    case Ucs2 = 'UCS-2';
    case Ucs2be = 'UCS-2BE';
    case Ucs2le = 'UCS-2LE';
    case Utf32 = 'UTF-32';
    case Utf32be = 'UTF-32BE';
    case Utf32le = 'UTF-32LE';
    case Utf16 = 'UTF-16';
    case Utf16be = 'UTF-16BE';
    case Utf16le = 'UTF-16LE';
    case Utf8 = 'UTF-8';
    case Utf7 = 'UTF-7';
    case Utf7Imap = 'UTF7-IMAP';
    case Ascii = 'ASCII';
    case EucJp = 'EUC-JP';
    case Sjis = 'SJIS';
    case EucjpWin = 'eucJP-win';
    case EucJp2004 = 'EUC-JP-2004';
    case SjisMobileDocomo = 'SJIS-Mobile#DOCOMO';
    case SjisMobileKddi = 'SJIS-Mobile#KDDI';
    case SjisMobileSoftbank = 'SJIS-Mobile#SOFTBANK';
    case SjisMac = 'SJIS-mac';
    case Sjis2004 = 'SJIS-2004';
    case Utf8MobileDocomo = 'UTF-8-Mobile#DOCOMO';
    case Utf8MobileKddiA = 'UTF-8-Mobile#KDDI-A';
    case Utf8MobileKddiB = 'UTF-8-Mobile#KDDI-B';
    case Utf8MobileSoftbank = 'UTF-8-Mobile#SOFTBANK';
    case Cp932 = 'CP932';
    case Cp51932 = 'CP51932';
    case Jis = 'JIS';
    case Iso2022Jp = 'ISO-2022-JP';
    case Iso2022JpMs = 'ISO-2022-JP-MS';
    case Gb18030 = 'GB18030';
    case Windows1252 = 'Windows-1252';
    case Windows1254 = 'Windows-1254';
    case Iso88591 = 'ISO-8859-1';
    case Iso88592 = 'ISO-8859-2';
    case Iso88593 = 'ISO-8859-3';
    case Iso88594 = 'ISO-8859-4';
    case Iso88595 = 'ISO-8859-5';
    case Iso88596 = 'ISO-8859-6';
    case Iso88597 = 'ISO-8859-7';
    case Iso88598 = 'ISO-8859-8';
    case Iso88599 = 'ISO-8859-9';
    case Iso885910 = 'ISO-8859-10';
    case Iso885913 = 'ISO-8859-13';
    case Iso885914 = 'ISO-8859-14';
    case Iso885915 = 'ISO-8859-15';
    case Iso885916 = 'ISO-8859-16';
    case EucCn = 'EUC-CN';
    case Cp936 = 'CP936';
    case Hz = 'HZ';
    case EucTw = 'EUC-TW';
    case Big5 = 'BIG-5';
    case Cp950 = 'CP950';
    case EucKr = 'EUC-KR';
    case Uhc = 'UHC';
    case Iso2022Kr = 'ISO-2022-KR';
    case Windows1251 = 'Windows-1251';
    case Cp866 = 'CP866';
    case Koi8R = 'KOI8-R';
    case Koi8U = 'KOI8-U';
    case Armscii8 = 'ArmSCII-8';
    case Cp850 = 'CP850';
    case Iso2022Jp2004 = 'ISO-2022-JP-2004';
    case Iso2022JpMobileKodi = 'ISO-2022-JP-MOBILE#KDDI';
    case Cp50220 = 'CP50220';
    case Cp50221 = 'CP50221';
    case Cp50222 = 'CP50222';

    /**
     * Provides the default character encoding.
     *
     * UTF-8 is selected as the default encoding because of its ability to represent any character in the Unicode
     * standard and its compatibility with ASCII. It's a practical choice for web and application development,
     * offering flexibility for handling international text and ensuring data integrity across diverse systems.
     *
     * @return static The UTF-8 encoding instance, representing the default encoding choice for string operations.
     *
     * @pure
     */
    #[\Override]
    public static function default(): static
    {
        return self::Utf8;
    }
}
