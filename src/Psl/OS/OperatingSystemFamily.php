<?php

declare(strict_types=1);

namespace Psl\OS;

use Psl\Default\DefaultInterface;

/**
 * Enumerates the family of operating systems recognized by the platform.
 *
 * This enumeration classifies different operating systems into a set of well-known families,
 * facilitating OS-specific behavior or optimizations in a type-safe manner. The classification
 * helps in abstracting OS checks and performing operations that depend on the underlying OS family.
 */
enum OperatingSystemFamily: string implements DefaultInterface
{
    /**
     * Represents the Windows family of operating systems.
     */
    case Windows = 'Windows';

    /**
     * Represents the various BSD flavors, including FreeBSD, OpenBSD, and NetBSD.
     */
    case BSD = 'BSD';

    /**
     * Represents the Darwin system, the core of macOS and iOS.
     */
    case Darwin = 'Darwin';

    /**
     * Represents the Solaris family of operating systems, including Oracle Solaris.
     */
    case Solaris = 'Solaris';

    /**
     * Represents Linux-based operating systems, covering a wide range of Linux distributions.
     */
    case Linux = 'Linux';

    /**
     * Represents an unknown or unrecognized operating system family.
     * This case is used when the OS does not match any of the known families.
     */
    case Unknown = 'Unknown';

    /**
     * Provides the default operating system family based on the current environment.
     *
     * This method leverages the {@see family()} function to dynamically determine and
     * return the operating system family of the environment in which the PHP script is executed.
     * It allows for adaptive behavior in applications, depending on the OS family at runtime.
     *
     * @return static The operating system family that matches the current environment.
     *
     * @pure
     */
    #[\Override]
    public static function default(): static
    {
        return namespace\family();
    }
}
