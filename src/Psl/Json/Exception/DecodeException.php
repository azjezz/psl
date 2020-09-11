<?php

declare(strict_types=1);

namespace Psl\Json\Exception;

use Psl\Exception\InvalidArgumentException;

final class DecodeException extends InvalidArgumentException implements ExceptionInterface
{
}
