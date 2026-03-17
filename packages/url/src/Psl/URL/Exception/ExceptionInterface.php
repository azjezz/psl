<?php

declare(strict_types=1);

namespace Psl\URL\Exception;

use Psl\Exception;

/**
 * Marker interface for all exceptions thrown by the URL component.
 *
 * @link https://www.rfc-editor.org/rfc/rfc3986 RFC 3986 - Uniform Resource Identifier (URI): Generic Syntax
 */
interface ExceptionInterface extends Exception\ExceptionInterface {}
