<?php

declare(strict_types=1);

namespace Psl\MIME;

use Closure;
use Psl\MIME\Internal\MediaTypeParser;
use Stringable;

use function is_numeric;
use function number_format;
use function ord;
use function round;
use function rtrim;
use function str_contains;
use function strlen;
use function strtolower;

/**
 * Represents a media type range used for content negotiation (e.g. "text/*", "*\/*").
 *
 * Unlike {@see MediaType}, this class permits wildcards in the type and/or subtype
 * components. A range expresses a set of media types that a consumer is willing to
 * accept, as defined by RFC 9110 §12.5.1.
 *
 * The weight (quality value) represents the relative preference for this range,
 * from 0.0 (not acceptable) to 1.0 (most preferred). It corresponds to the `q`
 * parameter in Accept headers but is NOT stored in the parameters collection.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-12.5.1
 *
 * @api
 */
final readonly class MediaRange implements Stringable
{
    /**
     * The top-level type, or "*" for a wildcard matching any type.
     *
     * @var non-empty-lowercase-string
     */
    public string $type;

    /**
     * The subtype, or "*" for a wildcard matching any subtype.
     *
     * @var non-empty-lowercase-string
     */
    public string $subtype;

    /**
     * Media type parameters (excluding the quality value `q`).
     */
    public Parameters $parameters;

    /**
     * @var float Quality value between 0.0 and 1.0 inclusive.
     */
    public float $weight;

    /**
     * Construct a new media range.
     *
     * Wildcards are permitted for the type and/or subtype. A wildcard type requires
     * a wildcard subtype (i.e. a type of "*" with a concrete subtype is invalid).
     *
     * @param string $type The top-level type or "*" for wildcard.
     * @param string $subtype The subtype or "*" for wildcard.
     * @param float $weight Quality value between 0.0 (not acceptable) and 1.0 (most preferred).
     *
     * @throws Exception\InvalidMediaTypeComponentException If the type, subtype, or weight is invalid.
     */
    public function __construct(string $type, string $subtype, null|Parameters $parameters = null, float $weight = 1.0)
    {
        $type = strtolower($type);
        $subtype = strtolower($subtype);

        if ($type !== '*') {
            self::validateComponent($type, static function () use ($type): never {
                throw Exception\InvalidMediaTypeComponentException::forType($type);
            });
        }

        if ($subtype !== '*') {
            self::validateComponent($subtype, static function () use ($subtype): never {
                throw Exception\InvalidMediaTypeComponentException::forSubtype($subtype);
            });
        }

        if ($type === '*' && $subtype !== '*') {
            throw Exception\InvalidMediaTypeComponentException::forType($type);
        }

        if ($weight < 0.0 || $weight > 1.0) {
            throw Exception\InvalidMediaTypeComponentException::forWeight($weight);
        }

        $this->type = $type; // @mago-expect analysis:invalid-property-assignment-value
        $this->subtype = $subtype; // @mago-expect analysis:invalid-property-assignment-value
        $this->parameters = $parameters ?? Parameters::default();
        $this->weight = $weight;
    }

    /**
     * Parse a media range string as found in an Accept header.
     *
     * The `q` parameter, if present, is extracted as the weight and not included in the
     * parameters collection. If `q` is absent, the weight defaults to 1.0.
     *
     * @param string $input The media range string to parse (e.g. "text/*; q=0.8").
     *
     * @throws Exception\ParsingException If the input is malformed.
     * @throws Exception\InvalidMediaTypeComponentException If the type or subtype is invalid.
     */
    public static function parse(string $input): self
    {
        [$type, $subtype, $params] = MediaTypeParser::parse($input, allowWildcards: true);

        $weight = 1.0;
        $filtered = [];
        foreach ($params as [$name, $value]) {
            if (strtolower($name) === 'q') {
                if (!self::isValidWeight($value)) {
                    throw Exception\InvalidMediaTypeComponentException::forWeight(0.0);
                }

                /** @var numeric-string $value */
                $weight = round((float) $value, 3);

                continue;
            }

            $filtered[] = [$name, $value];
        }

        $parameters = $filtered !== [] ? Parameters::fromPairs($filtered) : null;

        return new self($type, $subtype, $parameters, $weight);
    }

    /**
     * Create an exact-match range from an existing {@see MediaType}.
     *
     * The resulting range will match only the given media type's type and subtype,
     * and carry over its parameters.
     *
     * @param float $weight Quality value between 0.0 and 1.0 inclusive.
     *
     * @throws Exception\InvalidMediaTypeComponentException If the weight is out of range.
     */
    public static function fromMediaType(MediaType $mediaType, float $weight = 1.0): self
    {
        return new self($mediaType->type, $mediaType->subtype, $mediaType->parameters, $weight);
    }

    /**
     * Check whether this range matches the given media type.
     *
     * Parameters are ignored during matching.
     */
    public function matches(MediaType $mediaType): bool
    {
        if ($this->type === '*') {
            return true;
        }

        if ($this->type !== $mediaType->type) {
            return false;
        }

        if ($this->subtype === '*') {
            return true;
        }

        return $this->subtype === $mediaType->subtype;
    }

    /**
     * Get the specificity score for sorting.
     *
     * Higher = more specific: exact (3), type/* (2), *\/* (1).
     *
     * @return int<1, 3>
     */
    public function specificity(): int
    {
        if ($this->type === '*') {
            return 1;
        }

        if ($this->subtype === '*') {
            return 2;
        }

        return 3;
    }

    /**
     * Get the essence (type/subtype without parameters).
     *
     * @return non-empty-lowercase-string
     */
    public function essence(): string
    {
        /** @var non-empty-lowercase-string */
        return $this->type . '/' . $this->subtype;
    }

    /**
     * Serialize the media range to its string representation, including parameters and quality value.
     *
     * The `q` parameter is appended only when the weight is not 1.0.
     *
     * @return non-empty-string
     */
    public function toString(): string
    {
        $result = $this->essence() . $this->parameters->toString();

        if ($this->weight !== 1.0) {
            $result .= '; q=' . self::formatWeight($this->weight);
        }

        return $result;
    }

    /**
     * Serialize the media range to its string representation.
     *
     * @see self::toString()
     *
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param Closure(): never $throw
     *
     * @psalm-assert =non-empty-lowercase-string $component
     */
    private static function validateComponent(string $component, Closure $throw): void
    {
        if ($component === '' || strlen($component) > 127) {
            $throw();
        }

        for ($i = 0, $len = strlen($component); $i < $len; $i++) {
            $ord = ord($component[$i]);

            $valid =
                $ord >= 0x61 && $ord <= 0x7A || // a-z
                $ord >= 0x30 && $ord <= 0x39 || // 0-9
                str_contains('!#$&-^_.+', $component[$i]);

            if (!$valid) {
                $throw();
            }
        }
    }

    /**
     * Check whether a string is a valid quality weight value between 0.0 and 1.0.
     */
    private static function isValidWeight(string $value): bool
    {
        if ($value === '' || !is_numeric($value)) {
            return false;
        }

        $float = (float) $value;

        return $float >= 0.0 && $float <= 1.0;
    }

    /**
     * Format a quality weight as a decimal string with up to 3 significant digits.
     *
     * Trailing zeros and a trailing decimal point are stripped (e.g. 0.500 becomes "0.5").
     */
    private static function formatWeight(float $weight): string
    {
        $formatted = number_format($weight, 3, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }
}
