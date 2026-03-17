<?php

declare(strict_types=1);

(static function (): void {
    $constants = [
        'Psl\Filesystem\SEPARATOR' => __DIR__ . '/Filesystem/constants.php',
    ];

    $functions = [
        'Psl\Filesystem\Internal\box' => __DIR__ . '/Filesystem/Internal/box.php',
        'Psl\Filesystem\canonicalize' => __DIR__ . '/Filesystem/canonicalize.php',
        'Psl\Filesystem\change_group' => __DIR__ . '/Filesystem/change_group.php',
        'Psl\Filesystem\change_owner' => __DIR__ . '/Filesystem/change_owner.php',
        'Psl\Filesystem\change_permissions' => __DIR__ . '/Filesystem/change_permissions.php',
        'Psl\Filesystem\copy' => __DIR__ . '/Filesystem/copy.php',
        'Psl\Filesystem\create_directory' => __DIR__ . '/Filesystem/create_directory.php',
        'Psl\Filesystem\create_directory_for_file' => __DIR__ . '/Filesystem/create_directory_for_file.php',
        'Psl\Filesystem\create_file' => __DIR__ . '/Filesystem/create_file.php',
        'Psl\Filesystem\create_hard_link' => __DIR__ . '/Filesystem/create_hard_link.php',
        'Psl\Filesystem\create_symbolic_link' => __DIR__ . '/Filesystem/create_symbolic_link.php',
        'Psl\Filesystem\create_temporary_file' => __DIR__ . '/Filesystem/create_temporary_file.php',
        'Psl\Filesystem\delete_directory' => __DIR__ . '/Filesystem/delete_directory.php',
        'Psl\Filesystem\delete_file' => __DIR__ . '/Filesystem/delete_file.php',
        'Psl\Filesystem\exists' => __DIR__ . '/Filesystem/exists.php',
        'Psl\Filesystem\file_size' => __DIR__ . '/Filesystem/file_size.php',
        'Psl\Filesystem\get_access_time' => __DIR__ . '/Filesystem/get_access_time.php',
        'Psl\Filesystem\get_basename' => __DIR__ . '/Filesystem/get_basename.php',
        'Psl\Filesystem\get_change_time' => __DIR__ . '/Filesystem/get_change_time.php',
        'Psl\Filesystem\get_directory' => __DIR__ . '/Filesystem/get_directory.php',
        'Psl\Filesystem\get_extension' => __DIR__ . '/Filesystem/get_extension.php',
        'Psl\Filesystem\get_filename' => __DIR__ . '/Filesystem/get_filename.php',
        'Psl\Filesystem\get_group' => __DIR__ . '/Filesystem/get_group.php',
        'Psl\Filesystem\get_inode' => __DIR__ . '/Filesystem/get_inode.php',
        'Psl\Filesystem\get_modification_time' => __DIR__ . '/Filesystem/get_modification_time.php',
        'Psl\Filesystem\get_owner' => __DIR__ . '/Filesystem/get_owner.php',
        'Psl\Filesystem\get_permissions' => __DIR__ . '/Filesystem/get_permissions.php',
        'Psl\Filesystem\is_directory' => __DIR__ . '/Filesystem/is_directory.php',
        'Psl\Filesystem\is_executable' => __DIR__ . '/Filesystem/is_executable.php',
        'Psl\Filesystem\is_file' => __DIR__ . '/Filesystem/is_file.php',
        'Psl\Filesystem\is_readable' => __DIR__ . '/Filesystem/is_readable.php',
        'Psl\Filesystem\is_symbolic_link' => __DIR__ . '/Filesystem/is_symbolic_link.php',
        'Psl\Filesystem\is_writable' => __DIR__ . '/Filesystem/is_writable.php',
        'Psl\Filesystem\read_directory' => __DIR__ . '/Filesystem/read_directory.php',
        'Psl\Filesystem\read_symbolic_link' => __DIR__ . '/Filesystem/read_symbolic_link.php',
    ];

    foreach ($constants as $constant => $path) {
        if (defined($constant)) {
            continue;
        }

        require_once $path;
    }

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
