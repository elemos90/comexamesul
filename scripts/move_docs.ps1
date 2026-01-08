# Script Simplificado de Organizacao de Documentacao

Write-Host "=== MOVENDO DOCUMENTOS ===" -ForegroundColor Cyan

# Getting Started
Move-Item "README.md" "docs\01-getting-started\" -ErrorAction SilentlyContinue
Move-Item "QUICK_START.md" "docs\01-getting-started\" -ErrorAction SilentlyContinue
Move-Item "GUIA_PRIMEIRO_ACESSO.md" "docs\01-getting-started\" -ErrorAction SilentlyContinue
Move-Item "GUIA_RAPIDO_REFERENCIA.md" "docs\01-getting-started\" -ErrorAction SilentlyContinue
Move-Item "GUIA_UTILIZADOR_INDICE.md" "docs\01-getting-started\" -ErrorAction SilentlyContinue

# Development
Move-Item "DESIGN_SYSTEM.md" "docs\02-development\" -ErrorAction SilentlyContinue
Move-Item "ANALISE_CODEBASE_2025.md" "docs\02-development\" -ErrorAction SilentlyContinue
Move-Item "GUIA_TESTE_*.md" "docs\02-development\" -ErrorAction SilentlyContinue
Move-Item "TESTE_*.md" "docs\02-development\" -ErrorAction SilentlyContinue
Move-Item "TESTES_*.md" "docs\02-development\" -ErrorAction SilentlyContinue

# Deployment
Move-Item "README_DEPLOY.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "DEPLOY_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "GUIA_DEPLOY_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "PLANO_DEPLOY_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "CHECKLIST_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "COMANDOS_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "GUIA_INSTALACAO_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "INSTALACAO_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "INSTALAR_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "EXECUTAR_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "UPLOAD_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "FAQ_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "TROUBLESHOOTING_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "RESOLVER_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue
Move-Item "RESUMO_DEPLOY_*.md" "docs\03-deployment\" -ErrorAction SilentlyContinue

# User Guides
Move-Item "GUIA_UTILIZADOR_*.md" "docs\04-user-guides\" -ErrorAction SilentlyContinue
Move-Item "GUIA_ALOCACAO_*.md" "docs\04-user-guides\" -ErrorAction SilentlyContinue
Move-Item "GUIA_CRIACAO_*.md" "docs\04-user-guides\" -ErrorAction SilentlyContinue
Move-Item "GUIA_VISUAL_*.md" "docs\04-user-guides\" -ErrorAction SilentlyContinue
Move-Item "SISTEMA_GESTAO_*.md" "docs\04-user-guides\" -ErrorAction SilentlyContinue
Move-Item "SISTEMA_ALTERACAO_*.md" "docs\04-user-guides\" -ErrorAction SilentlyContinue
Move-Item "SISTEMA_CANCELAMENTO_*.md" "docs\04-user-guides\" -ErrorAction SilentlyContinue
Move-Item "SISTEMA_REVISAO_*.md" "docs\04-user-guides\" -ErrorAction SilentlyContinue
Move-Item "SISTEMA_AJUDA_*.md" "docs\04-user-guides\" -ErrorAction SilentlyContinue

# API Reference
Move-Item "DOCUMENTACAO_INDEX.md" "docs\05-api-reference\" -ErrorAction SilentlyContinue
Move-Item "INDICE_DOCUMENTACAO.md" "docs\05-api-reference\" -ErrorAction SilentlyContinue
Move-Item "README_AUTO_*.md" "docs\05-api-reference\" -ErrorAction SilentlyContinue
Move-Item "README_SMART_*.md" "docs\05-api-reference\" -ErrorAction SilentlyContinue
Move-Item "SISTEMA_ALOCACAO_*.md" "docs\05-api-reference\" -ErrorAction SilentlyContinue
Move-Item "SISTEMA_TOP3_*.md" "docs\05-api-reference\" -ErrorAction SilentlyContinue
Move-Item "NOVAS_FUNCIONALIDADES.md" "docs\05-api-reference\" -ErrorAction SilentlyContinue

# Changelog
Move-Item "CHANGELOG_*.md" "docs\changelog\" -ErrorAction SilentlyContinue
Move-Item "MUDANCAS_*.md" "docs\changelog\" -ErrorAction SilentlyContinue
Move-Item "FASE*.md" "docs\changelog\" -ErrorAction SilentlyContinue
Move-Item "IMPLEMENTACAO_*.md" "docs\changelog\" -ErrorAction SilentlyContinue
Move-Item "IMPLEMENTACOES_*.md" "docs\changelog\" -ErrorAction SilentlyContinue

# Archive (todos os outros)
Move-Item "*.md" "docs\archive\" -ErrorAction SilentlyContinue

Write-Host "[OK] Documentos organizados!" -ForegroundColor Green
