<?php

declare(strict_types=1);

namespace Psl\SecureRandom\Exception;

use Psl\Exception\RuntimeException;

/**
 * @api
 */
final class InsufficientEntropyException extends RuntimeException implements ExceptionInterface {}
