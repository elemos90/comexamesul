# Script de Limpeza do Diretorio /public
# Move scripts auxiliares, testes e legados para locais apropriados

Write-Host "=== LIMPEZA DO DIRETORIO /public ===" -ForegroundColor Cyan
Write-Host ""

$baseDir = "C:\xampp\htdocs\comexamesul"
$publicDir = Join-Path $baseDir "public"

# Criar diretorios de destino
Write-Host "Criando estrutura de diretorios..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "$baseDir\legacy" | Out-Null
New-Item -ItemType Directory -Force -Path "$baseDir\tests\public" | Out-Null
New-Item -ItemType Directory -Force -Path "$baseDir\docs\archive\config" | Out-Null

$moved = 0
$total = 0

# Funcao para mover arquivo
function Move-SafeFile {
    param($source, $dest, $category)
    
    if (Test-Path $source) {
        Write-Host "[$category] $(Split-Path $source -Leaf) -> $dest" -ForegroundColor Green
        Move-Item -Path $source -Destination $dest -Force
        $script:moved++
    }
    $script:total++
}

Write-Host ""
Write-Host "--- MOVENDO ARQUIVOS ---" -ForegroundColor Magenta
Write-Host ""

# 1. Scripts de Instalacao -> /scripts
Write-Host "[INSTALACAO]" -ForegroundColor Cyan
Move-SafeFile "$publicDir\install.php" "$baseDir\scripts\" "INSTALL"
Move-SafeFile "$publicDir\install_master_data.php" "$baseDir\scripts\" "INSTALL"
Move-SafeFile "$publicDir\fix_juries_table.php" "$baseDir\scripts\" "INSTALL"
Move-SafeFile "$publicDir\ping.php" "$baseDir\scripts\" "UTIL"

# 2. Arquivos de Teste -> /tests/public
Write-Host ""
Write-Host "[TESTES]" -ForegroundColor Cyan
Move-SafeFile "$publicDir\test.php" "$baseDir\tests\public\" "TEST"
Move-SafeFile "$publicDir\test.html" "$baseDir\tests\public\" "TEST"
Move-SafeFile "$publicDir\test-drag.html" "$baseDir\tests\public\" "TEST"
Move-SafeFile "$publicDir\test_master_data.php" "$baseDir\tests\public\" "TEST"
Move-SafeFile "$publicDir\test_routes.php" "$baseDir\tests\public\" "TEST"
Move-SafeFile "$publicDir\index.php.test" "$baseDir\tests\public\" "TEST"
Move-SafeFile "$publicDir\check.php" "$baseDir\tests\public\" "TEST"

# 3. Scripts Legados/Diretos -> /legacy
Write-Host ""
Write-Host "[LEGADO]" -ForegroundColor Cyan
Move-SafeFile "$publicDir\alocar_equipe.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\criar_juri.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\dashboard_direto.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\distribuicao_automatica.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\login_direto.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\logout_direto.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\mapa_alocacoes.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\relatorios.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\ver_disciplinas.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\ver_locais.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\ver_salas.php" "$baseDir\legacy\" "LEGACY"
Move-SafeFile "$publicDir\get_rooms.php" "$baseDir\legacy\" "LEGACY"

# 4. Arquivos .htaccess extras -> docs/archive/config
Write-Host ""
Write-Host "[CONFIG]" -ForegroundColor Cyan
Move-SafeFile "$publicDir\.htaccess.minimal" "$baseDir\docs\archive\config\" "CONFIG"
Move-SafeFile "$publicDir\.htaccess.production" "$baseDir\docs\archive\config\" "CONFIG"
Move-SafeFile "$publicDir\.htaccess.test" "$baseDir\docs\archive\config\" "CONFIG"

# Relatorio Final
Write-Host ""
Write-Host "=== RELATORIO ===" -ForegroundColor Cyan
Write-Host "Arquivos movidos: $moved de $total" -ForegroundColor Green
Write-Host ""

# Verificar o que restou em /public
Write-Host "Arquivos restantes em /public:" -ForegroundColor Yellow
Get-ChildItem -Path $publicDir -File | Select-Object Name | Format-Table -AutoSize

Write-Host ""
Write-Host "[OK] Limpeza concluida!" -ForegroundColor Green
Write-Host "- Scripts de instalacao: scripts/" -ForegroundColor Cyan
Write-Host "- Testes: tests/public/" -ForegroundColor Cyan
Write-Host "- Scripts legados: legacy/" -ForegroundColor Cyan
Write-Host "- Configs antigas: docs/archive/config/" -ForegroundColor Cyan
