<?php

declare(strict_types=1);

namespace Psl\EitherOrBoth\Exception;

use Psl\Exception\UnderflowException;

/**
 * @api
 */
final class MissingRightException extends UnderflowException implements ExceptionInterface {}
