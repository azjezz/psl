<?php

declare(strict_types=1);

namespace Psl\Exception;

use OutOfBoundsException as OutOfBoundsRootException;

class OutOfBoundsException extends OutOfBoundsRootException implements ExceptionInterface
{
}
