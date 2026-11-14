param(
    [int]$PollSeconds = 30
)

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$progressPaths = @(
    'vbd_2024_05_to_2024_09_progress.json',
    'vbd_2024_10_to_2025_02_progress.json',
    'vbd_2025_03_to_2025_07_progress.json',
    'vbd_2025_08_to_2025_12_progress.json'
) | ForEach-Object { Join-Path $projectRoot "data/eog/vbd/processed/parts/$_" }
$reportRoot = Join-Path $projectRoot 'outputs/tct_hybrid_2015_2025/reports'
$qgisPython = 'C:\Program Files\QGIS 4.0.3\apps\Python312\python.exe'
$modelPython = Join-Path $projectRoot '.venv-tct/Scripts/python.exe'
New-Item -ItemType Directory -Force -Path $reportRoot | Out-Null
Set-Location -LiteralPath $projectRoot

while ($true) {
    $complete = 0
    foreach ($progressPath in $progressPaths) {
        if (-not (Test-Path -LiteralPath $progressPath)) { continue }
        $progress = Get-Content -LiteralPath $progressPath -Raw | ConvertFrom-Json
        if ($progress.status -eq 'failed') {
            throw "EOG batch failed: $($progress.error)"
        }
        if ($progress.status -eq 'complete') {
            $complete++
        }
    }
    if ($complete -eq $progressPaths.Count) { break }
    Start-Sleep -Seconds $PollSeconds
}

& $qgisPython 'scripts/merge_vbd_parts.py' *> (Join-Path $reportRoot 'merge_vbd.log')
if ($LASTEXITCODE -ne 0) {
    throw "EOG merge failed; see $reportRoot/merge_vbd.log"
}
$ErrorActionPreference = 'Continue' # tqdm writes progress to stderr; exit code remains the error signal.
& $modelPython 'scripts/run_tct_hybrid_notebook.py' 1> (Join-Path $reportRoot 'notebook_execution.log') 2> (Join-Path $reportRoot 'notebook_execution.stderr.log')
if ($LASTEXITCODE -ne 0) {
    throw "Notebook failed; see $reportRoot/notebook_execution.log and notebook_execution.stderr.log"
}
& $modelPython 'scripts/evaluate_tct_scientific_gate.py' 1> (Join-Path $reportRoot 'scientific_gate.log') 2>&1
if ($LASTEXITCODE -ne 0) {
    throw "Scientific gate failed; see $reportRoot/scientific_gate.log"
}
Write-Output "TCT pipeline finished; inspect $reportRoot before web integration."
