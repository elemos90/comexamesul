$folders = @('vendor', 'app', 'public', 'config', 'database', 'storage', '.git')
foreach ($f in $folders) {
    if (Test-Path $f) {
        $size = (Get-ChildItem -Path $f -Recurse -File -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum / 1MB
        "{0}: {1:N2} MB" -f $f, $size
    }
}
$totalSize = (Get-ChildItem -Path . -Recurse -File -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum / 1MB
"TOTAL PROJECT SIZE: {0:N2} MB" -f $totalSize
