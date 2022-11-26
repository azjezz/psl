<?php

declare(strict_types=1);

namespace Psl\Option\Exception;

use Psl\Exception\UnderflowException;

final class NoneException extends UnderflowException implements ExceptionInterface
{
}
