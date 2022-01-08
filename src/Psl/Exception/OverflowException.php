<?php

declare(strict_types=1);

namespace Psl\Exception;

use OverflowException as OverflowRootException;

class OverflowException extends OverflowRootException implements ExceptionInterface
{
}
