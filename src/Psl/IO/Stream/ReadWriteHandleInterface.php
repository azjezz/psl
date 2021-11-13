<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface ReadWriteHandleInterface extends IO\ReadWriteHandleInterface, ReadHandleInterface, WriteHandleInterface
{
}
