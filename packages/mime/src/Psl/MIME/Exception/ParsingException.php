<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

/**
 * Base exception for MIME parsing failures.
 *
 * Thrown when a MIME string cannot be parsed into a structured representation.
 * Subclasses specialize the failure to a specific MIME component.
 *
 * @inheritors MediaTypeParsingException|ParameterParsingException|ContentDispositionParsingException|ContentIdParsingException
 *
 * @see MediaTypeParsingException
 * @see ParameterParsingException
 * @see ContentDispositionParsingException
 * @see ContentIdParsingException
 *
 * @api
 */
class ParsingException extends RuntimeException {}
