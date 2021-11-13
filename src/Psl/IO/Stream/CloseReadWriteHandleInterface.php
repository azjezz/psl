<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface CloseReadWriteHandleInterface extends
    CloseReadHandleInterface,
    CloseWriteHandleInterface,
    IO\CloseReadWriteHandleInterface,
    ReadWriteHandleInterface
{
}
