# 🚀 Guia de Deploy - Produção admissao.cycode.net

**Domínio**: admissao.cycode.net  
**Usuário**: cycodene  
**IP**: 57.128.126.160  
**Data**: 17 de Outubro de 2025

---

## 📋 Checklist Pré-Deploy

### ✅ Preparação Local

- [ ] Backup completo do banco de dados local
- [ ] Testar todas as funcionalidades críticas
- [ ] Verificar dependências do Composer
- [ ] Limpar arquivos temporários e logs
- [ ] Validar configurações de segurança

---

## 🔧 Passo 1: Preparar Arquivos para Upload

### 1.1. Criar .env de Produção

Crie um arquivo `.env.production` localmente com estas configurações:

```env
# ====================================
# CONFIGURAÇÃO DA APLICAÇÃO - PRODUÇÃO
# ====================================

APP_NAME="Portal da Comissão de Exames de Admissão"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admissao.cycode.net
APP_TIMEZONE=Africa/Maputo

# ====================================
# BANCO DE DADOS PRODUÇÃO
# ====================================

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cycodene_comexames
DB_USERNAME=cycodene_dbuser
DB_PASSWORD=SENHA_FORTE_AQUI_TROCAR

# ====================================
# CONFIGURAÇÃO DE EMAIL
# ====================================

MAIL_FROM_NAME="Portal da Comissão de Exames"
MAIL_FROM_ADDRESS="noreply@admissao.cycode.net"
MAIL_SMTP_HOST=smtp.gmail.com
MAIL_SMTP_PORT=587
MAIL_SMTP_USER=seu_email@gmail.com
MAIL_SMTP_PASS=sua_senha_app_gmail
MAIL_SMTP_ENCRYPTION=tls

# ====================================
# SEGURANÇA - PRODUÇÃO
# ====================================

SESSION_NAME=exam_portal_prod
SESSION_LIFETIME=7200
SESSION_SECURE=true
SESSION_COOKIE_DOMAIN=.cycode.net

RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_WINDOW=900

CSRF_TOKEN_KEY=csrf_token
```

### 1.2. Arquivos a NÃO Enviar

Adicione ao `.gitignore` ou exclua antes de fazer upload:

```
/vendor/
/node_modules/
.env
.env.local
composer.lock
package-lock.json
*.log
/storage/logs/*.log
/storage/cache/*
/public/uploads/*
test_*.php
debug_*.php
temp_*.php
.DS_Store
Thumbs.db
```

### 1.3. Comprimir Projeto

```bash
# No Windows (PowerShell)
Compress-Archive -Path c:\xampp\htdocs\comexamesul\* -DestinationPath c:\xampp\htdocs\comexamesul-deploy.zip -Force

# Ou use WinRAR/7-Zip manualmente
# Exclua: vendor/, storage/logs/, storage/cache/, public/uploads/
```

---

## 🌐 Passo 2: Upload para Servidor

### 2.1. Conectar via FTP/SFTP

**Opção 1: FileZilla**
- Host: `ftp.cycode.net` ou `57.128.126.160`
- Usuário: `cycodene`
- Senha: (sua senha de hospedagem)
- Porta: 21 (FTP) ou 22 (SFTP)

**Opção 2: cPanel File Manager**
- URL: `https://cycode.net:2083` ou `https://cycode.net/cpanel`
- Login com usuário `cycodene`

### 2.2. Estrutura de Diretórios no Servidor

```
/home/cycodene/
├── public_html/              # ⚠️ NÃO colocar aqui
│   └── (outros sites)
├── admissao.cycode.net/      # ✅ Criar esta pasta
│   ├── app/
│   ├── bootstrap.php
│   ├── composer.json
│   ├── config/
│   ├── public/               # ⬅️ DocumentRoot
│   ├── scripts/
│   ├── storage/
│   └── .env                  # Criar depois
└── logs/
```

### 2.3. Upload dos Arquivos

1. Crie pasta `admissao.cycode.net` em `/home/cycodene/`
2. Faça upload do arquivo `comexamesul-deploy.zip`
3. Descompacte no servidor (cPanel > File Manager > Extract)
4. Ou envie pasta por pasta via FTP

---

## 🗄️ Passo 3: Configurar Banco de Dados

### 3.1. Criar Banco via cPanel

1. Acesse **cPanel > MySQL Databases**
2. Criar novo banco:
   - Nome: `cycodene_comexames`
   
3. Criar usuário:
   - Usuário: `cycodene_dbuser`
   - Senha: (gerar senha forte)
   
4. Adicionar usuário ao banco:
   - Permissões: **ALL PRIVILEGES**

### 3.2. Importar Estrutura do Banco

**Via phpMyAdmin** (cPanel > phpMyAdmin):

```sql
-- 1. Importar estrutura básica
-- Arquivo: app/Database/migrations.sql

-- 2. Importar dados mestres
-- Arquivo: app/Database/migrations_master_data.sql

-- 3. Importar sistema de alocação
-- Arquivo: app/Database/migrations_auto_allocation.sql

-- 4. Importar views e triggers
-- Arquivo: app/Database/migrations_triggers.sql

-- 5. Importar índices de performance
-- Arquivo: app/Database/performance_indexes.sql
```

**Ordem de importação**:
1. `migrations.sql`
2. `migrations_v2.2.sql`
3. `migrations_v2.3.sql`
4. `migrations_v2.5.sql`
5. `migrations_master_data_simple.sql`
6. `migrations_auto_allocation.sql`
7. `migrations_triggers.sql`
8. `performance_indexes.sql`

### 3.3. Criar Usuário Administrador

```sql
INSERT INTO users (
    name, email, phone, role, password_hash, 
    email_verified_at, available_for_vigilance, 
    supervisor_eligible, created_at, updated_at
) VALUES (
    'Coordenador Principal',
    'coordenador@admissao.cycode.net',
    '+258841234567',
    'coordenador',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    NOW(),
    0,
    1,
    NOW(),
    NOW()
);
```

**⚠️ IMPORTANTE**: Altere a senha imediatamente após primeiro login!

---

## ⚙️ Passo 4: Configurar .env no Servidor

### 4.1. Criar arquivo .env

Via cPanel File Manager ou FTP, crie `/home/cycodene/admissao.cycode.net/.env`:

```env
APP_NAME="Portal da Comissão de Exames de Admissão"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admissao.cycode.net
APP_TIMEZONE=Africa/Maputo

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cycodene_comexames
DB_USERNAME=cycodene_dbuser
DB_PASSWORD=SENHA_DO_BANCO_AQUI

MAIL_FROM_NAME="Portal da Comissão de Exames"
MAIL_FROM_ADDRESS="noreply@admissao.cycode.net"
MAIL_SMTP_HOST=smtp.gmail.com
MAIL_SMTP_PORT=587
MAIL_SMTP_USER=seu_email@gmail.com
MAIL_SMTP_PASS=senha_app_gmail
MAIL_SMTP_ENCRYPTION=tls

SESSION_NAME=exam_portal_prod
SESSION_LIFETIME=7200
SESSION_SECURE=true

RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_WINDOW=900

CSRF_TOKEN_KEY=csrf_token
```

### 4.2. Proteger arquivo .env

```bash
# Via SSH ou File Manager > Permissions
chmod 600 .env
```

---

## 🔒 Passo 5: Configurar Domínio e SSL

### 5.1. Apontar DocumentRoot (cPanel)

1. **cPanel > Domains > Addon Domains** ou **Subdomains**
2. Adicionar:
   - Subdomain: `admissao`
   - Domain: `cycode.net`
   - Document Root: `/home/cycodene/admissao.cycode.net/public`

### 5.2. Ativar SSL/HTTPS (Let's Encrypt)

1. **cPanel > SSL/TLS > Manage SSL**
2. Ou **cPanel > SSL/TLS Status**
3. Selecionar `admissao.cycode.net`
4. Clicar "Run AutoSSL" ou "Install Free SSL"

**Aguardar 5-10 minutos** para propagação do certificado.

### 5.3. Forçar HTTPS

Verificar se `.htaccess` tem:

```apache
# Forçar HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 📦 Passo 6: Instalar Dependências Composer

### 6.1. Via SSH (Recomendado)

```bash
# Conectar via SSH
ssh cycodene@57.128.126.160

# Navegar para pasta
cd ~/admissao.cycode.net

# Instalar dependências
composer install --no-dev --optimize-autoloader

# Se não tiver composer globalmente:
php composer.phar install --no-dev --optimize-autoloader
```

### 6.2. Via cPanel Terminal

Se SSH não estiver disponível:
1. cPanel > Terminal
2. Executar comandos acima

### 6.3. Sem acesso SSH

**Upload manual da pasta vendor/**:
1. Executar `composer install` localmente
2. Fazer upload da pasta `vendor/` completa via FTP

---

## 🔐 Passo 7: Configurar Permissões

### 7.1. Permissões de Pastas

```bash
# Via SSH
cd ~/admissao.cycode.net

# Storage (escrita)
chmod -R 775 storage/
chmod -R 775 storage/logs/
chmod -R 775 storage/cache/
chmod -R 775 public/uploads/

# Criar .gitkeep se não existir
touch storage/logs/.gitkeep
touch storage/cache/.gitkeep
touch public/uploads/.gitkeep
touch public/uploads/avatars/.gitkeep
```

### 7.2. Verificar Propriedade

```bash
# Garantir que Apache pode escrever
chown -R cycodene:cycodene storage/
chown -R cycodene:cycodene public/uploads/
```

---

## 📝 Passo 8: Verificar .htaccess

### 8.1. Arquivo public/.htaccess

Verificar se existe e tem este conteúdo:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Forçar HTTPS em produção
    RewriteCond %{HTTPS} off
    RewriteCond %{HTTP_HOST} admissao\.cycode\.net
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Redirecionar para index.php
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Segurança adicional
<FilesMatch "\.(env|sql|md|log|ini)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# PHP Settings
php_flag display_errors Off
php_flag log_errors On
php_value error_log /home/cycodene/logs/php_errors.log

# Proteger arquivos sensíveis
<Files .env>
    Order allow,deny
    Deny from all
</Files>

<Files composer.json>
    Order allow,deny
    Deny from all
</Files>
```

### 8.2. Arquivo raiz/.htaccess (opcional)

Na raiz do projeto (`/home/cycodene/admissao.cycode.net/.htaccess`):

```apache
# Bloquear acesso direto à raiz
Order deny,allow
Deny from all

# Permitir apenas acesso à pasta public
<IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>
```

---

## ⏰ Passo 9: Configurar Cron Jobs

### 9.1. Adicionar Cron (cPanel > Cron Jobs)

**Comando**:
```bash
/usr/bin/php /home/cycodene/admissao.cycode.net/app/Cron/check_deadlines.php >> /home/cycodene/logs/cron.log 2>&1
```

**Frequência**: A cada 30 minutos
- Minutos: `*/30`
- Horas: `*`
- Dia: `*`
- Mês: `*`
- Dia da semana: `*`

### 9.2. Verificar Execução

```bash
# Via SSH
tail -f ~/logs/cron.log
```

---

## 🧪 Passo 10: Testar Sistema

### 10.1. Checklist de Testes

- [ ] Acessar https://admissao.cycode.net
- [ ] Verificar redirecionamento HTTP → HTTPS
- [ ] Testar login com usuário criado
- [ ] Verificar dashboard carrega corretamente
- [ ] Testar criação de vaga
- [ ] Testar criação de júri
- [ ] Testar sistema de candidaturas
- [ ] Testar drag-and-drop de alocação
- [ ] Verificar envio de emails
- [ ] Testar upload de avatar
- [ ] Verificar logs de erro

### 10.2. Verificar Logs

```bash
# Via SSH ou cPanel File Manager
tail -f /home/cycodene/logs/php_errors.log
tail -f /home/cycodene/admissao.cycode.net/storage/logs/app.log
```

### 10.3. Teste de Performance

```bash
# Via SSH - verificar tempo de resposta
curl -w "@curl-format.txt" -o /dev/null -s https://admissao.cycode.net
```

---

## 🔒 Passo 11: Segurança Pós-Deploy

### 11.1. Alterar Senhas Padrão

```sql
-- Alterar senha do coordenador
UPDATE users 
SET password_hash = '$2y$10$NOVA_HASH_AQUI'
WHERE email = 'coordenador@admissao.cycode.net';
```

Ou via interface web após login.

### 11.2. Limpar Dados de Teste

```sql
-- Remover usuários de teste
DELETE FROM users WHERE email LIKE '%@example.com';

-- Remover júris de teste
DELETE FROM juries WHERE created_at < '2025-10-17';
```

### 11.3. Configurar Firewall

Via cPanel > IP Blocker, bloquear IPs suspeitos se necessário.

### 11.4. Backup Automático

Configurar backup automático via cPanel:
1. **cPanel > Backup > Backup Wizard**
2. Ativar backups diários
3. Armazenar em local remoto (Google Drive, Dropbox)

---

## 🐛 Troubleshooting Comum

### Erro: "500 Internal Server Error"

```bash
# Verificar logs
tail -f ~/logs/php_errors.log

# Verificar permissões
chmod -R 775 storage/

# Verificar .htaccess
# Comentar linha por linha para identificar problema
```

### Erro: "Database connection failed"

```bash
# Verificar credenciais no .env
cat .env | grep DB_

# Testar conexão MySQL
mysql -u cycodene_dbuser -p cycodene_comexames
```

### Erro: "Composer dependencies missing"

```bash
cd ~/admissao.cycode.net
composer install --no-dev --optimize-autoloader
```

### CSS/JS não carregam

```html
<!-- Verificar URLs no código -->
<!-- Trocar de relativo para absoluto -->
<link href="<?= env('APP_URL') ?>/css/app.css">
```

### Emails não enviam

```env
# Verificar configurações SMTP no .env
# Testar com Gmail App Password
# Verificar porta 587 ou 465
```

---

## 📊 Pós-Deploy: Monitoramento

### 11.1. Ferramentas Recomendadas

1. **UptimeRobot** - Monitorar disponibilidade
   - URL: https://uptimerobot.com
   - Configurar alerta a cada 5 minutos

2. **Google Analytics** - Análise de uso
   
3. **Sentry** (opcional) - Error tracking

### 11.2. Métricas a Monitorar

- Tempo de resposta (< 2s)
- Taxa de erro (< 1%)
- Uso de CPU/RAM
- Espaço em disco
- Conexões MySQL

---

## 📋 Checklist Final

### Antes de Anunciar Produção

- [ ] SSL/HTTPS funcionando
- [ ] Banco de dados configurado e populado
- [ ] Credenciais de produção no .env
- [ ] Composer dependencies instaladas
- [ ] Permissões corretas (storage, uploads)
- [ ] Cron job ativo
- [ ] Backup inicial criado
- [ ] Senhas padrão alteradas
- [ ] Todos os testes passando
- [ ] Logs verificados (sem erros)
- [ ] Emails de teste enviados com sucesso
- [ ] Documentação de usuário pronta
- [ ] Plano de rollback preparado

---

## 🆘 Suporte e Manutenção

### Backup Regular

```bash
# Backup manual (SSH)
cd /home/cycodene
tar -czf backup-$(date +%Y%m%d).tar.gz admissao.cycode.net/
mysqldump -u cycodene_dbuser -p cycodene_comexames > db-backup-$(date +%Y%m%d).sql
```

### Atualização do Sistema

```bash
# 1. Fazer backup
# 2. Upload novos arquivos
# 3. Executar migrations
# 4. Limpar cache
rm -rf storage/cache/*
```

### Contatos Importantes

- Hospedagem: suporte@cycode.net
- Desenvolvedor: (seu contato)
- Documentação: /docs/

---

## 🎉 Sistema em Produção!

Após completar todos os passos:

**URL**: https://admissao.cycode.net  
**Login**: coordenador@admissao.cycode.net  
**Senha**: (definida por você)

**Boa sorte com o deploy! 🚀**

---

**Criado em**: 17 de Outubro de 2025  
**Última atualização**: 17 de Outubro de 2025  
**Versão do Sistema**: 2.5+
