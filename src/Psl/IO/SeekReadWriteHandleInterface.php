<?php

declare(strict_types=1);

namespace Psl\IO;

interface SeekReadWriteHandleInterface extends
    ReadWriteHandleInterface,
    SeekReadHandleInterface,
    SeekWriteHandleInterface
{
}
