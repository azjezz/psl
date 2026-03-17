<?php

declare(strict_types=1);

namespace Psl\IRI\Exception;

use function dechex;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Exception thrown when an IRI string is invalid per RFC 3987.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3987
 */
final class InvalidIRIException extends InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Create an exception for an invalid Unicode code point in an IRI.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-2.2
     */
    public static function forInvalidUnicodeCharacter(int $codepoint): self
    {
        /** @var non-negative-int $codepoint */
        return new self(
            'Invalid Unicode character U+' . str_pad(dechex($codepoint), 4, '0', STR_PAD_LEFT) . ' in IRI.',
        );
    }

    /**
     * Create an exception for private-use characters outside the query component.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-2.2
     */
    public static function forPrivateUseOutsideQuery(): self
    {
        return new self('Unicode private use characters are only allowed in the query component of an IRI.');
    }

    /**
     * Create an exception for an invalid IDNA label.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5891
     */
    public static function forInvalidIDNALabel(string $label): self
    {
        return new self('Invalid IDNA label "' . $label . '".');
    }
}
