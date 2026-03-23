<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Dict;
use Psl\File;
use Psl\Filesystem;
use Psl\Iter;
use Psl\Json;
use Psl\Regex;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

use function Psl\Internal\package_namespace_map;

/**
 * Verify that every package's composer.json `require` section matches actual source imports,
 * and that no source dependency is incorrectly placed in `require-dev`.
 *
 * Returns true if all packages are valid.
 *
 * @param list<Package> $packages
 *
 * @mago-expect lint:no-literal-password - Not really a password.
 * @mago-expect lint:excessive-nesting - :(
 */
function verify(array $packages): bool
{
    $nsToDir = package_namespace_map();

    $ok = true;

    foreach ($packages as $package) {
        $srcDir = $package->path . '/src';
        if (!Filesystem\is_directory($srcDir)) {
            continue;
        }

        $composerPath = $package->path . '/composer.json';
        $composer = Json\typed(
            File\read($composerPath),
            Type\shape([
                'require' => Type\dict(Type\non_empty_string(), Type\non_empty_string()),
                'require-dev' => Type\optional(Type\dict(Type\non_empty_string(), Type\non_empty_string())),
            ], allowUnknownFields: true),
        );

        $declaredRequire = [];
        foreach ($composer['require'] as $pkg => $ver) {
            if (!Str\starts_with($pkg, 'php-standard-library/')) {
                continue;
            }

            $declaredRequire[] = Str\strip_prefix($pkg, 'php-standard-library/');
        }

        $declaredDev = [];
        foreach ($composer['require-dev'] ?? [] as $pkg => $ver) {
            if (!Str\starts_with($pkg, 'php-standard-library/')) {
                continue;
            }

            $declaredDev[] = Str\strip_prefix($pkg, 'php-standard-library/');
        }

        $srcDeps = [];
        $checkFiles =
            /**
             * @param list<non-empty-string> $files
             */
            function (Package $package, array $files) use ($nsToDir, &$srcDeps, &$checkFiles): void {
                foreach ($files as $file) {
                    if (Filesystem\is_directory($file)) {
                        $checkFiles($package, Filesystem\read_directory($file));
                        continue;
                    }

                    if (Filesystem\get_extension($file) !== 'php') {
                        continue;
                    }

                    if (Str\ends_with($file, 'bootstrap.php')) {
                        continue;
                    }

                    $content = File\read($file);
                    foreach ($nsToDir as $ns => $dir) {
                        if ($dir === $package->directory) {
                            continue;
                        }

                        $escapedNs = Str\replace($ns, '\\', '\\\\');
                        if (Regex\matches($content, '/Psl\\\\' . $escapedNs . '[\\\\\\s;,)]/')) {
                            $srcDeps[$dir] = true;
                        }
                    }
                }
            };

        $checkFiles($package, Filesystem\read_directory($srcDir));

        $missingFromRequire = Dict\diff(Vec\keys($srcDeps), $declaredRequire);
        foreach ($missingFromRequire as $dep) {
            if (Iter\contains($declaredDev, $dep)) {
                Log\error(
                    '%s uses %s in source, but it is in require-dev instead of require',
                    $package->name,
                    'php-standard-library/' . $dep,
                );
            } else {
                Log\error(
                    '%s uses %s in source, but it is not declared in require',
                    $package->name,
                    'php-standard-library/' . $dep,
                );
            }

            $ok = false;
        }

        $extraInRequire = Dict\diff($declaredRequire, Vec\keys($srcDeps));
        foreach ($extraInRequire as $dep) {
            Log\warn(
                '%s declares %s in require, but it is not used in source',
                $package->name,
                'php-standard-library/' . $dep,
            );

            $ok = false;
        }

        $testDir = $package->path . '/tests';
        if (!Filesystem\is_directory($testDir)) {
            continue;
        }

        $testDeps = [];
        $checkTestFiles =
            /**
             * @param list<non-empty-string> $files
             */
            function (Package $package, array $files) use ($nsToDir, &$testDeps, &$checkTestFiles): void {
                foreach ($files as $file) {
                    if (Filesystem\is_directory($file)) {
                        $checkTestFiles($package, Filesystem\read_directory($file));
                        continue;
                    }

                    if (Filesystem\get_extension($file) !== 'php') {
                        continue;
                    }

                    $content = File\read($file);
                    foreach ($nsToDir as $ns => $dir) {
                        if ($dir === $package->directory) {
                            continue;
                        }

                        $escapedNs = Str\replace($ns, '\\', '\\\\');
                        if (Regex\matches($content, '/Psl\\\\' . $escapedNs . '[\\\\\\s;,)]/')) {
                            $testDeps[$dir] = true;
                        }
                    }
                }
            };

        $checkTestFiles($package, Filesystem\read_directory($testDir));

        $allDeclared = Vec\concat($declaredRequire, $declaredDev);
        $missingFromTestDeps = Dict\diff(Vec\keys($testDeps), $allDeclared);
        foreach ($missingFromTestDeps as $dep) {
            Log\error(
                '%s uses %s in tests, but it is not declared in require or require-dev',
                $package->name,
                'php-standard-library/' . $dep,
            );

            $ok = false;
        }
    }

    return $ok;
}
