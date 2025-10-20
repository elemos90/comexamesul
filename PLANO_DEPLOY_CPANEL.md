# 🚀 Plano de Deploy via cPanel - Portal Comissão de Exames

**Domínio**: admissao.cycode.net  
**Data do Plano**: 20 de Outubro de 2025  
**Versão do Sistema**: 2.5+

---

## 📋 Informações do Servidor

```
Usuário cPanel:     cycodene
IP Compartilhado:   57.128.126.160
Domínio Destino:    admissao.cycode.net

Banco de Dados:     cycodene_comexamesul
Usuário DB:         cycodene_cycodene
Senha DB:           &~Oi)0SXsPNh7$bF
Host DB:            localhost

Repositório:        https://github.com/elemos90/comexamesul.git
```

---

## 🎯 Visão Geral do Processo

### Etapas Principais:
1. **Preparação Local** - 30 minutos
2. **Configuração cPanel** - 30 minutos  
3. **Clone do Repositório** - 15 minutos
4. **Configuração Base de Dados** - 45 minutos
5. **Instalação Dependências** - 30 minutos
6. **Configuração SSL/HTTPS** - 20 minutos
7. **Testes e Validação** - 1 hora

**Tempo Total Estimado**: 3-4 horas

---

## ⏱️ FASE 1: Preparação Local (30 min)

### 1.1. Verificar Requisitos do Sistema

```bash
# Executar no ambiente local para validar o projeto
cd c:\xampp\htdocs\comexamesul
php -v  # Verificar PHP 8.1+
```

**Requisitos do Servidor**:
- ✅ PHP 8.1 ou superior
- ✅ MySQL 8.0 ou superior
- ✅ Extensões PHP: `pdo_mysql`, `mbstring`, `json`, `fileinfo`, `zip`
- ✅ Composer instalado
- ✅ Acesso SSH (recomendado) ou Terminal cPanel

### 1.2. Preparar Arquivo .env de Produção

Criar localmente `c:\xampp\htdocs\comexamesul\.env.production`:

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
DB_DATABASE=cycodene_comexamesul
DB_USERNAME=cycodene_cycodene
DB_PASSWORD=&~Oi)0SXsPNh7$bF

# ====================================
# CONFIGURAÇÃO DE EMAIL (CONFIGURAR DEPOIS)
# ====================================

MAIL_FROM_NAME="Portal da Comissão de Exames"
MAIL_FROM_ADDRESS="noreply@cycode.net"
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

### 1.3. Checklist Pré-Deploy

- [ ] Backup do banco de dados local criado
- [ ] Arquivo `.env.production` criado
- [ ] Credenciais de acesso cPanel confirmadas
- [ ] Credenciais do banco de dados validadas
- [ ] Repositório GitHub acessível

---

## 🌐 FASE 2: Acesso ao cPanel (15 min)

### 2.1. Acessar cPanel

**Opções de Acesso**:
```
URL Principal:  https://cycode.net:2083
URL Alternativa: https://57.128.126.160:2083
Usuário: cycodene
Senha: [sua senha de hospedagem]
```

### 2.2. Verificar Versão do PHP

1. Acessar **cPanel > MultiPHP Manager**
2. Verificar domínio `admissao.cycode.net`
3. Garantir que está usando **PHP 8.1** ou superior
4. Se necessário, alterar versão do PHP

### 2.3. Verificar Extensões PHP

1. **cPanel > MultiPHP INI Editor**
2. Verificar extensões habilitadas:
   - `pdo_mysql` ✅
   - `mbstring` ✅
   - `json` ✅
   - `fileinfo` ✅
   - `zip` ✅
   - `gd` ✅ (para geração de PDFs)

---

## 📂 FASE 3: Clone do Repositório (30 min)

### 3.1. Acesso SSH (Método Recomendado)

```bash
# Conectar ao servidor via SSH
ssh cycodene@57.128.126.160

# Navegar para o diretório home
cd ~

# Verificar se existe pasta admissao.cycode.net
ls -la

# Se existir, fazer backup
mv admissao.cycode.net admissao.cycode.net.backup_$(date +%Y%m%d)

# Clonar repositório
git clone https://github.com/elemos90/comexamesul.git admissao.cycode.net

# Entrar na pasta
cd admissao.cycode.net

# Verificar arquivos
ls -la
```

### 3.2. Via Terminal cPanel (Se SSH não disponível)

1. **cPanel > Terminal**
2. Executar os mesmos comandos acima

### 3.3. Alternativa: Upload Manual

**Se Git não estiver disponível**:

1. Baixar repositório localmente:
   ```bash
   git clone https://github.com/elemos90/comexamesul.git
   cd comexamesul
   ```

2. Criar arquivo ZIP (excluir vendor/, .git/, storage/logs/):
   ```bash
   # PowerShell
   Compress-Archive -Path * -DestinationPath comexamesul-deploy.zip
   ```

3. **cPanel > File Manager**:
   - Navegar para `/home/cycodene/`
   - Upload do arquivo `comexamesul-deploy.zip`
   - Clicar com botão direito > Extract
   - Renomear para `admissao.cycode.net`

### 3.4. Estrutura Esperada

```
/home/cycodene/admissao.cycode.net/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Views/
│   ├── Database/
│   └── ...
├── config/
├── public/          # ← DocumentRoot
├── scripts/
├── storage/
│   ├── cache/
│   └── logs/
├── bootstrap.php
├── composer.json
├── .htaccess
└── README.md
```

---

## 🗄️ FASE 4: Configurar Banco de Dados (45 min)

### 4.1. Verificar Banco de Dados Existente

1. **cPanel > MySQL Databases**
2. Procurar banco: `cycodene_comexamesul`
3. Verificar usuário: `cycodene_cycodene`

**Se o banco JÁ EXISTIR**:
- ⚠️ **CUIDADO**: Fazer backup antes de qualquer alteração
- Prosseguir para 4.3

**Se o banco NÃO EXISTIR**:
- Seguir para 4.2

### 4.2. Criar Banco de Dados (se necessário)

**Via cPanel > MySQL Databases**:

1. **Criar Novo Banco**:
   - Nome: `comexamesul` (será criado como `cycodene_comexamesul`)
   - Criar

2. **Criar Usuário**:
   - Nome: `cycodene` (será criado como `cycodene_cycodene`)
   - Senha: `&~Oi)0SXsPNh7$bF`
   - Criar Usuário

3. **Adicionar Usuário ao Banco**:
   - Banco: `cycodene_comexamesul`
   - Usuário: `cycodene_cycodene`
   - Permissões: **ALL PRIVILEGES**
   - Adicionar

### 4.3. Importar Estrutura do Banco

**Via phpMyAdmin** (cPanel > phpMyAdmin):

1. Selecionar banco `cycodene_comexamesul`

2. **Aba "Import"** - Importar arquivos nesta ordem:

   **Ordem de Importação**:
   ```
   1. migrations.sql                          (estrutura básica)
   2. migrations_v2.2.sql                     (melhorias v2.2)
   3. migrations_v2.3.sql                     (melhorias v2.3)
   4. migrations_v2.5.sql                     (melhorias v2.5)
   5. migrations_master_data_simple.sql       (dados mestres)
   6. migrations_auto_allocation.sql          (sistema de alocação)
   7. migrations_triggers.sql                 (triggers e validações)
   8. performance_indexes.sql                 (índices de performance)
   ```

   **Localização dos arquivos**: `/home/cycodene/admissao.cycode.net/app/Database/`

3. **Importar cada arquivo**:
   - Clicar em "Choose File"
   - Selecionar arquivo SQL
   - Clicar em "Go"
   - Aguardar mensagem de sucesso
   - Repetir para próximo arquivo

### 4.4. Criar Usuário Administrador

**Via phpMyAdmin > SQL**:

```sql
-- Criar usuário coordenador principal
INSERT INTO users (
    name, 
    email, 
    phone, 
    role, 
    password_hash, 
    email_verified_at, 
    available_for_vigilance, 
    supervisor_eligible, 
    created_at, 
    updated_at
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
```

**Credenciais Temporárias**:
- Email: `coordenador@cycode.net`
- Senha: `password` (⚠️ **ALTERAR IMEDIATAMENTE APÓS PRIMEIRO LOGIN**)

### 4.5. Verificar Tabelas Criadas

**Via phpMyAdmin > Structure**:

Verificar que as seguintes tabelas existem:
- [ ] `users`
- [ ] `vacancies`
- [ ] `vacancy_applications`
- [ ] `juries`
- [ ] `jury_vigilantes`
- [ ] `locations`
- [ ] `subjects`
- [ ] `rooms`
- [ ] E outras...

Total esperado: **15-20 tabelas**

---

## ⚙️ FASE 5: Configurar Aplicação (45 min)

### 5.1. Criar Arquivo .env no Servidor

**Via SSH ou Terminal cPanel**:

```bash
cd ~/admissao.cycode.net

# Copiar arquivo de exemplo
cp .env.example .env

# Editar com nano ou vi
nano .env
```

**Ou via cPanel > File Manager**:
1. Navegar para `/home/cycodene/admissao.cycode.net/`
2. Criar novo arquivo `.env`
3. Copiar conteúdo do `.env.production` preparado anteriormente
4. Salvar

### 5.2. Conteúdo do .env

```env
APP_NAME="Portal da Comissão de Exames de Admissão"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admissao.cycode.net
APP_TIMEZONE=Africa/Maputo

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cycodene_comexamesul
DB_USERNAME=cycodene_cycodene
DB_PASSWORD=&~Oi)0SXsPNh7$bF

MAIL_FROM_NAME="Portal da Comissão de Exames"
MAIL_FROM_ADDRESS="noreply@cycode.net"
MAIL_SMTP_HOST=smtp.gmail.com
MAIL_SMTP_PORT=587
MAIL_SMTP_USER=seu_email@gmail.com
MAIL_SMTP_PASS=sua_senha_app_gmail
MAIL_SMTP_ENCRYPTION=tls

SESSION_NAME=exam_portal_prod
SESSION_LIFETIME=7200
SESSION_SECURE=true

RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_WINDOW=900

CSRF_TOKEN_KEY=csrf_token
```

### 5.3. Proteger Arquivo .env

```bash
# Via SSH
chmod 600 .env
```

**Ou via File Manager**:
- Botão direito em `.env` > Permissions
- Definir como: `600` (Owner: Read+Write)

### 5.4. Instalar Dependências Composer

**Via SSH (Recomendado)**:

```bash
cd ~/admissao.cycode.net

# Verificar se Composer está instalado
which composer

# Se composer estiver instalado globalmente:
composer install --no-dev --optimize-autoloader

# Se não tiver composer, verificar se existe composer.phar:
ls composer.phar

# Se existir composer.phar local:
php composer.phar install --no-dev --optimize-autoloader

# Se não existir, baixar Composer:
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

**Alternativa - Upload Manual da Pasta vendor/**:

Se não conseguir instalar via SSH:

1. **No computador local**:
   ```bash
   cd c:\xampp\htdocs\comexamesul
   composer install --no-dev --optimize-autoloader
   ```

2. **Comprimir pasta vendor/**:
   - Clicar com botão direito na pasta `vendor/`
   - Comprimir para `vendor.zip`

3. **Upload via cPanel > File Manager**:
   - Upload `vendor.zip` para `/home/cycodene/admissao.cycode.net/`
   - Extrair arquivo
   - Verificar que pasta `vendor/` foi criada corretamente

### 5.5. Configurar Permissões

```bash
cd ~/admissao.cycode.net

# Criar diretórios necessários se não existirem
mkdir -p storage/cache storage/logs public/uploads/avatars

# Definir permissões de escrita
chmod -R 775 storage/
chmod -R 775 public/uploads/

# Criar arquivos .gitkeep
touch storage/cache/.gitkeep
touch storage/logs/.gitkeep
touch public/uploads/.gitkeep
touch public/uploads/avatars/.gitkeep
```

**Ou via File Manager**:
- Botão direito nas pastas > Permissions
- Marcar: Read, Write, Execute para Owner e Group
- Valor: `775`

---

## 🌍 FASE 6: Configurar Domínio e SSL (30 min)

### 6.1. Adicionar Subdomínio

**cPanel > Domains > Subdomains**:

1. **Criar Subdomínio**:
   - Subdomain: `admissao`
   - Domain: `cycode.net`
   - Document Root: `/home/cycodene/admissao.cycode.net/public`
   - ✅ Criar

2. **Aguardar Propagação DNS**: 5-10 minutos

### 6.2. Verificar DocumentRoot

**IMPORTANTE**: O DocumentRoot deve apontar para a pasta `public/`:
```
/home/cycodene/admissao.cycode.net/public
```

**Verificar via cPanel > Domains**:
- Clicar no ícone de engrenagem ao lado de `admissao.cycode.net`
- Confirmar Document Root

### 6.3. Ativar SSL/HTTPS

**cPanel > SSL/TLS Status**:

1. Localizar `admissao.cycode.net`
2. Clicar em "Run AutoSSL"
3. Aguardar emissão do certificado Let's Encrypt (5-10 minutos)
4. Status deve mudar para ✅ "AutoSSL certificate installed"

**Alternativa via cPanel > SSL/TLS**:
1. Manage SSL Sites
2. Selecionar `admissao.cycode.net`
3. Install SSL Certificate

### 6.4. Forçar HTTPS

Verificar se o arquivo `/home/cycodene/admissao.cycode.net/public/.htaccess` contém:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Forçar HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Auto-detectar base path
    RewriteBase /
    
    # Permitir acesso direto a arquivos existentes
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Redirecionar tudo para index.php
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

**Se não existir, adicionar ao início do arquivo.**

---

## ⏰ FASE 7: Configurar Cron Jobs (15 min)

### 7.1. Adicionar Cron Job

**cPanel > Cron Jobs**:

1. **Configuração do Cron**:
   - Minuto: `*/30` (a cada 30 minutos)
   - Hora: `*`
   - Dia: `*`
   - Mês: `*`
   - Dia da Semana: `*`

2. **Comando**:
   ```bash
   /usr/bin/php /home/cycodene/admissao.cycode.net/app/Cron/check_deadlines.php >> /home/cycodene/logs/cron.log 2>&1
   ```

3. Clicar em **Add New Cron Job**

### 7.2. Verificar Caminho do PHP

Para garantir o caminho correto do PHP:

```bash
# Via SSH
which php
# Resultado comum: /usr/bin/php ou /usr/local/bin/php
```

Se o caminho for diferente, ajustar no comando do cron.

### 7.3. Criar Diretório de Logs

```bash
# Via SSH
mkdir -p ~/logs
touch ~/logs/cron.log
chmod 755 ~/logs
```

---

## 🧪 FASE 8: Testes e Validação (1 hora)

### 8.1. Teste de Acesso Básico

1. **Acessar via HTTP**:
   - URL: `http://admissao.cycode.net`
   - Deve redirecionar para HTTPS

2. **Acessar via HTTPS**:
   - URL: `https://admissao.cycode.net`
   - Deve exibir página inicial
   - Certificado SSL válido (cadeado verde)

### 8.2. Teste de Autenticação

1. **Página de Login**:
   - Acessar: `https://admissao.cycode.net/login`
   - Inserir credenciais:
     - Email: `coordenador@cycode.net`
     - Senha: `password`
   - Clicar em **Entrar**

2. **Dashboard**:
   - Deve carregar o dashboard do coordenador
   - Verificar menu de navegação
   - Verificar que não há erros visíveis

3. **Alterar Senha**:
   - Ir para Perfil/Configurações
   - Alterar senha temporária
   - Fazer logout e login com nova senha

### 8.3. Testes Funcionais

**Criar Vaga**:
- [ ] Menu > Vagas > Nova Vaga
- [ ] Preencher formulário
- [ ] Salvar com sucesso

**Criar Júri**:
- [ ] Menu > Júris > Novo Júri
- [ ] Preencher dados
- [ ] Salvar com sucesso

**Sistema de Alocação**:
- [ ] Menu > Júris > Planejamento
- [ ] Verificar drag-and-drop funciona
- [ ] Testar auto-alocação

**Upload de Avatar**:
- [ ] Perfil > Alterar Avatar
- [ ] Upload de imagem
- [ ] Verificar que foi salvo em `/public/uploads/avatars/`

### 8.4. Verificar Logs

**Via SSH**:
```bash
# Logs da aplicação
tail -f ~/admissao.cycode.net/storage/logs/app.log

# Logs de erro PHP
tail -f ~/logs/php_errors.log

# Logs do Cron
tail -f ~/logs/cron.log
```

**Via File Manager**:
- Navegar para `storage/logs/`
- Abrir arquivos de log
- Verificar se há erros

### 8.5. Teste de Performance

```bash
# Via SSH - teste de tempo de resposta
curl -w "Time: %{time_total}s\n" -o /dev/null -s https://admissao.cycode.net
```

**Benchmarks esperados**:
- Homepage: < 2s
- Dashboard: < 3s
- Listagens: < 2s

---

## 🔒 FASE 9: Segurança Pós-Deploy (30 min)

### 9.1. Alterar Credenciais Padrão

✅ **JÁ FEITO** no teste 8.2.3

### 9.2. Verificar Proteção de Arquivos

**Testar acesso a arquivos sensíveis** (devem retornar 403 Forbidden):
- `https://admissao.cycode.net/.env` → ❌ Bloqueado
- `https://admissao.cycode.net/composer.json` → ❌ Bloqueado
- `https://admissao.cycode.net/config/database.php` → ❌ Bloqueado

### 9.3. Limpar Dados de Teste

**Via phpMyAdmin**:

```sql
-- Remover usuários de exemplo (CUIDADO - verificar antes)
DELETE FROM users WHERE email LIKE '%@example.com';
DELETE FROM users WHERE email LIKE '%unilicungo.ac.mz';

-- Manter apenas o coordenador criado
SELECT * FROM users; -- Verificar lista
```

### 9.4. Configurar Backup Automático

**cPanel > Backup > Backup Wizard**:
1. Backup > Full Backup
2. Destino: Email ou FTP remoto
3. Frequência: Diária
4. Ativar notificações

### 9.5. Configurar Monitoramento

**Recomendações**:
- **UptimeRobot**: https://uptimerobot.com
  - Monitorar: `https://admissao.cycode.net`
  - Intervalo: 5 minutos
  - Alerta: Email

---

## 📊 FASE 10: Documentação Final (15 min)

### 10.1. Informações do Sistema

**Registrar em local seguro**:

```
=== PRODUÇÃO - admissao.cycode.net ===

SERVIDOR:
- Hospedagem: CyCode
- IP: 57.128.126.160
- cPanel: https://cycode.net:2083
- Usuário: cycodene

APLICAÇÃO:
- URL: https://admissao.cycode.net
- Versão: 2.5+
- PHP: 8.1+
- MySQL: 8.0+

BANCO DE DADOS:
- Host: localhost
- Database: cycodene_comexamesul
- User: cycodene_cycodene
- Password: &~Oi)0SXsPNh7$bF

CREDENCIAIS ADMIN:
- Email: coordenador@cycode.net
- Senha: [SENHA ALTERADA - VER GERENCIADOR DE SENHAS]

CRON JOB:
- Comando: /usr/bin/php /home/cycodene/admissao.cycode.net/app/Cron/check_deadlines.php
- Frequência: */30 * * * * (a cada 30 minutos)

BACKUP:
- Tipo: Automático via cPanel
- Frequência: Diária
- Destino: [EMAIL/FTP]
```

### 10.2. Comandos Úteis

```bash
# Conectar via SSH
ssh cycodene@57.128.126.160

# Navegar para projeto
cd ~/admissao.cycode.net

# Ver logs em tempo real
tail -f storage/logs/app.log
tail -f ~/logs/php_errors.log

# Limpar cache
rm -rf storage/cache/*

# Backup manual
tar -czf ~/backup-$(date +%Y%m%d).tar.gz admissao.cycode.net/
mysqldump -u cycodene_cycodene -p cycodene_comexamesul > ~/db-backup-$(date +%Y%m%d).sql

# Verificar permissões
ls -la storage/
ls -la public/uploads/

# Verificar cron jobs ativos
crontab -l
```

---

## ✅ Checklist Final de Deploy

### Antes de Anunciar Produção

- [ ] SSL/HTTPS funcionando com certificado válido
- [ ] Banco de dados importado e populado
- [ ] Arquivo .env configurado corretamente
- [ ] Dependências Composer instaladas
- [ ] Permissões corretas (storage, uploads)
- [ ] Cron job ativo e funcionando
- [ ] Usuário administrador criado e testado
- [ ] Senha padrão alterada
- [ ] Todos os testes funcionais passando
- [ ] Logs verificados (sem erros críticos)
- [ ] Backup inicial criado
- [ ] Monitoramento configurado
- [ ] Documentação completa

### Performance

- [ ] Homepage carrega em < 2s
- [ ] Dashboard carrega em < 3s
- [ ] Sem erros 500
- [ ] Sem warnings PHP visíveis

### Segurança

- [ ] Arquivos sensíveis bloqueados (.env, .sql, etc.)
- [ ] HTTPS forçado (redirecionamento HTTP → HTTPS)
- [ ] Senhas fortes em uso
- [ ] Rate limiting ativo

---

## 🐛 Troubleshooting

### Erro: "500 Internal Server Error"

**Causas comuns**:
1. Permissões incorretas
2. Arquivo .env mal configurado
3. Erro no .htaccess

**Solução**:
```bash
# Verificar logs
tail -50 ~/logs/php_errors.log

# Corrigir permissões
chmod 775 storage/ -R
chmod 775 public/uploads/ -R

# Verificar .env
cat .env
```

### Erro: "Database connection failed"

**Solução**:
```bash
# Verificar credenciais
cat .env | grep DB_

# Testar conexão MySQL
mysql -u cycodene_cycodene -p cycodene_comexamesul
# Inserir senha: &~Oi)0SXsPNh7$bF
```

### Erro: "Composer dependencies missing"

**Solução**:
```bash
cd ~/admissao.cycode.net
composer install --no-dev --optimize-autoloader
```

### CSS/JS não carregam

**Verificar**:
1. DocumentRoot aponta para `/public/`
2. Arquivos existem em `/public/assets/`
3. Verificar console do navegador (F12)

### Emails não enviam

**Verificar**:
1. Configurações SMTP no `.env`
2. Usar Gmail App Password: https://myaccount.google.com/apppasswords
3. Testar portas 587 ou 465

---

## 🎉 SISTEMA EM PRODUÇÃO!

Após completar todos os passos:

**🌐 URL**: https://admissao.cycode.net  
**👤 Login Admin**: coordenador@cycode.net  
**🔒 Senha**: [sua senha segura]

---

## 📞 Suporte

### Contatos Importantes

- **Hospedagem**: suporte@cycode.net
- **Desenvolvedor**: [seu contato]
- **Documentação**: https://admissao.cycode.net/docs/

### Manutenção Regular

**Semanal**:
- Verificar logs de erro
- Monitorar uso de disco
- Verificar backups

**Mensal**:
- Atualizar dependências Composer
- Revisar usuários inativos
- Analisar performance

**Trimestral**:
- Revisar configurações de segurança
- Atualizar documentação
- Planejar melhorias

---

**📅 Criado em**: 20 de Outubro de 2025  
**🏷️ Versão do Plano**: 1.0  
**✅ Status**: Pronto para Implementação

**Boa sorte com o deploy! 🚀**
