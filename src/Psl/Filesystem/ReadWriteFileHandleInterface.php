<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\IO;

interface ReadWriteFileHandleInterface extends
  ReadFileHandleInterface,
  WriteFileHandleInterface,
  IO\CloseSeekReadWriteHandleInterface
{
}
