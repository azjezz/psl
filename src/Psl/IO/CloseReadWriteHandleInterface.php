<?php

declare(strict_types=1);

namespace Psl\IO;

interface CloseReadWriteHandleInterface extends
    CloseReadHandleInterface,
    CloseWriteHandleInterface,
    ReadWriteHandleInterface
{
}
