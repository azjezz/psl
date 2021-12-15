<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

interface CloseWriteStreamHandleInterface extends CloseStreamHandleInterface, IO\CloseWriteHandleInterface, WriteStreamHandleInterface
{
}
