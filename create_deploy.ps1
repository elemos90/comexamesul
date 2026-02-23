$source = 'c:\xampp\htdocs\comexamesul'
$zipName = 'deploy_jogos_v2.zip'
$zipPath = Join-Path $source $zipName

# Remove old zip if exists
if (Test-Path $zipPath) { Remove-Item $zipPath }

# Create temp staging directory
$staging = Join-Path $env:TEMP 'deploy_jogos_v2'
if (Test-Path $staging) { Remove-Item $staging -Recurse -Force }
New-Item -ItemType Directory -Path $staging -Force | Out-Null

# Copy essential directories
$dirs = @('app', 'config', 'database', 'vendor')
foreach ($d in $dirs) {
    $src = Join-Path $source $d
    $dst = Join-Path $staging $d
    if (Test-Path $src) {
        Copy-Item $src $dst -Recurse -Force
        Write-Host "Copied: $d"
    }
}

# Create storage directory structure (empty)
$storageDirs = @('storage', 'storage/logs', 'storage/cache', 'storage/sessions', 'storage/uploads')
foreach ($sd in $storageDirs) {
    New-Item -ItemType Directory -Path (Join-Path $staging $sd) -Force | Out-Null
}
foreach ($sd in @('storage/logs', 'storage/cache', 'storage/sessions', 'storage/uploads')) {
    New-Item -ItemType File -Path (Join-Path $staging "$sd/.gitkeep") -Force | Out-Null
}

# Copy public directory (only production files)
$publicSrc = Join-Path $source 'public'
$publicDst = Join-Path $staging 'public'
New-Item -ItemType Directory -Path $publicDst -Force | Out-Null

# Copy public subdirectories
foreach ($pd in @('css', 'js', 'img', 'assets', 'uploads')) {
    $psrc = Join-Path $publicSrc $pd
    if (Test-Path $psrc) {
        Copy-Item $psrc (Join-Path $publicDst $pd) -Recurse -Force
        Write-Host "Copied: public/$pd"
    }
}

# Copy only essential public files
$publicFiles = @('.htaccess', 'index.php', 'debug_path.php')
foreach ($pf in $publicFiles) {
    $pfSrc = Join-Path $publicSrc $pf
    if (Test-Path $pfSrc) {
        Copy-Item $pfSrc (Join-Path $publicDst $pf) -Force
    }
}

# Copy root files
$rootFiles = @('.htaccess', 'bootstrap.php', 'index.php', 'composer.json', 'composer.lock', '.env.production')
foreach ($rf in $rootFiles) {
    $rfSrc = Join-Path $source $rf
    if (Test-Path $rfSrc) {
        Copy-Item $rfSrc (Join-Path $staging $rf) -Force
        Write-Host "Copied: $rf"
    }
}

# Create ZIP
Add-Type -Assembly System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($staging, $zipPath)

$zipSize = [math]::Round((Get-Item $zipPath).Length / 1MB, 1)
Write-Host "ZIP created: $zipName ($zipSize MB)"

# Cleanup staging
Remove-Item $staging -Recurse -Force
Write-Host "Done!"
