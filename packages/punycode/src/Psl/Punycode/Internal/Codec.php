<?php

declare(strict_types=1);

namespace Psl\Punycode\Internal;

use Psl\Punycode\Exception\EncodingException;

use function array_merge;
use function array_slice;
use function array_unique;
use function array_values;
use function chr;
use function count;
use function implode;
use function intdiv;
use function mb_chr;
use function mb_ord;
use function mb_strlen;
use function mb_substr;
use function ord;
use function sort;
use function strlen;
use function strrpos;
use function substr;

/**
 * RFC 3492 Punycode bootstring codec.
 *
 * Parameters per RFC 3492 Section 5:
 *   base=36, tmin=1, tmax=26, skew=38, damp=700,
 *   initial_bias=72, initial_n=128
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3492
 *
 * @internal
 *
 * @mago-expect lint:excessive-nesting
 */
final class Codec
{
    private const int BASE = 36;
    private const int TMIN = 1;
    private const int TMAX = 26;
    private const int SKEW = 38;
    private const int DAMP = 700;
    private const int INITIAL_BIAS = 72;
    private const int INITIAL_N = 128;

    /**
     * Encode a Unicode string to Punycode.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3492#section-6.3
     *
     * @throws EncodingException If encoding overflows or the input is invalid.
     */
    public static function encode(string $input): string
    {
        $codePoints = self::toCodePoints($input);
        $n = self::INITIAL_N;
        $delta = 0;
        $bias = self::INITIAL_BIAS;

        $basic = [];
        $nonBasic = [];
        foreach ($codePoints as $cp) {
            if ($cp < 128) {
                $basic[] = chr($cp);
            }

            if ($cp >= $n) {
                $nonBasic[] = $cp;
            }
        }

        $output = implode('', $basic);
        $handled = count($basic);
        $totalPoints = count($codePoints);

        if ($handled > 0 && $handled < $totalPoints) {
            $output .= '-';
        }

        $nonBasic = array_values(array_unique($nonBasic));
        sort($nonBasic);

        foreach ($nonBasic as $m) {
            $delta += ($m - $n) * ($handled + 1);

            if ($delta < 0) {
                throw EncodingException::forOverflow();
            }

            $n = $m;

            foreach ($codePoints as $cp) {
                if ($cp < $n) {
                    $delta++;
                } elseif ($cp === $n) {
                    $q = $delta;
                    for ($k = self::BASE;; $k += self::BASE) {
                        $t = self::threshold($k, $bias);
                        if ($q < $t) {
                            break;
                        }

                        $output .= self::digitToChar($t + (($q - $t) % (self::BASE - $t)));
                        $q = intdiv($q - $t, self::BASE - $t);
                    }

                    $output .= self::digitToChar($q);
                    $bias = self::adapt($delta, $handled + 1, $handled === count($basic));
                    $delta = 0;
                    $handled++;
                }
            }

            $delta++;
            $n++;
        }

        return $output;
    }

    /**
     * Decode a Punycode string to Unicode.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3492#section-6.2
     *
     * @throws EncodingException If the input is malformed or decoding overflows.
     */
    public static function decode(string $input): string
    {
        $n = self::INITIAL_N;
        $bias = self::INITIAL_BIAS;
        $i = 0;

        $lastDelim = strrpos($input, '-');
        $basic = $lastDelim !== false ? substr($input, 0, $lastDelim) : '';
        $encoded = $lastDelim !== false ? substr($input, $lastDelim + 1) : $input;

        /** @var list<int> $output */
        $output = [];
        for ($j = 0; $j < strlen($basic); $j++) {
            $output[] = ord($basic[$j]);
        }

        $pos = 0;
        $encodedLen = strlen($encoded);

        while ($pos < $encodedLen) {
            $oldi = $i;
            $w = 1;

            for ($k = self::BASE;; $k += self::BASE) {
                if ($pos >= $encodedLen) {
                    throw EncodingException::forBadEncoding($input);
                }

                $digit = self::charToDigit($encoded[$pos]);
                if ($digit === null) {
                    throw EncodingException::forInvalidInput('invalid character "' . $encoded[$pos] . '"');
                }

                $pos++;

                $i += $digit * $w;
                if ($i < 0) {
                    throw EncodingException::forOverflow();
                }

                $t = self::threshold($k, $bias);
                if ($digit < $t) {
                    break;
                }

                $w *= self::BASE - $t;
                if ($w < 0) {
                    throw EncodingException::forOverflow();
                }
            }

            $outputLen = count($output) + 1;
            $bias = self::adapt($i - $oldi, $outputLen, $oldi === 0);
            $n += intdiv($i, $outputLen);
            $i %= $outputLen;

            /** @var non-negative-int $i */
            $before = array_slice($output, 0, $i);
            $after = array_slice($output, $i);
            $output = array_merge($before, [$n], $after);
            $i++;
        }

        return self::fromCodePoints($output);
    }

    /**
     * Bias adaptation function per RFC 3492 Section 6.1.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3492#section-6.1
     */
    private static function adapt(int $delta, int $numpoints, bool $firstTime): int
    {
        $delta = $firstTime ? intdiv($delta, self::DAMP) : intdiv($delta, 2);
        $delta += intdiv($delta, $numpoints);

        $k = 0;
        while ($delta > intdiv((self::BASE - self::TMIN) * self::TMAX, 2)) {
            $delta = intdiv($delta, self::BASE - self::TMIN);
            $k += self::BASE;
        }

        return $k + intdiv((self::BASE - self::TMIN + 1) * $delta, $delta + self::SKEW);
    }

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc3492#section-6.1
     */
    private static function threshold(int $k, int $bias): int
    {
        if ($k <= ($bias + self::TMIN)) {
            return self::TMIN;
        }

        if ($k >= ($bias + self::TMAX)) {
            return self::TMAX;
        }

        return $k - $bias;
    }

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc3492#section-5
     */
    private static function digitToChar(int $digit): string
    {
        if ($digit < 26) {
            return chr($digit + ord('a'));
        }

        return chr($digit - 26 + ord('0'));
    }

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc3492#section-5
     */
    private static function charToDigit(string $char): null|int
    {
        $ord = ord($char);
        if ($ord >= ord('a') && $ord <= ord('z')) {
            return $ord - ord('a');
        }

        if ($ord >= ord('A') && $ord <= ord('Z')) {
            return $ord - ord('A');
        }

        if ($ord >= ord('0') && $ord <= ord('9')) {
            return $ord - ord('0') + 26;
        }

        return null;
    }

    /**
     * @return list<int>
     */
    private static function toCodePoints(string $input): array
    {
        $points = [];
        $length = mb_strlen($input);
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($input, $i, 1);
            $points[] = mb_ord($char);
        }

        return $points;
    }

    /**
     * @param list<int> $codePoints
     */
    private static function fromCodePoints(array $codePoints): string
    {
        $result = '';
        foreach ($codePoints as $cp) {
            $result .= (string) mb_chr($cp);
        }

        return $result;
    }
}
