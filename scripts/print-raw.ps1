param(
    [Parameter(Mandatory = $true)][string]$PrinterName,
    [Parameter(Mandatory = $true)][string]$FilePath
)

$signature = @'
using System;
using System.Runtime.InteropServices;

public static class RawPrinter
{
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)]
    public class DOC_INFO_1
    {
        [MarshalAs(UnmanagedType.LPWStr)] public string pDocName;
        [MarshalAs(UnmanagedType.LPWStr)] public string pOutputFile;
        [MarshalAs(UnmanagedType.LPWStr)] public string pDataType;
    }

    [DllImport("winspool.drv", SetLastError = true, CharSet = CharSet.Unicode)]
    public static extern bool OpenPrinter(string printerName, out IntPtr printer, IntPtr defaults);
    [DllImport("winspool.drv", SetLastError = true)]
    public static extern bool ClosePrinter(IntPtr printer);
    [DllImport("winspool.drv", SetLastError = true, CharSet = CharSet.Unicode)]
    public static extern int StartDocPrinter(IntPtr printer, int level, DOC_INFO_1 docInfo);
    [DllImport("winspool.drv", SetLastError = true)]
    public static extern bool EndDocPrinter(IntPtr printer);
    [DllImport("winspool.drv", SetLastError = true)]
    public static extern bool StartPagePrinter(IntPtr printer);
    [DllImport("winspool.drv", SetLastError = true)]
    public static extern bool EndPagePrinter(IntPtr printer);
    [DllImport("winspool.drv", SetLastError = true)]
    public static extern bool WritePrinter(IntPtr printer, IntPtr bytes, int count, out int written);
}
'@

Add-Type -TypeDefinition $signature

$printer = [IntPtr]::Zero
$buffer = [IntPtr]::Zero
$documentStarted = $false
$pageStarted = $false

try {
    if (-not [RawPrinter]::OpenPrinter($PrinterName, [ref]$printer, [IntPtr]::Zero)) {
        throw "Cannot open printer '$PrinterName' (Windows error $([Runtime.InteropServices.Marshal]::GetLastWin32Error()))."
    }

    $document = New-Object RawPrinter+DOC_INFO_1
    $document.pDocName = 'Writ barcode label'
    $document.pDataType = 'RAW'

    if ([RawPrinter]::StartDocPrinter($printer, 1, $document) -eq 0) {
        throw "Cannot start print job (Windows error $([Runtime.InteropServices.Marshal]::GetLastWin32Error()))."
    }
    $documentStarted = $true

    if (-not [RawPrinter]::StartPagePrinter($printer)) {
        throw "Cannot start printer page (Windows error $([Runtime.InteropServices.Marshal]::GetLastWin32Error()))."
    }
    $pageStarted = $true

    [byte[]]$bytes = [IO.File]::ReadAllBytes($FilePath)
    $buffer = [Runtime.InteropServices.Marshal]::AllocHGlobal($bytes.Length)
    [Runtime.InteropServices.Marshal]::Copy($bytes, 0, $buffer, $bytes.Length)
    $written = 0

    if (-not [RawPrinter]::WritePrinter($printer, $buffer, $bytes.Length, [ref]$written) -or $written -ne $bytes.Length) {
        throw "Printer accepted $written of $($bytes.Length) bytes (Windows error $([Runtime.InteropServices.Marshal]::GetLastWin32Error()))."
    }

    Write-Output "Raw print job queued: $written bytes."
}
catch {
    Write-Error $_.Exception.Message
    exit 1
}
finally {
    if ($buffer -ne [IntPtr]::Zero) { [Runtime.InteropServices.Marshal]::FreeHGlobal($buffer) }
    if ($pageStarted) { [void][RawPrinter]::EndPagePrinter($printer) }
    if ($documentStarted) { [void][RawPrinter]::EndDocPrinter($printer) }
    if ($printer -ne [IntPtr]::Zero) { [void][RawPrinter]::ClosePrinter($printer) }
}
