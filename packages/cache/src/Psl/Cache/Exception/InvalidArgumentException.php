<?php

declare(strict_types=1);

namespace Psl\Cache\Exception;

use Psl\Exception;

/**
 * Thrown when a cache key is invalid (empty or contains illegal characters).
 *
 * @api
 */
final class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface {}
