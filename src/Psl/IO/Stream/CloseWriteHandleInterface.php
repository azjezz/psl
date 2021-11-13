<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface CloseWriteHandleInterface extends CloseHandleInterface, IO\CloseWriteHandleInterface, WriteHandleInterface
{
}
