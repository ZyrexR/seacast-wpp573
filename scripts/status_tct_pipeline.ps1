$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$partsRoot = Join-Path $projectRoot 'data/eog/vbd/processed/parts'
$names = @(
    'vbd_2024_05_to_2024_09',
    'vbd_2024_10_to_2025_02',
    'vbd_2025_03_to_2025_07',
    'vbd_2025_08_to_2025_12'
)
$rows = foreach ($name in $names) {
    $path = Join-Path $partsRoot "${name}_progress.json"
    if (Test-Path -LiteralPath $path) {
        $p = Get-Content -LiteralPath $path -Raw | ConvertFrom-Json
        [PSCustomObject]@{
            Batch = $name
            Status = $p.status
            Bulan = "$($p.completed_months)/$($p.target_months)"
            Terakhir = @($p.completed | Select-Object -Last 1)[0]
            Diperbarui = $p.updated_at
        }
    } else {
        [PSCustomObject]@{ Batch = $name; Status = 'pending'; Bulan = '0/5'; Terakhir = ''; Diperbarui = '' }
    }
}
$rows | Format-Table -AutoSize
$finalPath = Join-Path $projectRoot 'data/eog/vbd/processed/vbd_monthly_wpp573_grid_manifest.json'
$modelPath = Join-Path $projectRoot 'outputs/tct_hybrid_2015_2025/reports/run_manifest.json'
Write-Output "EOG final: $(if (Test-Path -LiteralPath $finalPath) { 'ada (periksa manifest)' } else { 'belum' })"
Write-Output "Model: $(if (Test-Path -LiteralPath $modelPath) { 'ada (periksa metrik validasi)' } else { 'belum' })"
