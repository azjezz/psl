<?php

declare(strict_types=1);

namespace Psl\Exception;

use RangeException as RangeRootException;

/**
 * @api
 */
class RangeException extends RangeRootException implements ExceptionInterface {}
