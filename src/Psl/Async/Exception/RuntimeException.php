<?php

declare(strict_types=1);

namespace Psl\Async\Exception;

use Psl\Exception;

class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
}
