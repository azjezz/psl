<?php

declare(strict_types=1);

namespace Psl\Exception;

use RuntimeException as RuntimeRootException;

class RuntimeException extends RuntimeRootException implements ExceptionInterface
{
}
