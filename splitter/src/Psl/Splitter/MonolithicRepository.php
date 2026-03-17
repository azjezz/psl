<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\File;
use Psl\Filesystem;
use Psl\Json;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use Psl\Iter;

/**
 * Scans the monorepo packages/ directory and builds the package list.
 */
final readonly class MonolithicRepository
{
    private const string ORG = 'php-standard-library';

    /**
     * @param non-empty-string $rootPath Absolute path to the monorepo root
     * @param list<Package> $packages
     */
    private function __construct(
        public string $rootPath,
        public array $packages,
    ) {}

    /**
     * Scan the monorepo and discover all packages.
     *
     * @param non-empty-string $rootPath
     *
     * @throws Filesystem\Exception\ExceptionInterface If the packages directory cannot be read.
     * @throws Json\Exception\DecodeException If a composer.json file contains invalid JSON.
     */
    public static function discover(string $rootPath): self
    {
        $packagesDir = $rootPath . '/packages';
        $directories = Filesystem\read_directory($packagesDir);
        $packages = [];

        foreach ($directories as $dir) {
            if (!Filesystem\is_directory($dir)) {
                continue;
            }

            $composerPath = $dir . '/composer.json';
            if (!Filesystem\is_file($composerPath)) {
                continue;
            }

            $composerJson = Json\typed(
                File\read($composerPath),
                Type\shape([
                    'name' => Type\non_empty_string(),
                    'require' => Type\dict(Type\non_empty_string(), Type\non_empty_string()),
                ], allowUnknownFields: true),
            );

            if (!Str\starts_with($composerJson['name'], self::ORG . '/')) {
                Log\warn('unexpected package name: ' . $composerJson['name']);
                continue;
            }

            $dependencies = Vec\filter(
                Vec\keys($composerJson['require']),
                static fn(string $pkg): bool => Str\starts_with($pkg, self::ORG . '/'),
            );

            $packages[] = new Package(
                name: $composerJson['name'],
                directory: Filesystem\get_basename($dir),
                path: $dir,
                dependencies: $dependencies,
            );
        }

        return new self($rootPath, $packages);
    }

    /**
     * Check if a version/branch should be processed (>= 6.0).
     *
     * Branches before 6.0 predate the split and are ignored.
     * The "next" branch is always processed.
     *
     * @param non-empty-string $ref Branch name or tag
     */
    public static function shouldProcess(string $ref): bool
    {
        if ($ref === 'next') {
            return true;
        }

        // Branch like "6.1.x" -> extract major version
        if (Str\ends_with($ref, '.x')) {
            $major = Str\before($ref, '.');
            return $major !== null && (int) $major >= 6;
        }

        // Tag like "6.1.0"
        $version = $ref;
        $major = Str\before($version, '.');
        return $major !== null && (int) $major >= 6;
    }

    /**
     * Infer the branch name from a tag.
     *
     * - 6.0.0 → "next" (new minor/major, creates 6.0.x)
     * - 6.0.1 → "6.0.x" (patch release)
     * - 6.1.0 → "next" (new minor, creates 6.1.x)
     *
     * @param non-empty-string $tag e.g. "6.1.0"
     *
     * @return non-empty-string
     */
    public static function branchForTag(string $tag): string
    {
        $version = $tag;
        $parts = Str\split($version, '.');
        if (Iter\count($parts) < 3) {
            return 'next';
        }

        $patch = (int) $parts[2];
        if ($patch === 0) {
            return 'next';
        }

        return $parts[0] . '.' . $parts[1] . '.x';
    }

    /**
     * Check if a tag is a new minor/major (x.y.0) requiring a maintenance branch.
     *
     * @param non-empty-string $tag
     */
    public static function isNewReleaseBranch(string $tag): bool
    {
        $version = $tag;
        $parts = Str\split($version, '.');

        return Iter\count($parts) >= 3 && (int) $parts[2] === 0;
    }
}
