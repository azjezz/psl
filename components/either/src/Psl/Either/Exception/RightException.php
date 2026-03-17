<?php

declare(strict_types=1);

namespace Psl\Either\Exception;

use Psl\Exception\UnderflowException;

final class RightException extends UnderflowException implements ExceptionInterface {}
