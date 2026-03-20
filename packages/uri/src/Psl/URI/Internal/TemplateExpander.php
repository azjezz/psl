<?php

declare(strict_types=1);

namespace Psl\URI\Internal;

use function array_is_list;
use function array_key_exists;
use function ctype_xdigit;
use function dechex;
use function implode;
use function is_array;
use function is_string;
use function mb_substr;
use function ord;
use function str_contains;
use function str_pad;
use function strlen;
use function strtoupper;

use const STR_PAD_LEFT;

/**
 * RFC 6570 URI Template expansion engine.
 *
 * Supports all four levels of template expansion:
 * - Level 1: Simple string expansion {var}
 * - Level 2: Reserved {+var}, Fragment {#var}
 * - Level 3: Label {.var}, Path {/var}, Parameter {;var}, Query {?var}, Continuation {&var}
 * - Level 4: Prefix {var:3}, Explode {var*}
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6570
 *
 * @internal
 *
 * @mago-expect lint:cyclomatic-complexity
 */
final class TemplateExpander
{
    /**
     * Expand a parsed template with the given variables.
     *
     * @param list<string|array{operator: string, variables: list<array{name: string, modifier: string, prefix: null|int}>}> $parts
     * @param array<string, null|string|int|float|list<string>|array<string, string>> $variables
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6570#section-3
     */
    public static function expand(array $parts, array $variables): string
    {
        $result = '';

        foreach ($parts as $part) {
            if (is_string($part)) {
                $result .= $part;
            } else {
                $result .= self::expandExpression($part['operator'], $part['variables'], $variables);
            }
        }

        return $result;
    }

    /**
     * Expand a single expression.
     *
     * @param list<array{name: string, modifier: string, prefix: null|int}> $varSpecs
     * @param array<string, null|string|int|float|list<string>|array<string, string>> $variables
     */
    private static function expandExpression(string $operator, array $varSpecs, array $variables): string
    {
        $first = self::getFirst($operator);
        $separator = self::getSeparator($operator);
        $named = self::isNamed($operator);
        $ifEmpty = self::getIfEmpty($operator);
        $allowReserved = self::allowReserved($operator);

        $parts = [];
        foreach ($varSpecs as $varSpec) {
            $name = $varSpec['name'];
            $modifier = $varSpec['modifier'];
            $prefix = $varSpec['prefix'];

            if (!array_key_exists($name, $variables)) {
                continue;
            }

            $value = $variables[$name];

            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $expanded = self::expandArray($name, $value, $modifier, $named, $ifEmpty, $separator, $allowReserved);
                if ($expanded !== null) {
                    $parts[] = $expanded;
                }
            } else {
                $stringValue = (string) $value;
                if ($prefix !== null && $modifier === ':') {
                    /** @var non-negative-int $prefix */
                    $stringValue = mb_substr($stringValue, 0, $prefix);
                }

                $encoded = self::encodeValue($stringValue, $allowReserved);

                if ($named) {
                    if ($encoded === '' && $ifEmpty !== '=') {
                        $parts[] = self::encodeValue($name, $allowReserved);
                    } else {
                        $parts[] = self::encodeValue($name, $allowReserved) . '=' . $encoded;
                    }
                } else {
                    $parts[] = $encoded;
                }
            }
        }

        if ($parts === []) {
            return '';
        }

        return $first . implode($separator, $parts);
    }

    /**
     * Expand an array value per RFC 6570.
     *
     * @param list<string>|array<string, string> $value
     */
    private static function expandArray(
        string $name,
        array $value,
        string $modifier,
        bool $named,
        string $ifEmpty,
        string $separator,
        bool $allowReserved,
    ): null|string {
        if ($value === []) {
            return null;
        }

        if ($modifier === '*') {
            $parts = [];
            if (array_is_list($value)) {
                foreach ($value as $item) {
                    $encoded = self::encodeValue($item, $allowReserved);
                    if ($named) {
                        $parts[] = self::formatNamed($name, $encoded, $ifEmpty, $allowReserved);
                    } else {
                        $parts[] = $encoded;
                    }
                }
            } else {
                /** @var array<string, string> $value */
                foreach ($value as $key => $item) {
                    $encodedKey = self::encodeValue((string) $key, $allowReserved);
                    $encodedValue = self::encodeValue($item, $allowReserved);
                    $parts[] = $encodedKey . '=' . $encodedValue;
                }
            }

            return implode($separator, $parts);
        }

        $parts = [];
        if (array_is_list($value)) {
            foreach ($value as $item) {
                $parts[] = self::encodeValue($item, $allowReserved);
            }
        } else {
            /** @var array<string, string> $value */
            foreach ($value as $key => $item) {
                $parts[] = self::encodeValue((string) $key, $allowReserved);
                $parts[] = self::encodeValue($item, $allowReserved);
            }
        }

        $composite = implode(',', $parts);

        if ($named) {
            $encodedName = self::encodeValue($name, $allowReserved);
            if ($composite === '' && $ifEmpty !== '=') {
                return $encodedName;
            }

            return $encodedName . '=' . $composite;
        }

        return $composite;
    }

    /**
     * Encode a value for template expansion.
     */
    private static function encodeValue(string $value, bool $allowReserved): string
    {
        if ($allowReserved) {
            return self::encodeReservedAllowed($value);
        }

        return self::encodeUnreservedOnly($value);
    }

    /**
     * Encode a value allowing only unreserved characters.
     */
    private static function encodeUnreservedOnly(string $value): string
    {
        return PercentEncoder::encode($value);
    }

    /**
     * Encode a value allowing unreserved + reserved characters.
     */
    private static function encodeReservedAllowed(string $value): string
    {
        $result = '';
        $length = strlen($value);

        for ($i = 0; $i < $length; $i++) {
            $char = $value[$i];
            $ord = ord($char);

            if ($char === '%' && ($i + 2) < $length && ctype_xdigit($value[$i + 1]) && ctype_xdigit($value[$i + 2])) {
                $result .= '%' . strtoupper($value[$i + 1] . $value[$i + 2]);
                $i += 2;
                continue;
            }

            if (
                $ord >= 0x41 && $ord <= 0x5A
                || $ord >= 0x61 && $ord <= 0x7A
                || $ord >= 0x30 && $ord <= 0x39
                || $char === '-'
                || $char === '.'
                || $char === '_'
                || $char === '~'
            ) {
                $result .= $char;
                continue;
            }

            if (str_contains(':/?#[]@!$&\'()*+,;=', $char)) {
                $result .= $char;
                continue;
            }

            $result .= '%' . strtoupper(str_pad(dechex(ord($char)), 2, '0', STR_PAD_LEFT));
        }

        return $result;
    }

    /**
     * Format a named variable expansion (e.g. "name=value" or just "name" when empty).
     */
    private static function formatNamed(string $name, string $encoded, string $ifEmpty, bool $allowReserved): string
    {
        $encodedName = self::encodeValue($name, $allowReserved);
        if ($encoded === '' && $ifEmpty !== '=') {
            return $encodedName;
        }

        return $encodedName . '=' . $encoded;
    }

    /**
     * Get the first character prefix for an operator.
     */
    private static function getFirst(string $operator): string
    {
        return match ($operator) {
            '#' => '#',
            '.' => '.',
            '/' => '/',
            ';' => ';',
            '?' => '?',
            '&' => '&',
            default => '',
        };
    }

    /**
     * Get the separator for an operator.
     */
    private static function getSeparator(string $operator): string
    {
        return match ($operator) {
            '.' => '.',
            '/' => '/',
            ';' => ';',
            '?', '&' => '&',
            default => ',',
        };
    }

    /**
     * Whether the operator uses named expansion.
     */
    private static function isNamed(string $operator): bool
    {
        return $operator === ';' || $operator === '?' || $operator === '&';
    }

    /**
     * Get the if-empty string for an operator.
     */
    private static function getIfEmpty(string $operator): string
    {
        return $operator === '?' || $operator === '&' ? '=' : '';
    }

    /**
     * Whether the operator allows reserved characters.
     */
    private static function allowReserved(string $operator): bool
    {
        return $operator === '+' || $operator === '#';
    }
}
