<?php

declare(strict_types=1);

namespace Psl\Ansi\Exception;

use Psl\Exception;

/**
 * @mutation-free
 */
final class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface {}
