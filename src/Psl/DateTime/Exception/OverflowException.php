<?php

declare(strict_types=1);

namespace Psl\DateTime\Exception;

use Psl\Exception;

final class OverflowException extends Exception\OverflowException implements ExceptionInterface
{
}
