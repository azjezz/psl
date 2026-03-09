<?php

declare(strict_types=1);

namespace Psl\Process\Exception;

use Psl\Exception;

final class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface {}
