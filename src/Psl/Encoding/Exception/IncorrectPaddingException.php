<?php

declare(strict_types=1);

namespace Psl\Encoding\Exception;

use Psl\Exception;

final class IncorrectPaddingException extends Exception\InvalidArgumentException implements ExceptionInterface
{
}
