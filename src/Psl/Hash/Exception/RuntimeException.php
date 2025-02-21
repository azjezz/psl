<?php

declare(strict_types=1);

namespace Psl\Hash\Exception;

use Psl\Exception;

/**
 * @psalm-suppress ClassMustBeFinal
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
}
