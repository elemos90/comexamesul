# Script para Baixar Assets de CDN para Local
# Reduz dependencia de CDNs externos

Write-Host "=== DOWNLOAD DE ASSETS LOCAIS ===" -ForegroundColor Cyan
Write-Host ""

$baseDir = "C:\xampp\htdocs\comexamesul"
$assetsDir = Join-Path $baseDir "public\assets\libs"

# Criar diretorio se nao existir
if (-not (Test-Path $assetsDir)) {
    New-Item -ItemType Directory -Force -Path $assetsDir | Out-Null
    Write-Host "[OK] Diretorio criado: $assetsDir" -ForegroundColor Green
}

$downloads = @(
    @{
        Name = "Tailwind CSS Standalone"
        Url = "https://cdn.tailwindcss.com/3.4.1/tailwind.min.js"
        Output = "tailwind.min.js"
        Description = "Framework CSS utility-first"
    },
    @{
        Name = "jQuery"
        Url = "https://code.jquery.com/jquery-3.7.1.min.js"
        Output = "jquery-3.7.1.min.js"
        Description = "Biblioteca JavaScript (necessaria para Toastr)"
    },
    @{
        Name = "Toastr JS"
        Url = "https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"
        Output = "toastr.min.js"
        Description = "Notificacoes toast"
    },
    @{
        Name = "Toastr CSS"
        Url = "https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css"
        Output = "toastr.min.css"
        Description = "Estilos para notificacoes"
    },
    @{
        Name = "Alpine.js"
        Url = "https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"
        Output = "alpine-3.13.3.min.js"
        Description = "Framework JavaScript reativo"
    },
    @{
        Name = "SortableJS"
        Url = "https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
        Output = "sortable-1.15.0.min.js"
        Description = "Biblioteca drag-and-drop"
    }
)

$downloaded = 0
$failed = 0

foreach ($asset in $downloads) {
    Write-Host "[BAIXANDO] $($asset.Name)" -ForegroundColor Yellow
    Write-Host "   URL: $($asset.Url)" -ForegroundColor Gray
    
    $outputPath = Join-Path $assetsDir $asset.Output
    
    try {
        # Verificar se ja existe
        if (Test-Path $outputPath) {
            $fileSize = (Get-Item $outputPath).Length
            Write-Host "   [EXISTE] Arquivo ja existe ($fileSize bytes)" -ForegroundColor Cyan
            Write-Host "   Deseja sobrescrever? (S/N)" -ForegroundColor Yellow
            # Pular se existir para automacao
            Write-Host "   [SKIP] Mantendo arquivo existente" -ForegroundColor Gray
            $downloaded++
        } else {
            # Baixar arquivo
            Invoke-WebRequest -Uri $asset.Url -OutFile $outputPath -UseBasicParsing
            
            $fileSize = (Get-Item $outputPath).Length
            $fileSizeKB = [math]::Round($fileSize / 1KB, 2)
            
            Write-Host "   [OK] Baixado com sucesso ($fileSizeKB KB)" -ForegroundColor Green
            $downloaded++
        }
    } catch {
        Write-Host "   [ERRO] Falha ao baixar: $($_.Exception.Message)" -ForegroundColor Red
        $failed++
    }
    
    Write-Host ""
}

# Relatorio
Write-Host "=== RELATORIO ===" -ForegroundColor Cyan
Write-Host "Assets processados: $($downloads.Count)" -ForegroundColor White
Write-Host "Sucesso: $downloaded" -ForegroundColor Green
Write-Host "Falhas: $failed" -ForegroundColor $(if ($failed -gt 0) { "Red" } else { "Gray" })
Write-Host ""

if ($failed -eq 0) {
    Write-Host "[OK] Todos os assets foram baixados!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Proximo passo:" -ForegroundColor Yellow
    Write-Host "  1. Atualizar referencias no layout (main.php)" -ForegroundColor Cyan
    Write-Host "  2. Testar aplicacao" -ForegroundColor Cyan
    Write-Host "  3. Adicionar fallbacks CDN (opcional)" -ForegroundColor Cyan
} else {
    Write-Host "[AVISO] Alguns downloads falharam" -ForegroundColor Yellow
    Write-Host "Tente executar novamente ou baixe manualmente" -ForegroundColor Gray
}

Write-Host ""
Write-Host "Localizacao: $assetsDir" -ForegroundColor Cyan
