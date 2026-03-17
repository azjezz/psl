<?php

declare(strict_types=1);

namespace Psl\Splitter;

/**
 * Represents a single package in the monorepo.
 */
final readonly class Package
{
    /**
     * @param non-empty-string $name e.g. "php-standard-library/type"
     * @param non-empty-string $directory e.g. "type"
     * @param non-empty-string $path Absolute path to the package directory
     * @param list<non-empty-string> $dependencies Package names this depends on
     */
    public function __construct(
        public string $name,
        public string $directory,
        public string $path,
        public array $dependencies,
    ) {}

    /**
     * The remote repository name, e.g. "php-standard-library/type".
     *
     * @return non-empty-string
     */
    public function remote(): string
    {
        return $this->name;
    }

    /**
     * The GitHub repository URL.
     *
     * @return non-empty-string
     */
    public function repositoryUrl(): string
    {
        return 'https://github.com/' . $this->name . '.git';
    }
}
