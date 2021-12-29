<?php

declare(strict_types=1);

namespace Psl\Filesystem\Exception;

use Psl\Exception;

class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
}
