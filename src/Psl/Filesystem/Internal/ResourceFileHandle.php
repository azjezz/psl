<?php

declare(strict_types=1);

namespace Psl\Filesystem\Internal;

use Psl\Filesystem;
use Psl\IO;
use Psl\IO\Exception;
use Psl\Type;

/**
 * @internal
 */
final class ResourceFileHandle extends IO\Internal\ResourceHandle implements Filesystem\ReadWriteFileHandleInterface
{
    private string $path;

    /**
     * @param resource $resource
     *
     * @throws Type\Exception\AssertException If $resource is not a resource.
     * @throws Exception\BlockingException If unable to set the handle resource to non-blocking mode.
     */
    public function __construct(string $path, $resource)
    {
        parent::__construct($resource);

        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
