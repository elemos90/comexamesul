# ============================================
# Script de Deploy - jogos.cycode.net
# Gera um ZIP limpo com apenas ficheiros de produção
# ============================================

$ProjectRoot = "c:\xampp\htdocs\comexamesul"
$OutputZip = "$ProjectRoot\deploy_jogos.zip"
$TempDir = "$env:TEMP\comexamesul_deploy"

Write-Host "=== Build Deploy Package ===" -ForegroundColor Cyan
Write-Host "Projecto: $ProjectRoot"
Write-Host "Output: $OutputZip"
Write-Host ""

# Limpar temp anterior
if (Test-Path $TempDir) {
    Remove-Item $TempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $TempDir | Out-Null

# === PASTAS OBRIGATÓRIAS ===
$folders = @(
    "app",
    "config",
    "public",
    "storage",
    "vendor",
    "src"
)

foreach ($folder in $folders) {
    $src = Join-Path $ProjectRoot $folder
    $dst = Join-Path $TempDir $folder
    if (Test-Path $src) {
        Write-Host "  Copiando $folder/" -ForegroundColor Green
        Copy-Item $src $dst -Recurse -Force
    }
}

# === FICHEIROS OBRIGATÓRIOS NA RAIZ ===
$rootFiles = @(
    "bootstrap.php",
    "index.php",
    "composer.json",
    "composer.lock",
    ".htaccess"
)

foreach ($file in $rootFiles) {
    $src = Join-Path $ProjectRoot $file
    if (Test-Path $src) {
        Write-Host "  Copiando $file" -ForegroundColor Green
        Copy-Item $src (Join-Path $TempDir $file)
    }
}

# === .ENV DE PRODUÇÃO (será renomeado para .env) ===
$envProd = Join-Path $ProjectRoot ".env.production"
if (Test-Path $envProd) {
    Write-Host "  Copiando .env.production -> .env" -ForegroundColor Yellow
    Copy-Item $envProd (Join-Path $TempDir ".env")
}

# === CRIAR storage/logs/ se não existir ===
$logsDir = Join-Path $TempDir "storage\logs"
if (!(Test-Path $logsDir)) {
    New-Item -ItemType Directory -Path $logsDir -Force | Out-Null
}
# Criar .gitkeep para manter a pasta
"" | Out-File -FilePath "$logsDir\.gitkeep" -Encoding utf8

# === LIMPAR FICHEIROS DESNECESSÁRIOS DO DEPLOY ===
Write-Host ""
Write-Host "Limpando ficheiros desnecessários..." -ForegroundColor Yellow

# Remover ficheiros debug/test da pasta public
$publicCleanup = @(
    "public\debug_auth.php",
    "public\debug_auth.txt",
    "public\diagnostic.php",
    "public\test_direct.php",
    "public\test_simple.php",
    "public\test_wizard.php",
    "public\run_migration_recovery.php",
    "public\sync_roles.php"
)

foreach ($file in $publicCleanup) {
    $target = Join-Path $TempDir $file
    if (Test-Path $target) {
        Remove-Item $target -Force
        Write-Host "  Removido: $file" -ForegroundColor DarkGray
    }
}

# Remover .bak files
Get-ChildItem $TempDir -Recurse -Filter "*.bak" | ForEach-Object {
    Remove-Item $_.FullName -Force
    Write-Host "  Removido: $($_.Name)" -ForegroundColor DarkGray
}

# Remover vendor dev dependencies dirs (phpunit, phpstan)
$devDirs = @(
    "vendor\phpunit",
    "vendor\phpstan"
)
foreach ($dir in $devDirs) {
    $target = Join-Path $TempDir $dir
    if (Test-Path $target) {
        Remove-Item $target -Recurse -Force
        Write-Host "  Removido: $dir/" -ForegroundColor DarkGray
    }
}

# === GERAR O ZIP ===
Write-Host ""
Write-Host "Gerando ZIP..." -ForegroundColor Cyan

if (Test-Path $OutputZip) {
    Remove-Item $OutputZip -Force
}

# Comprimir a partir da pasta temp (para que os ficheiros fiquem na raiz do ZIP)
Compress-Archive -Path "$TempDir\*" -DestinationPath $OutputZip -CompressionLevel Optimal

# Limpar pasta temp
Remove-Item $TempDir -Recurse -Force

# Resultado
$zipSize = (Get-Item $OutputZip).Length / 1MB
Write-Host ""
Write-Host "=== Deploy package criado ===" -ForegroundColor Green
Write-Host "Ficheiro: $OutputZip" -ForegroundColor Green  
Write-Host "Tamanho: $([math]::Round($zipSize, 2)) MB" -ForegroundColor Green
Write-Host ""
Write-Host "--- INSTRUÇÕES ---" -ForegroundColor Yellow
Write-Host "1. Aceda ao cPanel -> File Manager -> jogos.cycode.net"
Write-Host "2. APAGUE todos os ficheiros existentes na pasta"
Write-Host "3. Faça upload do deploy_jogos.zip"
Write-Host "4. Clique com botão direito -> Extract"
Write-Host "5. Verifique que storage/logs tem permissões 755"
Write-Host "6. Aceda a https://jogos.cycode.net/"
Write-Host ""
