param(
    [Parameter(Mandatory=$true)][int]$ProcessId,
    [string]$CsvPath = 'data/eog/vbd/processed/vbd_monthly_wpp573_grid.csv',
    [string]$ProgressPath = 'data/eog/vbd/processed/vbd_progress_live.json',
    [string]$StartMonth = '2015-01',
    [string]$EndMonth = '2025-12',
    [int]$IntervalSeconds = 30
)

function Get-MonthRange([string]$Start, [string]$End) {
    $cursor = [datetime]::ParseExact($Start, 'yyyy-MM', $null)
    $last = [datetime]::ParseExact($End, 'yyyy-MM', $null)
    $months = [System.Collections.Generic.List[string]]::new()
    while ($cursor -le $last) {
        $months.Add($cursor.ToString('yyyy-MM'))
        $cursor = $cursor.AddMonths(1)
    }
    return $months
}

$targets = @(Get-MonthRange $StartMonth $EndMonth)
$parent = Split-Path -Parent $ProgressPath
New-Item -ItemType Directory -Force -Path $parent | Out-Null

do {
    $completed = @()
    $rows = 0
    if (Test-Path -LiteralPath $CsvPath) {
        $lines = @(Get-Content -LiteralPath $CsvPath)
        if ($lines.Count -gt 1) {
            $rows = $lines.Count - 1
            $completed = @($lines | Select-Object -Skip 1 | ForEach-Object {
                $parts = $_ -split ',', 2
                if ($parts.Count -gt 0) { $parts[0] }
            } | Sort-Object -Unique)
        }
    }
    $remaining = @($targets | Where-Object { $_ -notin $completed })
    $process = Get-Process -Id $ProcessId -ErrorAction SilentlyContinue
    $status = if ($process) { 'running' } elseif ($remaining.Count -eq 0) { 'complete' } else { 'stopped' }
    [pscustomobject]@{
        status = $status
        completed_months = $completed.Count
        target_months = $targets.Count
        completed = $completed
        remaining_months = $remaining
        rows = $rows
        updated_at = (Get-Date).ToString('o')
        process_id = $ProcessId
    } | ConvertTo-Json -Depth 4 | Set-Content -LiteralPath $ProgressPath -Encoding UTF8
    if (-not $process) { break }
    Start-Sleep -Seconds $IntervalSeconds
} while ($true)
