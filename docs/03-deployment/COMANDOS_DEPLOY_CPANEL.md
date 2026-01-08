# 🚀 Comandos Rápidos - Deploy cPanel

**Referência rápida para deploy e manutenção via SSH/Terminal cPanel**

---

## 🔌 Conexão SSH

```bash
# Conectar ao servidor
ssh cycodene@57.128.126.160

# Se solicitar senha, inserir senha do cPanel
```

---

## 📥 Clone do Repositório

```bash
# Navegar para home
cd ~

# Backup de instalação anterior (se existir)
mv admissao.cycode.net admissao.cycode.net.backup_$(date +%Y%m%d_%H%M%S)

# Clonar repositório do GitHub
git clone https://github.com/elemos90/comexamesul.git admissao.cycode.net

# Entrar na pasta
cd admissao.cycode.net

# Verificar arquivos
ls -la
```

---

## ⚙️ Configuração Inicial

```bash
# Navegar para projeto
cd ~/admissao.cycode.net

# Criar arquivo .env a partir do exemplo
cp .env.example .env

# Editar arquivo .env
nano .env
# Ou usar vi:
vi .env

# Configurar permissões do .env
chmod 600 .env

# Criar diretórios necessários
mkdir -p storage/cache storage/logs public/uploads/avatars

# Configurar permissões de escrita
chmod -R 775 storage/
chmod -R 775 public/uploads/

# Criar arquivos .gitkeep
touch storage/cache/.gitkeep
touch storage/logs/.gitkeep
touch public/uploads/.gitkeep
touch public/uploads/avatars/.gitkeep
```

---

## 📦 Instalar Dependências Composer

```bash
cd ~/admissao.cycode.net

# Verificar se Composer está instalado
which composer

# Opção 1: Composer global
composer install --no-dev --optimize-autoloader

# Opção 2: Composer local (composer.phar)
php composer.phar install --no-dev --optimize-autoloader

# Opção 3: Baixar e instalar Composer
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader

# Verificar instalação
ls -la vendor/
```

---

## 🗄️ Base de Dados - Comandos MySQL

```bash
# Conectar ao MySQL
mysql -u cycodene_cycodene -p cycodene_comexamesul
# Senha: &~Oi)0SXsPNh7$bF

# Listar tabelas
SHOW TABLES;

# Verificar estrutura de tabela
DESCRIBE users;

# Contar registros
SELECT COUNT(*) FROM users;

# Criar usuário administrador
INSERT INTO users (
    name, email, phone, role, password_hash, 
    email_verified_at, available_for_vigilance, 
    supervisor_eligible, created_at, updated_at
) VALUES (
    'Coordenador Principal',
    'coordenador@cycode.net',
    '+258840000000',
    'coordenador',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    NOW(),
    0,
    1,
    NOW(),
    NOW()
);

# Sair do MySQL
EXIT;
```

---

## 📋 Importar Base de Dados via Linha de Comando

```bash
cd ~/admissao.cycode.net/app/Database

# Importar cada migration na ordem correta
mysql -u cycodene_cycodene -p cycodene_comexamesul < migrations.sql
mysql -u cycodene_cycodene -p cycodene_comexamesul < migrations_v2.2.sql
mysql -u cycodene_cycodene -p cycodene_comexamesul < migrations_v2.3.sql
mysql -u cycodene_cycodene -p cycodene_comexamesul < migrations_v2.5.sql
mysql -u cycodene_cycodene -p cycodene_comexamesul < migrations_master_data_simple.sql
mysql -u cycodene_cycodene -p cycodene_comexamesul < migrations_auto_allocation.sql
mysql -u cycodene_cycodene -p cycodene_comexamesul < migrations_triggers.sql
mysql -u cycodene_cycodene -p cycodene_comexamesul < performance_indexes.sql

# Senha para todos: &~Oi)0SXsPNh7$bF
```

---

## ⏰ Configurar Cron Job

```bash
# Editar crontab
crontab -e

# Adicionar linha (pressionar 'i' para inserir no vi):
*/30 * * * * /usr/bin/php /home/cycodene/admissao.cycode.net/app/Cron/check_deadlines.php >> /home/cycodene/logs/cron.log 2>&1

# Salvar e sair:
# - No vi: pressionar ESC, depois :wq e ENTER
# - No nano: CTRL+X, depois Y e ENTER

# Listar cron jobs ativos
crontab -l

# Criar diretório de logs se não existir
mkdir -p ~/logs
touch ~/logs/cron.log
```

---

## 🔍 Verificação e Testes

```bash
# Verificar versão do PHP
php -v

# Verificar extensões PHP
php -m | grep -E "pdo|mysql|mbstring|json|fileinfo"

# Testar conexão com banco de dados
mysql -u cycodene_cycodene -p cycodene_comexamesul -e "SELECT DATABASE();"

# Verificar permissões
ls -la ~/admissao.cycode.net/storage/
ls -la ~/admissao.cycode.net/public/uploads/

# Verificar arquivo .env
cat ~/admissao.cycode.net/.env | grep -E "APP_|DB_"

# Teste rápido do site
curl -I https://admissao.cycode.net
```

---

## 📝 Logs - Visualização

```bash
# Logs da aplicação (tempo real)
tail -f ~/admissao.cycode.net/storage/logs/app.log

# Logs de erro PHP (tempo real)
tail -f ~/logs/php_errors.log

# Logs do Cron (tempo real)
tail -f ~/logs/cron.log

# Ver últimas 50 linhas de log
tail -50 ~/admissao.cycode.net/storage/logs/app.log

# Buscar erros específicos
grep "ERROR" ~/admissao.cycode.net/storage/logs/app.log

# Ver tamanho dos logs
du -sh ~/admissao.cycode.net/storage/logs/
```

---

## 🧹 Limpeza e Manutenção

```bash
# Limpar cache
rm -rf ~/admissao.cycode.net/storage/cache/*

# Limpar logs antigos (cuidado!)
rm ~/admissao.cycode.net/storage/logs/*.log
touch ~/admissao.cycode.net/storage/logs/app.log

# Limpar sessões antigas
find ~/admissao.cycode.net/storage/sessions/ -type f -mtime +7 -delete

# Verificar uso de disco
du -sh ~/admissao.cycode.net/
du -sh ~/admissao.cycode.net/* | sort -h

# Verificar espaço livre
df -h
```

---

## 💾 Backup

```bash
# Criar diretório de backups
mkdir -p ~/backups

# Backup completo do projeto
cd ~
tar -czf ~/backups/admissao-$(date +%Y%m%d_%H%M%S).tar.gz admissao.cycode.net/

# Backup apenas da aplicação (sem vendor)
tar -czf ~/backups/admissao-app-$(date +%Y%m%d_%H%M%S).tar.gz \
    --exclude='admissao.cycode.net/vendor' \
    --exclude='admissao.cycode.net/storage/cache/*' \
    --exclude='admissao.cycode.net/storage/logs/*' \
    admissao.cycode.net/

# Backup do banco de dados
mysqldump -u cycodene_cycodene -p cycodene_comexamesul > ~/backups/db-$(date +%Y%m%d_%H%M%S).sql

# Backup completo (projeto + banco)
cd ~
tar -czf ~/backups/full-backup-$(date +%Y%m%d_%H%M%S).tar.gz \
    admissao.cycode.net/ \
    backups/db-$(date +%Y%m%d_%H%M%S).sql

# Listar backups
ls -lh ~/backups/

# Remover backups antigos (mais de 30 dias)
find ~/backups/ -type f -mtime +30 -delete
```

---

## 🔄 Atualização do Sistema

```bash
# Fazer backup antes de atualizar
cd ~
tar -czf ~/backups/pre-update-$(date +%Y%m%d_%H%M%S).tar.gz admissao.cycode.net/

# Entrar no projeto
cd ~/admissao.cycode.net

# Puxar atualizações do Git
git pull origin main

# Ou atualizar branch específico
git fetch --all
git checkout main
git pull

# Atualizar dependências
composer install --no-dev --optimize-autoloader

# Se houver novas migrations, executar
cd ~/admissao.cycode.net/app/Database
# Importar novos arquivos SQL via phpMyAdmin ou:
mysql -u cycodene_cycodene -p cycodene_comexamesul < nova_migration.sql

# Limpar cache
rm -rf ~/admissao.cycode.net/storage/cache/*

# Verificar logs após atualização
tail -f ~/admissao.cycode.net/storage/logs/app.log
```

---

## 🔐 Segurança

```bash
# Verificar permissões de arquivos sensíveis
ls -la ~/admissao.cycode.net/.env
ls -la ~/admissao.cycode.net/config/

# Corrigir permissões se necessário
chmod 600 ~/admissao.cycode.net/.env
chmod 644 ~/admissao.cycode.net/config/*.php

# Verificar propriedade dos arquivos
ls -la ~/admissao.cycode.net/ | head -20

# Alterar proprietário (se necessário)
chown -R cycodene:cycodene ~/admissao.cycode.net/

# Verificar arquivos que não devem estar acessíveis
curl -I https://admissao.cycode.net/.env
curl -I https://admissao.cycode.net/composer.json
# Ambos devem retornar 403 Forbidden
```

---

## 🔧 Resolução de Problemas

### Erro 500 - Internal Server Error

```bash
# Verificar logs de erro
tail -50 ~/logs/php_errors.log
tail -50 ~/admissao.cycode.net/storage/logs/app.log

# Verificar permissões
chmod -R 775 ~/admissao.cycode.net/storage/
chmod -R 775 ~/admissao.cycode.net/public/uploads/

# Verificar .htaccess
cat ~/admissao.cycode.net/public/.htaccess

# Testar PHP syntax
php -l ~/admissao.cycode.net/public/index.php
```

### Erro de Conexão ao Banco

```bash
# Testar conexão
mysql -u cycodene_cycodene -p cycodene_comexamesul

# Verificar credenciais no .env
cat ~/admissao.cycode.net/.env | grep DB_

# Verificar host do banco
echo "SELECT 1;" | mysql -u cycodene_cycodene -p cycodene_comexamesul
```

### Composer não funciona

```bash
# Baixar Composer localmente
cd ~/admissao.cycode.net
curl -sS https://getcomposer.org/installer | php

# Usar composer.phar
php composer.phar install --no-dev --optimize-autoloader

# Verificar versão
php composer.phar --version
```

### Site não carrega (página em branco)

```bash
# Ativar debug temporariamente
nano ~/admissao.cycode.net/.env
# Alterar: APP_DEBUG=true

# Acessar o site e verificar erro
# Depois DESATIVAR: APP_DEBUG=false

# Verificar logs
tail -50 ~/logs/php_errors.log
```

---

## 📊 Monitoramento

```bash
# Verificar processos PHP ativos
ps aux | grep php

# Verificar uso de CPU e memória
top -u cycodene

# Verificar conexões MySQL
mysqladmin -u cycodene_cycodene -p processlist

# Verificar tamanho do banco de dados
mysql -u cycodene_cycodene -p -e "
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'cycodene_comexamesul'
GROUP BY table_schema;
"

# Estatísticas do site (acessos)
tail -100 ~/access_logs/admissao.cycode.net | awk '{print $1}' | sort | uniq -c | sort -rn | head -10
```

---

## 🎯 Comandos Essenciais Resumidos

```bash
# Conectar
ssh cycodene@57.128.126.160

# Navegar
cd ~/admissao.cycode.net

# Ver logs
tail -f storage/logs/app.log

# Limpar cache
rm -rf storage/cache/*

# Backup rápido
tar -czf ~/backup-$(date +%Y%m%d).tar.gz ~/admissao.cycode.net/
mysqldump -u cycodene_cycodene -p cycodene_comexamesul > ~/db-$(date +%Y%m%d).sql

# Atualizar
git pull
composer install --no-dev --optimize-autoloader

# Verificar status
curl -I https://admissao.cycode.net
```

---

## 📞 Informações de Acesso

```bash
# SSH
ssh cycodene@57.128.126.160

# MySQL
mysql -u cycodene_cycodene -p cycodene_comexamesul
# Senha: &~Oi)0SXsPNh7$bF

# Paths importantes
~/admissao.cycode.net/           # Projeto
~/admissao.cycode.net/public/    # DocumentRoot
~/logs/                          # Logs do servidor
~/backups/                       # Backups
```

---

**📅 Criado em**: 20 de Outubro de 2025  
**🔄 Última atualização**: 20 de Outubro de 2025  
**📌 Versão**: 1.0
