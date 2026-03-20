<?php

declare(strict_types=1);

namespace Psl\URI\Internal;

use function str_contains;
use function str_starts_with;
use function strpos;
use function strrpos;
use function strtolower;
use function substr;

/**
 * URI normalization per RFC 3986 Section 6.
 *
 * Applies:
 * 1. Case normalization — scheme and host lowercased
 * 2. Percent-encoding normalization — unreserved chars decoded, hex uppercased
 * 3. Dot-segment removal — prevents path traversal
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986#section-6
 *
 * @internal
 */
final class Normalizer
{
    /**
     * Lowercase a scheme per RFC 3986 Section 3.1.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-3.1
     *
     * @return ($schema is non-empty-string ? non-empty-lowercase-string : lowercase-string)
     */
    public static function normalizeScheme(string $scheme): string
    {
        return strtolower($scheme);
    }

    /**
     * Lowercase a registered name host per RFC 3986 Section 3.2.2.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-3.2.2
     */
    public static function normalizeHost(string $host): string
    {
        return PercentEncoder::normalize(strtolower($host));
    }

    /**
     * Normalize percent-encoding in a URI component.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-2.1
     */
    public static function normalizeEncoding(string $component): string
    {
        return PercentEncoder::normalize($component);
    }

    /**
     * Remove dot segments from a path per RFC 3986 Section 5.2.4.
     *
     * This is critical for preventing path traversal attacks.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-5.2.4
     */
    public static function removeDotSegments(string $path): string
    {
        if (
            $path === ''
            || $path !== '.' && $path !== '..' && !str_contains($path, '/.') && !str_contains($path, './')
        ) {
            return $path;
        }

        $output = '';
        $outputSegments = 0;
        $input = $path;

        while ($input !== '') {
            if (str_starts_with($input, '../')) {
                $input = substr($input, 3);
                continue;
            }

            if (str_starts_with($input, './')) {
                $input = substr($input, 2);
                continue;
            }

            if (str_starts_with($input, '/./')) {
                $input = '/' . substr($input, 3);
                continue;
            }

            if ($input === '/.') {
                $input = '/';
                continue;
            }

            if (str_starts_with($input, '/../')) {
                $input = '/' . substr($input, 4);
                if ($outputSegments > 0) {
                    $lastSlash = strrpos($output, '/');
                    $output = $lastSlash !== false ? substr($output, 0, $lastSlash) : '';
                    $outputSegments--;
                }

                continue;
            }

            if ($input === '/..') {
                $input = '/';
                if ($outputSegments > 0) {
                    $lastSlash = strrpos($output, '/');
                    $output = $lastSlash !== false ? substr($output, 0, $lastSlash) : '';
                    $outputSegments--;
                }

                continue;
            }

            if ($input === '.' || $input === '..') {
                $input = '';
                continue;
            }

            if (str_starts_with($input, '/')) {
                $nextSlash = strpos($input, '/', 1);
                if ($nextSlash === false) {
                    $output .= $input;
                    $input = '';
                } else {
                    $output .= substr($input, 0, $nextSlash);
                    $input = substr($input, $nextSlash);
                }
            } else {
                $nextSlash = strpos($input, '/');
                if ($nextSlash === false) {
                    $output .= $input;
                    $input = '';
                } else {
                    $output .= substr($input, 0, $nextSlash);
                    $input = substr($input, $nextSlash);
                }
            }

            $outputSegments++;
        }

        return $output;
    }
}
