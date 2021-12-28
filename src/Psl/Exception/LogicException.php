<?php

declare(strict_types=1);

namespace Psl\Exception;

use LogicException as LogicRootException;

class LogicException extends LogicRootException implements ExceptionInterface
{
}
