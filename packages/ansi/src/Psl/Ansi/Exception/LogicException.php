<?php

declare(strict_types=1);

namespace Psl\Ansi\Exception;

use Psl\Exception;

/**
 * @mutation-free
 *
 * @api
 */
final class LogicException extends Exception\LogicException implements ExceptionInterface {}
