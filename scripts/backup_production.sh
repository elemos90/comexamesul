#!/bin/bash
# ============================================
# SCRIPT DE BACKUP - PRODUÇÃO
# admissao.cycode.net
# ============================================
#
# Uso: bash backup_production.sh
# Ou: chmod +x backup_production.sh && ./backup_production.sh
#
# ============================================

# Configurações
APP_PATH="/home/cycodene/admissao.cycode.net"
BACKUP_PATH="/home/cycodene/backups"
DB_NAME="cycodene_comexames"
DB_USER="cycodene_dbuser"
DB_PASS="SENHA_DO_BANCO_AQUI"  # ⚠️ ALTERAR
DATE=$(date +%Y%m%d_%H%M%S)

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  BACKUP PRODUÇÃO - admissao.cycode.net                     ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Criar pasta de backup se não existir
if [ ! -d "$BACKUP_PATH" ]; then
    echo -e "${YELLOW}→ Criando pasta de backups...${NC}"
    mkdir -p "$BACKUP_PATH"
fi

# ============================================
# 1. BACKUP DO BANCO DE DADOS
# ============================================

echo -e "${YELLOW}→ Fazendo backup do banco de dados...${NC}"

mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_PATH/db_${DATE}.sql"

if [ $? -eq 0 ]; then
    # Comprimir SQL
    gzip "$BACKUP_PATH/db_${DATE}.sql"
    
    DB_SIZE=$(du -h "$BACKUP_PATH/db_${DATE}.sql.gz" | cut -f1)
    echo -e "${GREEN}✓ Backup do banco criado: db_${DATE}.sql.gz (${DB_SIZE})${NC}"
else
    echo -e "${RED}✗ Erro ao fazer backup do banco de dados${NC}"
    exit 1
fi

# ============================================
# 2. BACKUP DOS ARQUIVOS
# ============================================

echo -e "${YELLOW}→ Fazendo backup dos arquivos...${NC}"

# Excluir pastas temporárias e logs
tar -czf "$BACKUP_PATH/files_${DATE}.tar.gz" \
    --exclude="$APP_PATH/vendor" \
    --exclude="$APP_PATH/node_modules" \
    --exclude="$APP_PATH/storage/logs/*.log" \
    --exclude="$APP_PATH/storage/cache/*" \
    --exclude="$APP_PATH/.git" \
    -C /home/cycodene \
    admissao.cycode.net

if [ $? -eq 0 ]; then
    FILES_SIZE=$(du -h "$BACKUP_PATH/files_${DATE}.tar.gz" | cut -f1)
    echo -e "${GREEN}✓ Backup dos arquivos criado: files_${DATE}.tar.gz (${FILES_SIZE})${NC}"
else
    echo -e "${RED}✗ Erro ao fazer backup dos arquivos${NC}"
    exit 1
fi

# ============================================
# 3. BACKUP COMPLETO (OPCIONAL)
# ============================================

echo -e "${YELLOW}→ Criando backup completo combinado...${NC}"

tar -czf "$BACKUP_PATH/full_backup_${DATE}.tar.gz" \
    -C "$BACKUP_PATH" \
    "db_${DATE}.sql.gz" \
    "files_${DATE}.tar.gz"

if [ $? -eq 0 ]; then
    FULL_SIZE=$(du -h "$BACKUP_PATH/full_backup_${DATE}.tar.gz" | cut -f1)
    echo -e "${GREEN}✓ Backup completo criado: full_backup_${DATE}.tar.gz (${FULL_SIZE})${NC}"
fi

# ============================================
# 4. LIMPEZA DE BACKUPS ANTIGOS
# ============================================

echo -e "${YELLOW}→ Limpando backups antigos (>30 dias)...${NC}"

# Remover backups individuais com mais de 30 dias
find "$BACKUP_PATH" -name "db_*.sql.gz" -type f -mtime +30 -delete
find "$BACKUP_PATH" -name "files_*.tar.gz" -type f -mtime +30 -delete

# Manter apenas os últimos 10 backups completos
cd "$BACKUP_PATH"
ls -t full_backup_*.tar.gz | tail -n +11 | xargs -r rm

REMAINING=$(ls -1 "$BACKUP_PATH"/full_backup_*.tar.gz 2>/dev/null | wc -l)
echo -e "${GREEN}✓ Backups antigos removidos. Backups mantidos: ${REMAINING}${NC}"

# ============================================
# 5. RESUMO
# ============================================

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  BACKUP CONCLUÍDO COM SUCESSO                              ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "Data/Hora: ${DATE}"
echo -e "Localização: ${BACKUP_PATH}"
echo ""
echo -e "Arquivos criados:"
echo -e "  • db_${DATE}.sql.gz"
echo -e "  • files_${DATE}.tar.gz"
echo -e "  • full_backup_${DATE}.tar.gz"
echo ""

# Listar últimos backups
echo -e "${YELLOW}Últimos 5 backups completos:${NC}"
ls -lht "$BACKUP_PATH"/full_backup_*.tar.gz | head -5 | awk '{print "  " $9 " (" $5 ")"}'
echo ""

# ============================================
# OPCIONAL: ENVIAR PARA LOCAL REMOTO
# ============================================

# Descomentar para enviar via SCP para servidor remoto
# scp "$BACKUP_PATH/full_backup_${DATE}.tar.gz" user@remote-server:/path/to/backups/

# Descomentar para enviar para Google Drive (requer rclone)
# rclone copy "$BACKUP_PATH/full_backup_${DATE}.tar.gz" gdrive:backups/admissao/

echo -e "${GREEN}✓ Backup finalizado!${NC}"
echo ""

exit 0
