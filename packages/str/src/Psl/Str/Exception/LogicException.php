<?php

declare(strict_types=1);

namespace Psl\Str\Exception;

use Psl\Exception;

/**
 * @mutation-free
 *
 * @api
 */
final class LogicException extends Exception\LogicException implements ExceptionInterface {}
