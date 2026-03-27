<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Exception;

use Psl\Exception;
use Psl\HTTP\Client;

/**
 * Base exception class for all HTTP client errors.
 *
 * This is the root of the HTTP client exception hierarchy. All concrete
 * exception classes in this namespace extend this class, making it possible
 * to catch any HTTP client error with a single catch clause.
 *
 * This class is also thrown directly (not via a subclass) for transport-level
 * errors that do not fit a more specific category, such as:
 *
 * - Denied destinations blocked by SSRF protection ({@see Client\Middleware\DeniedDestinationsMiddleware})
 * - ALPN negotiation mismatches during connection pooling ({@see Client\Connection\PooledConnector})
 *
 * @see ExceptionInterface Marker interface implemented by all HTTP client exceptions.
 * @see Exception\RuntimeException Parent class providing the standard runtime exception contract.
 *
 * @api
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface {}
