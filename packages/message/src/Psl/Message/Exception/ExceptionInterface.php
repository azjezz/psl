<?php

declare(strict_types=1);

namespace Psl\Message\Exception;

use Psl\Exception;

/**
 * Marker interface for all exceptions thrown by the Psl\Message component.
 *
 * All exceptions in this namespace implement this interface, allowing
 * callers to catch any message-related exception with a single type.
 *
 * @api
 */
interface ExceptionInterface extends Exception\ExceptionInterface {}
