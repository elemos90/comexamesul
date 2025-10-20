# ⚡ Deploy Rápido - admissao.cycode.net

Guia resumido para deploy em produção em **30 minutos**.

---

## 📋 Informações do Servidor

```
Domínio:    admissao.cycode.net
Usuário:    cycodene
IP:         57.128.126.160
cPanel:     https://cycode.net:2083
Banco:      cycodene_comexames
DB User:    cycodene_dbuser
```

---

## ⚡ 10 Passos Rápidos

### 1️⃣ Verificação Local (5 min)

```bash
# Executar verificação
php scripts/pre_deploy_check.php

# Deve mostrar: "✅ SISTEMA PRONTO PARA DEPLOY!"
```

### 2️⃣ Criar .env de Produção (2 min)

```bash
# Copiar template
cp env.production.example .env.production

# Editar e preencher:
# - DB_PASSWORD
# - MAIL_SMTP_USER
# - MAIL_SMTP_PASS
```

### 3️⃣ Comprimir Projeto (3 min)

```powershell
# Windows PowerShell
Compress-Archive -Path * -DestinationPath ..\comexamesul-deploy.zip -Force

# Excluir manualmente antes:
# - vendor/
# - storage/logs/*.log
# - storage/cache/*
# - .env
```

### 4️⃣ Upload para Servidor (5 min)

**Via cPanel File Manager:**
1. Acessar: https://cycode.net:2083
2. Login: `cycodene`
3. File Manager → `/home/cycodene/`
4. Criar pasta: `admissao.cycode.net`
5. Upload: `comexamesul-deploy.zip`
6. Extract → admissao.cycode.net/

### 5️⃣ Criar Banco de Dados (3 min)

**Via cPanel:**
1. MySQL Databases
2. Create Database: `cycodene_comexames`
3. Create User: `cycodene_dbuser`
4. Password: **(gerar senha forte)**
5. Add User to Database → ALL PRIVILEGES

### 6️⃣ Importar SQL (5 min)

**Via phpMyAdmin:**
1. Selecionar: `cycodene_comexames`
2. Import → Choose File
3. Importar **nesta ordem**:
   - `app/Database/migrations.sql`
   - `app/Database/migrations_v2.2.sql`
   - `app/Database/migrations_v2.5.sql`
   - `app/Database/migrations_master_data_simple.sql`
   - `app/Database/migrations_auto_allocation.sql`
   - `app/Database/migrations_triggers.sql`
   - `app/Database/performance_indexes.sql`
   - `install_production.sql`

### 7️⃣ Criar .env no Servidor (2 min)

**Via cPanel File Manager:**
1. Navegar: `/home/cycodene/admissao.cycode.net/`
2. New File: `.env`
3. Edit
4. Colar conteúdo do `.env.production` (com senhas)
5. Save

**Conteúdo básico:**
```env
APP_URL=https://admissao.cycode.net
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_DATABASE=cycodene_comexames
DB_USERNAME=cycodene_dbuser
DB_PASSWORD=SUA_SENHA_AQUI

MAIL_SMTP_USER=seu_email@gmail.com
MAIL_SMTP_PASS=senha_app_google

SESSION_SECURE=true
```

### 8️⃣ Configurar Domínio (3 min)

**Via cPanel:**
1. Domains → Subdomains
2. Subdomain: `admissao`
3. Domain: `cycode.net`
4. Document Root: `/home/cycodene/admissao.cycode.net/public`
5. Create

### 9️⃣ Ativar SSL (2 min)

**Via cPanel:**
1. SSL/TLS Status
2. Localizar: `admissao.cycode.net`
3. Run AutoSSL
4. Aguardar 5 minutos

### 🔟 Testar Sistema (5 min)

```bash
# 1. Acessar
https://admissao.cycode.net

# 2. Login
Email: coordenador@admissao.cycode.net
Senha: password

# 3. Alterar senha imediatamente!

# 4. Testar:
✓ Dashboard carrega
✓ Criar vaga
✓ Criar júri
✓ Upload de avatar
```

---

## ✅ Checklist Final

```
[ ] Site acessível via HTTPS
[ ] Login funcionando
[ ] Dashboard sem erros
[ ] SSL ativo (cadeado verde)
[ ] Senha admin alterada
[ ] Emails funcionando (testar recuperação de senha)
```

---

## 🔧 Comandos Essenciais

### Instalar Dependências (Via SSH)

```bash
ssh cycodene@57.128.126.160
cd ~/admissao.cycode.net

# Verificar versão do PHP
php -v  # Deve ser >= 8.1

# Se PHP < 8.1, alterar no cPanel → MultiPHP Manager

composer install --no-dev --optimize-autoloader
```

**⚠️ Se aparecer erro:** `Composer requires PHP >= 8.2.0`
```bash
# Solução 1: Alterar PHP no cPanel para 8.2
# cPanel → MultiPHP Manager → admissao.cycode.net → PHP 8.2

# Solução 2: Já foi ajustado no composer.json para usar PHP 8.1
# Basta executar composer install localmente e fazer upload do vendor/
```

### Ou Upload Manual do vendor/

```bash
# Local: executar
composer install

# Depois fazer upload da pasta vendor/ via FTP
```

### Ver Logs de Erro

```bash
# Via SSH
tail -f ~/logs/php_errors.log

# Via cPanel: File Manager
/home/cycodene/logs/php_errors.log
```

### Configurar Cron

**cPanel → Cron Jobs:**
```
Comando:
/usr/bin/php /home/cycodene/admissao.cycode.net/app/Cron/check_deadlines.php >> /home/cycodene/logs/cron.log 2>&1

Minuto: */30
Hora: *
Dia: *
Mês: *
Dia semana: *
```

---

## 🐛 Problemas Comuns

### Erro 503 Service Unavailable ⚠️
**MUITO COMUM EM DEPLOYS**

```bash
# 1. Diagnóstico rápido
https://admissao.cycode.net/check.php

# 2. Solução mais comum: instalar dependências
ssh cycodene@57.128.126.160
cd ~/admissao.cycode.net
composer install --no-dev --optimize-autoloader

# 3. Se não funcionar: usar .htaccess minimal
cp public/.htaccess.minimal public/.htaccess

# 4. Guia completo:
# Ver arquivo TROUBLESHOOTING_503.md
```

### Erro 500
```bash
# Verificar permissões
chmod -R 775 storage/
chmod -R 775 public/uploads/

# Verificar logs
tail ~/logs/php_errors.log
```

### "Database connection failed"
```bash
# Verificar .env
cat .env | grep DB_

# Testar conexão
mysql -u cycodene_dbuser -p cycodene_comexames
```

### CSS não carrega
```bash
# Verificar DocumentRoot
# Deve ser: /home/cycodene/admissao.cycode.net/public

# Copiar .htaccess de produção
cp public/.htaccess.production public/.htaccess
```

### Emails não enviam
```
1. Gmail: gerar App Password
2. https://myaccount.google.com/apppasswords
3. Usar senha de 16 caracteres no .env
```

---

## 📞 Suporte

**Arquivos de Ajuda Criados:**
- `GUIA_DEPLOY_PRODUCAO.md` - Guia completo detalhado
- `CHECKLIST_DEPLOY.md` - Checklist passo a passo
- `COMANDOS_PRODUCAO.md` - Comandos úteis
- `env.production.example` - Template .env

**Scripts Úteis:**
- `scripts/pre_deploy_check.php` - Verificação pré-deploy
- `scripts/backup_production.sh` - Backup automático

---

## 🎉 Pós-Deploy

### Backup Inicial
```bash
# Via SSH
cd ~
tar -czf backup-inicial.tar.gz admissao.cycode.net/
mysqldump -u cycodene_dbuser -p cycodene_comexames > backup-inicial.sql
```

### Monitoramento
1. Configurar UptimeRobot: https://uptimerobot.com
2. URL: `https://admissao.cycode.net`
3. Intervalo: 5 minutos

### Primeiro Acesso dos Usuários

**Coordenador:**
```
URL: https://admissao.cycode.net/login
Email: coordenador@admissao.cycode.net
Senha: password (TROCAR!)
```

**Criar Novos Usuários:**
1. Dashboard → Configurações
2. Ou permitir auto-registro em `/register`

---

## 🔒 Segurança Crítica

```bash
# 1. Alterar senha admin IMEDIATAMENTE
# 2. Verificar .env protegido
chmod 600 .env

# 3. Remover arquivos de teste
rm public/test_*.php
rm public/debug_*.php

# 4. Desativar DEBUG
# .env → APP_DEBUG=false
```

---

## ✨ Sistema Online!

**Após completar todos os passos:**

🌐 **URL**: https://admissao.cycode.net  
👤 **Admin**: coordenador@admissao.cycode.net  
🔐 **Senha**: (definida por você)

---

**Tempo estimado**: 30-45 minutos  
**Dificuldade**: ⭐⭐⭐ Média  
**Pré-requisitos**: Acesso cPanel + Credenciais MySQL

**Boa sorte com o deploy! 🚀**

---

**Criado**: 17 de Outubro de 2025  
**Para**: Produção admissao.cycode.net  
**Versão**: 2.5+
