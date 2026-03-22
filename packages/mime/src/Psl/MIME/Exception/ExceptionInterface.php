<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Psl\Exception;

/**
 * Marker interface for all exceptions thrown by the MIME component.
 *
 * Every exception in the {@see \Psl\MIME} namespace implements this interface,
 * allowing callers to catch all MIME-related errors with a single type.
 *
 * @see RuntimeException
 * @see InvalidArgumentException
 *
 * @api
 */
interface ExceptionInterface extends Exception\ExceptionInterface {}
