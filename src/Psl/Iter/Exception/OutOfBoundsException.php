<?php

declare(strict_types=1);

namespace Psl\Iter\Exception;

use OutOfBoundsException as OutOfBoundsRootException;

final class OutOfBoundsException extends OutOfBoundsRootException implements ExceptionInterface
{
}
