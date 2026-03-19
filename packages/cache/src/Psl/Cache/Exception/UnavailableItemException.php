<?php

declare(strict_types=1);

namespace Psl\Cache\Exception;

/**
 * Thrown when a requested cache item does not exist or has expired.
 */
final class UnavailableItemException extends RuntimeException implements ExceptionInterface {}
