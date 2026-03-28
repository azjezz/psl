<?php

declare(strict_types=1);

namespace Psl\Iter\Exception;

use Psl\Exception;

/**
 * @api
 */
final class OutOfBoundsException extends Exception\OutOfBoundsException implements ExceptionInterface {}
