<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\IO;

interface WriteFileHandleInterface extends FileHandleInterface, IO\CloseSeekWriteHandleInterface
{
}
