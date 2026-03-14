<?php

declare(strict_types=1);

namespace Psl\Exception;

use RangeException as RangeRootException;

class RangeException extends RangeRootException implements ExceptionInterface {}
