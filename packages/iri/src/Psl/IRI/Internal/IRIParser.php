<?php

declare(strict_types=1);

namespace Psl\IRI\Internal;

use Psl\IP;
use Psl\IP\Address;
use Psl\IRI\Exception\InvalidIRIException;
use Psl\IRI\IRI;
use Psl\URI\Authority\Authority;
use Psl\URI\Authority\HostInterface;
use Psl\URI\Authority\IPHost;
use Psl\URI\Authority\RegisteredNameHost;
use Psl\URI\Internal\Normalizer;
use Psl\URI\PathKind;

use function mb_ord;
use function mb_strlen;
use function mb_strtolower;
use function mb_substr;
use function preg_match;
use function str_starts_with;
use function strpos;
use function strrpos;
use function substr;

/**
 * RFC 3987 IRI parser.
 *
 * Accepts Unicode input. Validates IRI-specific character ranges (ucschar, iprivate).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3987
 *
 * @internal
 *
 * @mago-expect lint:cyclomatic-complexity
 */
final class IRIParser
{
    /**
     * Parse an IRI string per RFC 3987.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-2.2
     *
     * @throws InvalidIRIException If the input is not a valid IRI.
     */
    public static function parse(string $input): IRI
    {
        $normalized = \Normalizer::normalize($input, \Normalizer::FORM_C);
        if ($normalized !== false) {
            $input = $normalized;
        }

        $matches = [];
        $result = preg_match('/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/su', $input, $matches);
        if (!$result) {
            return new IRI(null, null, '', PathKind::None, null, null);
        }

        /** @var null|non-empty-string $scheme */
        $scheme = ($matches[2] ?? '') !== '' ? $matches[2] : null;
        $hasAuthority = ($matches[3] ?? '') !== '';
        $authorityString = $matches[4] ?? '';
        $path = $matches[5] ?? '';
        $hasQuery = isset($matches[6]) && $matches[6] !== '';
        $query = $hasQuery ? $matches[7] ?? '' : null;
        $hasFragment = isset($matches[8]) && $matches[8] !== '';
        $fragment = $hasFragment ? $matches[9] ?? '' : null;

        if ($scheme !== null) {
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9+\-.]*$/', $scheme)) {
                throw InvalidIRIException::forInvalidIDNALabel($scheme);
            }

            /** @var non-empty-lowercase-string $scheme */
            $scheme = Normalizer::normalizeScheme($scheme);
        }

        self::validateIRICharacters($path, false);
        if ($query !== null) {
            self::validateIRICharacters($query, true);
        }

        if ($fragment !== null) {
            self::validateIRICharacters($fragment, false);
        }

        $authority = $hasAuthority ? self::parseAuthority($authorityString) : null;

        if ($authority !== null || $scheme !== null) {
            $path = Normalizer::removeDotSegments($path);
        }

        $pathKind = PathKind::None;
        if ($path !== '') {
            $pathKind = $path[0] === '/' ? PathKind::Absolute : PathKind::Rootless;
        }

        return new IRI($scheme, $authority, $path, $pathKind, $query, $fragment);
    }

    /**
     * Validate that all non-ASCII characters in a component are valid per RFC 3987.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-2.2
     *
     * @throws InvalidIRIException If characters are invalid.
     */
    private static function validateIRICharacters(string $component, bool $allowPrivateUse): void
    {
        $length = mb_strlen($component);

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($component, $i, 1);
            $cp = mb_ord($char);

            if ($cp < 0x80) {
                continue;
            }

            if (
                $cp >= 0xE000 && $cp <= 0xF8FF
                || $cp >= 0xF_0000 && $cp <= 0xF_FFFD
                || $cp >= 0x10_0000 && $cp <= 0x10_FFFD
            ) {
                if (!$allowPrivateUse) {
                    throw InvalidIRIException::forPrivateUseOutsideQuery();
                }

                continue;
            }

            if (self::isUcsChar($cp)) {
                continue;
            }

            throw InvalidIRIException::forInvalidUnicodeCharacter($cp);
        }
    }

    /**
     * Check if a code point is a valid ucschar per RFC 3987.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-2.2
     */
    private static function isUcsChar(int $cp): bool
    {
        return (
            $cp >= 0x00A0
            && $cp <= 0xD7FF
            || $cp >= 0xF900
            && $cp <= 0xFDCF
            || $cp >= 0xFDF0
            && $cp <= 0xFFEF
            || $cp >= 0x1_0000
            && $cp <= 0x1_FFFD
            || $cp >= 0x2_0000
            && $cp <= 0x2_FFFD
            || $cp >= 0x3_0000
            && $cp <= 0x3_FFFD
            || $cp >= 0x4_0000
            && $cp <= 0x4_FFFD
            || $cp >= 0x5_0000
            && $cp <= 0x5_FFFD
            || $cp >= 0x6_0000
            && $cp <= 0x6_FFFD
            || $cp >= 0x7_0000
            && $cp <= 0x7_FFFD
            || $cp >= 0x8_0000
            && $cp <= 0x8_FFFD
            || $cp >= 0x9_0000
            && $cp <= 0x9_FFFD
            || $cp >= 0xA_0000
            && $cp <= 0xA_FFFD
            || $cp >= 0xB_0000
            && $cp <= 0xB_FFFD
            || $cp >= 0xC_0000
            && $cp <= 0xC_FFFD
            || $cp >= 0xD_0000
            && $cp <= 0xD_FFFD
            || $cp >= 0xE_1000
            && $cp <= 0xE_FFFD
        );
    }

    /**
     * Parse an authority string into its components.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-2.2
     *
     * @throws InvalidIRIException If the authority is malformed.
     */
    private static function parseAuthority(string $authority): Authority
    {
        $userInfo = null;
        $remaining = $authority;

        $atPos = strrpos($remaining, '@');
        if ($atPos !== false) {
            $userInfo = substr($remaining, 0, $atPos);
            $remaining = substr($remaining, $atPos + 1);
        }

        [$host, $port] = self::parseHost($remaining);

        /** @var int<0, 65535>|null $port */
        return new Authority($userInfo, $host, $port);
    }

    /**
     * Parse a host:port string into a host and optional port number.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-2.2
     *
     * @return array{HostInterface, null|int}
     */
    private static function parseHost(string $hostPort): array
    {
        if (str_starts_with($hostPort, '[')) {
            $closeBracket = strpos($hostPort, ']');
            if ($closeBracket === false) {
                /** @var non-empty-string $hostPort */
                return [new RegisteredNameHost($hostPort), null];
            }

            /** @var non-negative-int $bracketLen */
            $bracketLen = $closeBracket - 1;
            $insideBrackets = substr($hostPort, 1, $bracketLen);
            $afterBrackets = substr($hostPort, $closeBracket + 1);

            $port = null;
            if (str_starts_with($afterBrackets, ':')) {
                $portString = substr($afterBrackets, 1);
                if ($portString !== '' && preg_match('/^[0-9]+$/', $portString)) {
                    $port = (int) $portString;
                }
            }

            $zone = null;
            $addressString = $insideBrackets;
            $zonePos = strpos($insideBrackets, '%25');
            if ($zonePos !== false) {
                $addressString = substr($insideBrackets, 0, $zonePos);
                $zone = substr($insideBrackets, $zonePos + 3);
            }

            try {
                /** @var non-empty-string $addressString */
                $address = Address::parse($addressString);
                return [new IPHost($address, $zone), $port];
            } catch (IP\Exception\InvalidArgumentException) {
                return [new RegisteredNameHost('[' . $insideBrackets . ']'), $port];
            }
        }

        $port = null;
        $hostString = $hostPort;
        $lastColon = strrpos($hostPort, ':');
        if ($lastColon !== false) {
            $potentialPort = substr($hostPort, $lastColon + 1);
            if ($potentialPort === '' || preg_match('/^[0-9]+$/', $potentialPort)) {
                $hostString = substr($hostPort, 0, $lastColon);
                if ($potentialPort !== '') {
                    $portValue = (int) $potentialPort;
                    if ($portValue <= 65_535) {
                        $port = $portValue;
                    }
                }
            }
        }

        if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $hostString)) {
            try {
                /** @var non-empty-string $hostString */
                $address = Address::parse($hostString);
                return [new IPHost($address), $port];
            } catch (IP\Exception\InvalidArgumentException) {
                // @mago-expect lint:no-empty-catch-clause - not a valid IP (e.g. 999.999.999.999), fall through to registered name
            }
        }

        $hostString = mb_strtolower($hostString);

        /** @var non-empty-string $hostString */
        return [new RegisteredNameHost($hostString), $port];
    }
}
