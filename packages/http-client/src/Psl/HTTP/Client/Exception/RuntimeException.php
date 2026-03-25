<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Exception;

use Psl\Exception;

/**
 * Base exception for all HTTP client errors.
 *
 * This is the root of the HTTP client exception hierarchy. All exceptions thrown
 * by the HTTP client extend this class, making it possible to catch any client
 * error with a single catch clause.
 */
class RuntimeException extends Exception\RuntimeException {}
