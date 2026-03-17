param(
    [switch]$Restore
)

Add-Type -TypeDefinition @"
using System;
using System.Runtime.InteropServices;

public static class Kernel32 {
    public const int  STD_INPUT_HANDLE = -10;
    public const uint ENABLE_ECHO_INPUT             = 0x0004;
    public const uint ENABLE_LINE_INPUT             = 0x0002;
    public const uint ENABLE_VIRTUAL_TERMINAL_INPUT = 0x0200;

    [DllImport("kernel32.dll", SetLastError = true)]
    public static extern IntPtr GetStdHandle(int nStdHandle);

    [DllImport("kernel32.dll", SetLastError = true)]
    public static extern bool GetConsoleMode(IntPtr hConsoleHandle, out uint lpMode);

    [DllImport("kernel32.dll", SetLastError = true)]
    public static extern bool SetConsoleMode(IntPtr hConsoleHandle, uint dwMode);
}
"@

$handle = [Kernel32]::GetStdHandle([Kernel32]::STD_INPUT_HANDLE)
if ($handle -eq [IntPtr]::Zero -or $handle -eq [IntPtr]::new(-1)) {
    Write-Error "Failed to get console input handle"
    exit 1
}

[uint32]$mode = 0
if (-not [Kernel32]::GetConsoleMode($handle, [ref]$mode)) {
    Write-Error "GetConsoleMode failed (error $([System.Runtime.InteropServices.Marshal]::GetLastWin32Error()))"
    exit 1
}

if ($Restore) {
    $originalMode = [uint32]$args[0]
    if (-not [Kernel32]::SetConsoleMode($handle, $originalMode)) {
        Write-Error "SetConsoleMode (restore) failed"
        exit 1
    }
    exit 0
}

Write-Output $mode

$newMode = $mode `
    -band (-bnot ([Kernel32]::ENABLE_LINE_INPUT -bor [Kernel32]::ENABLE_ECHO_INPUT)) `
    -bor [Kernel32]::ENABLE_VIRTUAL_TERMINAL_INPUT

if (-not [Kernel32]::SetConsoleMode($handle, $newMode)) {
    Write-Error "SetConsoleMode failed (error $([System.Runtime.InteropServices.Marshal]::GetLastWin32Error()))"
    exit 1
}
