<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

interface CloseReadWriteStreamHandleInterface extends
    CloseReadStreamHandleInterface,
    CloseWriteStreamHandleInterface,
    IO\CloseReadWriteHandleInterface,
    ReadWriteStreamHandleInterface
{
}
