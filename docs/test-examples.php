<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\File;
use Psl\Filesystem;
use Psl\IO;
use Psl\Iter;
use Psl\Regex;
use Psl\Shell;
use Psl\Str;
use Psl\Vec;

const DOCUMENTATION_DIR = __DIR__;

/**
 * @var non-empty-string
 */
const EXAMPLES_DIR = DOCUMENTATION_DIR . '/examples';

Async\main(static function (): int {
    if (!Filesystem\exists(DOCUMENTATION_DIR . '/../vendor/autoload.php')) {
        IO\write_error_line('Missing vendor/autoload.php - run composer install first.');
        return 1;
    }

    if (!Filesystem\is_directory(EXAMPLES_DIR)) {
        IO\write_error_line('Missing examples/ directory.');
        return 1;
    }

    $files = [];
    foreach (Filesystem\read_directory(EXAMPLES_DIR) as $categoryPath) {
        if (!Filesystem\is_directory($categoryPath)) {
            continue;
        }

        foreach (Filesystem\read_directory($categoryPath) as $filePath) {
            if (!Str\ends_with($filePath, '.php')) {
                continue;
            }

            $relative = Str\replace($filePath, EXAMPLES_DIR . '/', '');
            $files[] = [
                'path' => $filePath,
                'label' => $relative,
            ];
        }
    }

    $files = Vec\sort($files, fn(array $a, array $b): int => $a['label'] <=> $b['label']);

    IO\write_error_line('Found %d example files.', Iter\count($files));
    IO\write_error_line('');

    $errors = [];
    $passed = 0;
    $skipped = 0;

    $skipPatterns = [
        '/tls-/' => 'requires TLS certificates/network',
        '/socks-/' => 'requires SOCKS proxy',
        '/terminal-(?!.*ansi)/' => 'requires interactive terminal',
    ];

    foreach ($files as $entry) {
        $file = $entry['path'];
        $label = $entry['label'];

        $canRun = true;
        $skipReason = '';
        foreach ($skipPatterns as $pattern => $reason) {
            if (!Regex\matches($label, $pattern)) {
                continue;
            }

            $canRun = false;
            $skipReason = $reason;
            break;
        }

        if ($canRun) {
            $content = File\read($file);

            if (Str\contains($content, 'Terminal\\Application')) {
                $canRun = false;
                $skipReason = 'uses Terminal\\Application (TUI)';
            }
        }

        if (!$canRun) {
            $skipped++;
            $passed++;
            IO\write_error_line('  SKIP  (skip run: %s)  %s', $skipReason, $label);
            continue;
        }

        try {
            Shell\execute(
                'php',
                [$file],
                error_output_behavior: Shell\ErrorOutputBehavior::Append,
                timeout: Duration::seconds(30),
            );
        } catch (Shell\Exception\FailedExecutionException $e) {
            $errors[] = [
                'label' => $label,
                'error' => $e->getOutput() . $e->getErrorOutput(),
            ];
            IO\write_error_line('  RUNTIME FAIL  %s', $label);
            continue;
        } catch (Shell\Exception\TimeoutException) {
            $errors[] = [
                'label' => $label,
                'error' => 'Timed out after 30 seconds',
            ];

            IO\write_error_line('  TIMEOUT  %s', $label);
            continue;
        }

        $passed++;
        IO\write_error_line('  OK  %s', $label);
    }

    $separator = Str\repeat('=', 60);
    IO\write_error_line('');
    IO\write_error_line($separator);
    IO\write_error_line(
        'RESULTS: %d files total, %d passed, %d skipped runtime, %d runtime errors',
        Iter\count($files),
        $passed,
        $skipped,
        Iter\count($errors),
    );
    IO\write_error_line($separator);
    IO\write_error_line('');

    if ($errors !== []) {
        IO\write_error_line('RUNTIME ERRORS:');
        IO\write_error_line('');
        foreach ($errors as $err) {
            IO\write_error_line('--- %s ---', $err['label']);
            IO\write_error_line($err['error']);
            IO\write_error_line('');
        }
    }

    return $errors === [] ? 0 : 1;
});
