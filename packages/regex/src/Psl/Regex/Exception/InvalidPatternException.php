<?php

declare(strict_types=1);

namespace Psl\Regex\Exception;

use Psl\Exception\InvalidArgumentException;

/**
 * @api
 */
final class InvalidPatternException extends InvalidArgumentException implements ExceptionInterface {}
