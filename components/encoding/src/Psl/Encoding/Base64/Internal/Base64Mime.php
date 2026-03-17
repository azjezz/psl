<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64\Internal;

use Override;
use Psl\Encoding\Exception;

use function str_replace;
use function strlen;
use function substr;

/**
 * MIME Base64 encoding per RFC 2045.
 *
 * Uses the standard Base64 alphabet but wraps output at 76 characters
 * with CRLF line endings. On decode, all whitespace is stripped before
 * processing.
 *
 * @internal
 */
final class Base64Mime extends Base64
{
    private const int LINE_LENGTH = 76;

    private const string LINE_ENDING = "\r\n";

    /**
     * @pure
     */
    #[Override]
    public static function encode(string $binary, bool $padding = true): string
    {
        $raw = parent::encode($binary, $padding);
        $rawLength = strlen($raw);

        if ($rawLength <= self::LINE_LENGTH) {
            return $raw;
        }

        $result = '';
        for ($i = 0; $i < $rawLength; $i += self::LINE_LENGTH) {
            if ($i > 0) {
                $result .= self::LINE_ENDING;
            }

            $result .= substr($raw, $i, self::LINE_LENGTH);
        }

        return $result;
    }

    /**
     * @pure
     *
     * @throws Exception\RangeException If the encoded string contains characters outside
     *                                  the base64 characters range.
     * @throws Exception\IncorrectPaddingException If the encoded string has an incorrect padding.
     */
    #[Override]
    public static function decode(string $base64, bool $explicitPadding = true): string
    {
        // Strip all whitespace (CR, LF, space, tab) before decoding
        $base64 = str_replace(["\r", "\n", ' ', "\t"], '', $base64);

        return parent::decode($base64, $explicitPadding);
    }
}
