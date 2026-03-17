<?php

declare(strict_types=1);

namespace Psl\IRI\Internal;

use Psl\IRI\Exception\InvalidIRIException;
use Psl\Punycode;

use function explode;
use function implode;
use function mb_ord;
use function mb_strlen;
use function mb_strtolower;
use function mb_substr;
use function preg_match;
use function str_ends_with;
use function str_starts_with;
use function strlen;
use function strtolower;
use function substr;

/**
 * RFC 5891/5892 IDNA 2008 support.
 *
 * Handles conversion between internationalized (Unicode) domain names and
 * their ASCII-compatible encoding (ACE) form using Punycode (RFC 3492).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5891
 * @link https://datatracker.ietf.org/doc/html/rfc5892
 *
 * @internal
 */
final readonly class IDNA
{
    private const string ACE_PREFIX = 'xn--';

    /**
     * Convert a Unicode domain name to ASCII form per RFC 5891.
     *
     * Processes each label independently, applying Punycode encoding only to
     * labels that contain non-ASCII characters.
     *
     * @param non-empty-string $domain
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5891#section-4
     *
     * @throws Punycode\Exception\EncodingException If Punycode encoding fails.
     * @throws InvalidIRIException If a label is invalid per IDNA rules.
     *
     * @return non-empty-string
     */
    public static function toASCII(string $domain): string
    {
        $labels = explode('.', $domain);
        $asciiLabels = [];

        foreach ($labels as $label) {
            if ($label === '') {
                $asciiLabels[] = '';
                continue;
            }

            if (!preg_match('/[\x80-\xFF]/', $label)) {
                $asciiLabels[] = strtolower($label);
                continue;
            }

            self::validateLabel($label);

            $encoded = Punycode\encode(mb_strtolower($label));
            $asciiLabels[] = self::ACE_PREFIX . $encoded;
        }

        /** @var non-empty-string */
        return implode('.', $asciiLabels);
    }

    /**
     * Convert an ASCII domain name to Unicode form per RFC 5891.
     *
     * Processes each label independently, decoding Punycode labels (those
     * starting with "xn--") to their Unicode representation.
     *
     * @param non-empty-string $domain
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5891#section-4
     *
     * @throws Punycode\Exception\EncodingException If Punycode decoding fails.
     *
     * @return non-empty-string
     */
    public static function toUnicode(string $domain): string
    {
        $labels = explode('.', $domain);
        $unicodeLabels = [];

        foreach ($labels as $label) {
            if (str_starts_with(strtolower($label), self::ACE_PREFIX)) {
                $punycode = substr($label, strlen(self::ACE_PREFIX));
                $unicodeLabels[] = Punycode\decode($punycode);
            } else {
                $unicodeLabels[] = $label;
            }
        }

        /** @var non-empty-string */
        return implode('.', $unicodeLabels);
    }

    /**
     * Validate a domain label per IDNA 2008 rules.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5892
     *
     * @throws InvalidIRIException If the label contains invalid characters.
     */
    private static function validateLabel(string $label): void
    {
        $normalized = mb_strtolower($label);

        if (str_starts_with($normalized, '-') || str_ends_with($normalized, '-')) {
            throw InvalidIRIException::forInvalidIDNALabel($label);
        }

        $length = mb_strlen($normalized);
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($normalized, $i, 1);
            $cp = mb_ord($char);

            if ($cp < 0x20 || $cp >= 0x7F && $cp <= 0x9F) {
                throw InvalidIRIException::forInvalidIDNALabel($label);
            }

            if ($cp === 0xFFFD || $cp === 0xFFFE || $cp === 0xFFFF) {
                throw InvalidIRIException::forInvalidIDNALabel($label);
            }

            if ($cp >= 0xD800 && $cp <= 0xDFFF) {
                throw InvalidIRIException::forInvalidIDNALabel($label);
            }
        }
    }
}
