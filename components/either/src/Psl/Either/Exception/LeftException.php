<?php

declare(strict_types=1);

namespace Psl\Either\Exception;

use Psl\Exception\UnderflowException;

final class LeftException extends UnderflowException implements ExceptionInterface {}
