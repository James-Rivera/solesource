<#
Simple admin helper: append host mappings to C:\Windows\System32\drivers\etc\hosts (idempotent).
Usage (run as Administrator):
  .\add-hosts.ps1 -Entries @("127.0.0.1 solesource.local","127.0.0.1 test.local")
#>

param(
    [Parameter(Mandatory=$true)]
    [string[]]$Entries
)

function Test-IsAdmin {
    $id = [Security.Principal.WindowsIdentity]::GetCurrent()
    $p = New-Object Security.Principal.WindowsPrincipal($id)
    return $p.IsInRole([Security.Principal.WindowsBuiltinRole]::Administrator)
}

if (-not (Test-IsAdmin)) {
    Write-Error "This script must be run as Administrator."
    exit 1
}

$hostsPath = "$env:SystemRoot\System32\drivers\etc\hosts"
$backupPath = "$hostsPath.bak.$((Get-Date).ToString('yyyyMMddHHmmss'))"
Copy-Item -Path $hostsPath -Destination $backupPath -Force

$existing = Get-Content -Path $hostsPath -ErrorAction Stop

foreach ($entry in $Entries) {
    if ($existing -notcontains $entry) {
        Add-Content -Path $hostsPath -Value $entry
        Write-Output "Added: $entry"
    } else {
        Write-Output "Exists: $entry"
    }
}

# Flush DNS cache
ipconfig /flushdns | Out-Null
Write-Output "Done. Backup saved to $backupPath"