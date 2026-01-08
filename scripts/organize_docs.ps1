# Script de Organização de Documentação
# Consolida 130+ arquivos MD em estrutura organizada

$baseDir = "C:\xampp\htdocs\comexamesul"

# Definir categorização dos documentos
$categories = @{
    "01-getting-started" = @(
        "README.md",
        "QUICK_START.md",
        "GUIA_PRIMEIRO_ACESSO.md",
        "GUIA_RAPIDO_REFERENCIA.md",
        "GUIA_UTILIZADOR_INDICE.md"
    )
    
    "02-development" = @(
        "DESIGN_SYSTEM.md",
        "ANALISE_CODEBASE_2025.md",
        "COMO_TESTAR.txt",
        "GUIA_TESTE_CORRECOES.md",
        "GUIA_TESTE_DRAG_DROP.md",
        "GUIA_TESTE_PERFORMANCE.md",
        "TESTES_V2.5.md",
        "TESTE_AGORA.md",
        "TESTE_CRIAR_JURIS.md",
        "TESTE_DRAG_DROP.md",
        "TESTE_NOME_APLICACAO.md",
        "TESTE_VAGAS.md",
        "TESTE_VALIDACAO_EXCLUSIVIDADE.md"
    )
    
    "03-deployment" = @(
        "README_DEPLOY.md",
        "DEPLOY_RAPIDO.md",
        "GUIA_DEPLOY_PRODUCAO.md",
        "PLANO_DEPLOY_CPANEL.md",
        "CHECKLIST_DEPLOY.md",
        "CHECKLIST_DEPLOY_CPANEL.md",
        "CHECKLIST_FINAL.md",
        "COMANDOS_DEPLOY_CPANEL.md",
        "COMANDOS_PRODUCAO.md",
        "COMANDOS_RAPIDOS.md",
        "GUIA_INSTALACAO_MIGRATIONS.md",
        "INSTALACAO_DADOS_MESTRES.md",
        "INSTALACAO_DND.md",
        "INSTALACAO_TOP3.md",
        "INSTALACAO_V2.5.md",
        "INSTALAR_INDICES.md",
        "EXECUTAR_MIGRATION_AGORA.md",
        "UPLOAD_VENDOR.md",
        "FAQ_TROUBLESHOOTING.md",
        "TROUBLESHOOTING_503.md",
        "RESOLVER_ERRO_INTERNO.md",
        "RESUMO_DEPLOY_CYCODE.md"
    )
    
    "04-user-guides" = @(
        "GUIA_UTILIZADOR_PARTE1.md",
        "GUIA_UTILIZADOR_PARTE2.md",
        "GUIA_UTILIZADOR_PARTE3.md",
        "GUIA_ALOCACAO_EQUIPE.md",
        "GUIA_CRIACAO_JURIS_POR_LOCAL.md",
        "GUIA_VISUAL_TOP3.md",
        "SISTEMA_GESTAO_CANDIDATURAS_VIGILANTE.md",
        "SISTEMA_ALTERACAO_DISPONIBILIDADE.md",
        "SISTEMA_CANCELAMENTO_JUSTIFICADO.md",
        "SISTEMA_REVISAO_CANDIDATURAS.md",
        "SISTEMA_AJUDA_CONTEXTUAL.md"
    )
    
    "05-api-reference" = @(
        "DOCUMENTACAO_INDEX.md",
        "INDICE_DOCUMENTACAO.md",
        "README_AUTO_ALLOCATION.md",
        "README_SMART_SUGGESTIONS.md",
        "SISTEMA_ALOCACAO_DND.md",
        "SISTEMA_TOP3_RESUMO.md",
        "NOVAS_FUNCIONALIDADES.md"
    )
    
    "changelog" = @(
        "CHANGELOG_V2.md",
        "MUDANCAS_IMPLEMENTADAS.txt",
        "MUDANCAS_MENU.md",
        "FASE1_COMPLETA.md",
        "IMPLEMENTACAO_V2.2_GUIA.md",
        "IMPLEMENTACAO_RAPIDA.md",
        "IMPLEMENTACAO_COMPLETA_AUTO_ALLOCATION.md",
        "IMPLEMENTACAO_DADOS_MESTRES.md",
        "IMPLEMENTACOES_ALOCACAO_REALIZADAS.md"
    )
    
    "archive" = @(
        # Documentos de correções/implementações específicas (histórico)
        "AGRUPAMENTO_ALOCACAO.md",
        "AJUSTES_LAYOUT_PLANNING.md",
        "ANALISE_CANDIDATURAS_PARTE1.md",
        "ANALISE_CANDIDATURAS_PARTE2.md",
        "ANALISE_SUGESTOES_2025.md",
        "ANALISE_SUGESTOES_MELHORIA.md",
        "APLICAR_SANITIZACAO_VIEWS.md",
        "BLOQUEIO_VAGAS_FECHADAS_IMPLEMENTADO.md",
        "BRANDING_APLICADO.md",
        "CACHE_IMPLEMENTADO.md",
        "CARDS_VAGAS_DINAMICOS.md",
        "CORRECAO_AUTENTICACAO.md",
        "CORRECAO_LAYOUT_LOGIN.md",
        "CORRECAO_LAYOUT_VAGAS.md",
        "CORRECAO_MENSAGENS_FLASH.md",
        "CORRECOES_CRITICAS_IMPLEMENTADAS.md",
        "CORRECOES_EXCLUSAO_JURIS.md",
        "CORRECOES_JURIS_IMPLEMENTADAS.md",
        "CORRECOES_SELECT_IMPLEMENTADAS.md",
        "CORRECOES_TOP3.md",
        "CRIACAO_JURIS_ALOCACOES.md",
        "ENCERRAMENTO_AUTOMATICO_VAGAS.md",
        "ESTADOS_VAGAS_EXPLICACAO.md",
        "ESTADOS_VAGAS_IMPLEMENTADO.md",
        "GESTAO_JURIS_MELHORADA.md",
        "HOMEPAGE_NOVO_DESIGN.md",
        "JURY_CREATION_SYSTEM_V3.md",
        "LAYOUT_FIXO_IMPLEMENTADO.md",
        "MELHORIAS_ALOCACAO_EQUIPE.md",
        "MELHORIAS_IMPLEMENTADAS_HOJE.md",
        "MELHORIAS_PLANEJAMENTO_IMPLEMENTADAS.md",
        "MELHORIAS_PROPOSTAS_2025.md",
        "MELHORIAS_VAGAS_PORTAL.md",
        "MENSAGENS_FLASH_MELHORADAS.md",
        "MENU_ACCORDION_IMPLEMENTADO.md",
        "N+1_QUERIES_RESOLVIDO.md",
        "NAVBAR_DROPDOWN_DOCS.md",
        "NOME_APLICACAO_ATUALIZADO.md",
        "PERFIL_ORGANIZADO_VIGILANTE.md",
        "PLANEAMENTO_JURIS_POR_VAGA.md",
        "PROFISSIONALIZACAO_COMPLETA.md",
        "PROPOSTA_MELHORIAS_CANDIDATURAS.md",
        "PROPOSTA_MELHORIAS_COMPLETA.md",
        "PROTECAO_EXCLUSAO_VAGAS.md",
        "PROXIMOS_PASSOS_IMEDIATOS.md",
        "REESTRUTURACAO_GESTAO_ALOCACOES.md",
        "RENOMEACAO_MENUS.md",
        "REORGANIZACAO_JURIS.md",
        "RESPONSIVIDADE_LOGIN_MELHORADA.md",
        "RESUMO_ANALISE.md",
        "RESUMO_CORRECOES_SELECT.md",
        "RESUMO_EXECUTIVO.md",
        "RESUMO_IMPLEMENTACAO_DND.md",
        "SEGURANCA_CRITICA.md",
        "SEGURANCA_CRITICA_IMPLEMENTADA.md",
        "SESSAO_14_OUTUBRO_2025.md",
        "SIMPLIFICACAO_MENU.md",
        "SIMPLIFICACAO_SISTEMA.md",
        "SISTEMA_PRONTO_SEM_TRIGGERS.md",
        "SOLUCAO_RAPIDA.txt",
        "STATUS_DRAG_DROP.md",
        "SUGESTOES_MELHORIA.md",
        "VALIDACOES_JURIS_IMPLEMENTADO.md",
        "VALIDACOES_MOCAMBICANAS.md",
        "XSS_PROTECTION_IMPLEMENTADA.md"
    )
}

# Função para mover arquivos com verificação
function Move-DocFile {
    param(
        [string]$fileName,
        [string]$category
    )
    
    $sourcePath = Join-Path $baseDir $fileName
    $destPath = Join-Path $baseDir "docs\$category\$fileName"
    
    if (Test-Path $sourcePath) {
        Write-Host "Movendo $fileName -> docs/$category/" -ForegroundColor Green
        Move-Item -Path $sourcePath -Destination $destPath -Force
        return $true
    } else {
        Write-Host "Arquivo não encontrado: $fileName" -ForegroundColor Yellow
        return $false
    }
}

# Executar movimentação
$totalMoved = 0
$notFound = @()

Write-Host "`n=== INICIANDO ORGANIZAÇÃO DA DOCUMENTAÇÃO ===" -ForegroundColor Cyan
Write-Host "Base: $baseDir`n" -ForegroundColor Cyan

foreach ($category in $categories.Keys) {
    Write-Host "`n[$category]" -ForegroundColor Magenta
    
    foreach ($file in $categories[$category]) {
        if (Move-DocFile -fileName $file -category $category) {
            $totalMoved++
        } else {
            $notFound += $file
        }
    }
}

# Relatório final
Write-Host "`n=== RELATÓRIO ===" -ForegroundColor Cyan
Write-Host "Total de arquivos movidos: $totalMoved" -ForegroundColor Green

if ($notFound.Count -gt 0) {
    Write-Host "`nArquivos não encontrados ($($notFound.Count)):" -ForegroundColor Yellow
    $notFound | ForEach-Object { Write-Host "  - $_" -ForegroundColor Yellow }
}

Write-Host ""
Write-Host "[OK] Organizacao concluida!" -ForegroundColor Green
Write-Host "Estrutura criada em: docs/" -ForegroundColor Cyan
