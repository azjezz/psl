<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

interface ReadWriteStreamHandleInterface extends IO\ReadWriteHandleInterface, ReadStreamHandleInterface, WriteStreamHandleInterface
{
}
