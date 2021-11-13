<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface CloseReadHandleInterface extends CloseHandleInterface, IO\CloseReadHandleInterface, ReadHandleInterface
{
}
