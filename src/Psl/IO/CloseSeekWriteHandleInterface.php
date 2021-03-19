<?php

declare(strict_types=1);

namespace Psl\IO;

interface CloseSeekWriteHandleInterface extends
    CloseSeekHandleInterface,
    CloseWriteHandleInterface,
    SeekWriteHandleInterface
{
}
