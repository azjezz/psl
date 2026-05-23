<?php

declare(strict_types=1);

namespace Psl\MIME;

use Closure;
use Psl\MIME\Exception\InvalidMediaTypeComponentException;
use Psl\MIME\Internal\MediaTypeParser;
use Psl\MIME\Internal\Registry\Types;
use Stringable;

use function mb_strtolower;
use function ord;
use function str_contains;
use function str_starts_with;
use function strlen;
use function strpos;
use function strrpos;
use function strtolower;
use function substr;

/**
 * Represents a MIME media type (e.g. "application/json", "text/html; charset=utf-8").
 *
 * Immutable value object. Type and subtype are always lowercased.
 * Suffix and tree are derived from the subtype.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2045
 * @link https://datatracker.ietf.org/doc/html/rfc6838
 *
 * @api
 */
final readonly class MediaType implements Stringable
{
    /**
     * @var non-empty-lowercase-string $type
     *
     * The top-level type (e.g. "application", "text", "image").
     */
    public string $type;

    /**
     * @var non-empty-lowercase-string $subtype
     *
     * The subtype (e.g. "json", "html", "vnd.company.app+xml").
     */
    public string $subtype;

    /**
     * The structured syntax suffix (e.g. "json" from "vnd.api+json"), or "" if none.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6838#section-4.2.8
     */
    public string $suffix;

    /**
     * The registration tree prefix (e.g. "vnd" from "vnd.company.app"), or "" for standards tree.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6838#section-3
     */
    public string $tree;

    /**
     * The parameters associated with this media type.
     */
    public Parameters $parameters;

    /**
     * Construct a new media type with the given type, subtype, and optional parameters.
     *
     * The type and subtype are lowercased and validated against RFC 6838 token rules.
     * The suffix and tree are automatically derived from the subtype.
     *
     * @param string $type The top-level type (e.g. "application", "text").
     * @param string $subtype The subtype (e.g. "json", "vnd.api+xml").
     *
     * @throws InvalidMediaTypeComponentException If the type or subtype is empty, exceeds 127 characters, or contains invalid characters.
     */
    public function __construct(string $type, string $subtype, null|Parameters $parameters = null)
    {
        $type = strtolower($type);
        $subtype = strtolower($subtype);

        self::validateComponent($type, static function () use ($type): never {
            throw InvalidMediaTypeComponentException::forType($type);
        });

        self::validateComponent($subtype, static function () use ($subtype): never {
            throw InvalidMediaTypeComponentException::forSubtype($subtype);
        });

        $this->type = $type;
        $this->subtype = $subtype;
        $this->parameters = $parameters ?? Parameters::default();
        $this->suffix = self::extractSuffix($subtype);
        $this->tree = self::extractTree($subtype);
    }

    /**
     * Look up the media type for a file extension.
     *
     * The extension is matched case-insensitively without the leading dot
     * (e.g. "json", "html", "svg").
     *
     * Returns null if the extension is not found in the IANA registry.
     *
     * @param string $extension The file extension without the leading dot.
     *
     * @throws Exception\MediaTypeParsingException If the registered type string is malformed.
     * @throws InvalidMediaTypeComponentException If the registered type has invalid components.
     */
    public static function fromExtension(string $extension): null|self
    {
        $essence = Types::EXTENSION_TO_TYPE[mb_strtolower($extension)] ?? null;
        if ($essence === null) {
            return null;
        }

        return self::parse($essence);
    }

    /**
     * Parse a MIME media type string into a {@see MediaType} instance.
     *
     * Accepts the full media type syntax including parameters (e.g. "text/html; charset=utf-8").
     *
     * @param string $input The media type string to parse.
     *
     * @throws Exception\ParsingException If the input is malformed.
     * @throws InvalidMediaTypeComponentException If the type or subtype is invalid.
     */
    public static function parse(string $input): self
    {
        [$type, $subtype, $params] = MediaTypeParser::parse($input);

        $parameters = $params !== [] ? Parameters::fromPairs($params) : null;

        return new self($type, $subtype, $parameters);
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
     * Get all file extensions associated with this media type.
     *
     * Returns an empty array if the media type has no known extensions in the
     * IANA registry. Only the type/subtype essence is used for lookup;
     * parameters are ignored. Extensions are returned without the leading dot.
     *
     * @return list<non-empty-string>
     */
    public function extensions(): array
    {
        return Types::TYPE_TO_EXTENSIONS[$this->essence()] ?? [];
    }

    /**
     * Check whether this media type is officially registered with IANA.
     *
     * Only the type/subtype essence is checked; parameters are ignored.
     *
     * @link https://www.iana.org/assignments/media-types/media-types.xhtml
     */
    public function isRegistered(): bool
    {
        return isset(Types::REGISTERED[$this->essence()]);
    }

    /**
     * Extract the structured syntax suffix from a subtype.
     *
     * Example: "vnd.api+json" → "json"
     */
    private static function extractSuffix(string $subtype): string
    {
        $pos = strrpos($subtype, '+');
        if ($pos === false || $pos === (strlen($subtype) - 1)) {
            return '';
        }

        return substr($subtype, $pos + 1);
    }

    /**
     * Extract the registration tree prefix from a subtype.
     *
     * Example: "vnd.company.app" → "vnd"
     */
    private static function extractTree(string $subtype): string
    {
        if (str_starts_with($subtype, 'x-')) {
            return 'x';
        }

        $pos = strpos($subtype, '.');
        if ($pos === false || $pos === 0) {
            return '';
        }

        $prefix = substr($subtype, 0, $pos);

        if ($prefix === 'vnd' || $prefix === 'prs' || $prefix === 'x') {
            return $prefix;
        }

        return '';
    }

    /**
     * @param Closure(): never $throw
     *
     * @psalm-assert non-empty-lowercase-string $component
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
     * Serialize the media type to its string representation, including parameters.
     *
     * Example: "text/html; charset=utf-8"
     *
     * @return non-empty-string
     */
    public function toString(): string
    {
        return $this->essence() . $this->parameters->toString();
    }

    /**
     * Serialize the media type to its string representation, including parameters.
     *
     * @see self::toString()
     *
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
