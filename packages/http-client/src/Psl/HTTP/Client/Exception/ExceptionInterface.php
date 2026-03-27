<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Exception;

use Psl\Exception;
use Psl\HTTP\Client;

/**
 * Marker interface for all exceptions thrown by the HTTP client component.
 *
 * Every exception in the {@see Client} namespace implements this
 * interface, making it possible to catch all HTTP client errors with a single
 * catch clause while still distinguishing them from exceptions thrown by other
 * PSL components.
 *
 * The exception hierarchy is:
 *
 * - {@see RuntimeException} (base class for all concrete exceptions)
 *   - {@see ProtocolException} (malformed responses, unsupported protocols, body size violations)
 *   - {@see RequestException} (invalid or unresolvable requests)
 *   - {@see TooManyRedirectsException} (redirect limit exceeded)
 *
 * @see Exception\ExceptionInterface Parent marker interface for all PSL exceptions.
 *
 * @api
 */
interface ExceptionInterface extends Exception\ExceptionInterface {}
