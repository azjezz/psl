<?php

declare(strict_types=1);

namespace Psl\DNS\Internal;

use function array_reverse;
use function count;
use function explode;
use function min;
use function strpos;
use function strtolower;
use function substr;

/**
 * DNS name utilities for canonical ordering and ancestor computation.
 *
 * @internal
 */
final class DNSName
{
    /**
     * Case-insensitive DNS name comparison.
     */
    public static function caselessEquals(string $a, string $b): bool
    {
        return strtolower($a) === strtolower($b);
    }

    /**
     * Canonical DNS name ordering per RFC 4034 Section 6.1.
     *
     * Names are compared label-by-label from the rightmost label.
     */
    public static function canonicalOrder(string $a, string $b): int
    {
        $labelsA = $a !== '.' && $a !== '' ? explode('.', strtolower($a)) : [];
        $labelsB = $b !== '.' && $b !== '' ? explode('.', strtolower($b)) : [];

        $labelsA = array_reverse($labelsA);
        $labelsB = array_reverse($labelsB);

        $countA = count($labelsA);
        $countB = count($labelsB);
        $minCount = min($countA, $countB);

        for ($i = 0; $i < $minCount; $i++) {
            $cmp = $labelsA[$i] <=> $labelsB[$i];
            if ($cmp !== 0) {
                return $cmp;
            }
        }

        return $countA <=> $countB;
    }

    /**
     * Get all ancestor names from most specific to root.
     *
     * @return list<string>
     */
    public static function getAncestors(string $name): array
    {
        if ($name === '' || $name === '.') {
            return ['.'];
        }

        $ancestors = [$name];
        $current = $name;

        while (true) {
            $dotPos = strpos($current, '.');
            if ($dotPos === false) {
                $ancestors[] = '.';
                break;
            }

            $current = substr($current, $dotPos + 1);
            $ancestors[] = $current;
        }

        return $ancestors;
    }

    /**
     * Get the parent name of a domain.
     */
    public static function getParentName(string $name): string
    {
        if ($name === '' || $name === '.') {
            return '.';
        }

        $dotPos = strpos($name, '.');
        if ($dotPos === false) {
            return '.';
        }

        return substr($name, $dotPos + 1);
    }
}
