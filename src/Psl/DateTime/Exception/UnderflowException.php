<?php

declare(strict_types=1);

namespace Psl\DateTime\Exception;

use Psl\Exception;

final class UnderflowException extends Exception\UnderflowException implements ExceptionInterface
{
}
