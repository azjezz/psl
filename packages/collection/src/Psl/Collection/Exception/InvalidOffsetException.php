<?php

declare(strict_types=1);

namespace Psl\Collection\Exception;

use Psl\Exception;

/**
 * @api
 */
final class InvalidOffsetException extends Exception\UnexpectedValueException implements ExceptionInterface {}
