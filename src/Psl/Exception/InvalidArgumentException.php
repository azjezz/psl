<?php

declare(strict_types=1);

namespace Psl\Exception;

use InvalidArgumentException as InvalidArgumentRootException;

class InvalidArgumentException extends InvalidArgumentRootException implements ExceptionInterface
{
}
