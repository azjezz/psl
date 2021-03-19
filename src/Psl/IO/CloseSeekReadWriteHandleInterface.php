<?php

declare(strict_types=1);

namespace Psl\IO;

interface CloseSeekReadWriteHandleInterface extends
    CloseReadWriteHandleInterface,
    CloseSeekReadHandleInterface,
    CloseSeekWriteHandleInterface,
    SeekReadWriteHandleInterface
{
}
