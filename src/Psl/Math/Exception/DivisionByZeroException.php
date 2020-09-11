<?php

declare(strict_types=1);

namespace Psl\Math\Exception;

use Psl\Exception\InvalidArgumentException;

final class DivisionByZeroException extends InvalidArgumentException implements ExceptionInterface
{
}
