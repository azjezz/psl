<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

/**
 * Thrown when a {@see \Psl\MIME\MediaType} is constructed or modified with
 * an invalid component value (type, subtype, parameter name, or quality weight).
 *
 * @api
 */
final class InvalidMediaTypeComponentException extends InvalidArgumentException
{
    /**
     * Create an exception for an invalid top-level media type (e.g., containing illegal characters).
     */
    public static function forType(string $type): self
    {
        return new self('Invalid media type: "' . $type . '".');
    }

    /**
     * Create an exception for an invalid media subtype (e.g., containing illegal characters).
     */
    public static function forSubtype(string $subtype): self
    {
        return new self('Invalid media subtype: "' . $subtype . '".');
    }

    /**
     * Create an exception for an invalid MIME parameter name (e.g., containing whitespace or special characters).
     */
    public static function forParameterName(string $name): self
    {
        return new self('Invalid parameter name: "' . $name . '".');
    }

    /**
     * Create an exception for a quality weight outside the valid range.
     *
     * @param float $weight The invalid weight; must be between 0.0 and 1.0 inclusive.
     */
    public static function forWeight(float $weight): self
    {
        return new self('Invalid quality weight: ' . $weight . '. Must be between 0.0 and 1.0.');
    }
}
