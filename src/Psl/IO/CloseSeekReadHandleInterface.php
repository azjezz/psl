<?php

declare(strict_types=1);

namespace Psl\IO;

interface CloseSeekReadHandleInterface extends
    CloseReadHandleInterface,
    CloseSeekHandleInterface,
    SeekReadHandleInterface
{
}
