# 🔧 Troubleshooting - Erro 503 Service Unavailable

Este guia resolve o erro **503 Service Unavailable** no servidor de produção.

---

## ⚡ Solução Rápida (Tente Primeiro)

### 1️⃣ **Usar .htaccess Mínimo** (2 min)

```bash
# Via cPanel File Manager:
1. Navegar para: /home/cycodene/admissao.cycode.net/public/
2. Renomear .htaccess para .htaccess.old
3. Copiar conteúdo de .htaccess.minimal
4. Criar novo arquivo .htaccess
5. Colar o conteúdo
6. Testar site
```

### 2️⃣ **Executar Diagnóstico** (1 min)

```bash
# Acessar no navegador:
https://admissao.cycode.net/check.php

# Verificar se há ✗ (erros)
# Seguir as instruções mostradas
```

### 3️⃣ **Verificar Permissões** (2 min)

```bash
# Via Terminal/SSH:
cd ~/admissao.cycode.net
chmod -R 755 .
chmod -R 775 storage/
chmod -R 775 public/uploads/
chmod 600 .env
```

---

## 🔍 Causas Comuns do Erro 503

### **Causa 1: Falta pasta vendor/ (MAIS COMUM)**

**Sintoma:**
- Site mostra 503 imediatamente
- check.php mostra: `✗ vendor/autoload.php`

**Solução:**
```bash
# Opção A: Via SSH (recomendado)
ssh cycodene@57.128.126.160
cd ~/admissao.cycode.net
php -v  # verificar versão PHP
composer install --no-dev --optimize-autoloader

# Opção B: Upload manual
# 1. Local: executar composer install
# 2. Comprimir pasta vendor/
# 3. Upload via FTP para /home/cycodene/admissao.cycode.net/
# 4. Extrair no servidor
```

---

### **Causa 2: Versão PHP Incompatível**

**Sintoma:**
- check.php mostra PHP < 8.1
- Erro: "Composer requires PHP >= 8.2.0"

**Solução:**
```bash
# Via cPanel:
1. MultiPHP Manager ou Select PHP Version
2. Selecionar: admissao.cycode.net
3. Mudar para: PHP 8.1 ou 8.2
4. Save
5. Aguardar 1-2 minutos
```

---

### **Causa 3: .htaccess com Regras Incompatíveis**

**Sintoma:**
- Funcionava e parou após upload
- Logs do Apache mostram erro 500/503

**Solução:**
```bash
# 1. Usar .htaccess minimal
cp public/.htaccess.minimal public/.htaccess

# 2. Se funcionar, adicionar regras gradualmente
# 3. Testar após cada adição
```

**Regras problemáticas comuns:**
- `php_flag` / `php_value` (não funciona em todos servidores)
- `mod_headers` desabilitado
- `mod_deflate` desabilitado
- Regex muito complexos

---

### **Causa 4: Document Root Incorreto**

**Sintoma:**
- Site mostra erro 503
- Outras URLs funcionam

**Solução:**
```bash
# Via cPanel → Domains → Manage:
1. Verificar Document Root
2. Deve ser: /home/cycodene/admissao.cycode.net/public
3. NÃO deve ser: /home/cycodene/admissao.cycode.net
4. Salvar e aguardar 2 minutos
```

---

### **Causa 5: Arquivo .env Ausente**

**Sintoma:**
- check.php mostra: `✗ .env`

**Solução:**
```bash
# Via File Manager:
1. Navegar: /home/cycodene/admissao.cycode.net/
2. New File: .env
3. Edit e colar conteúdo do .env.production
4. Ajustar senhas:
   - DB_PASSWORD
   - MAIL_SMTP_PASS
5. Save
6. chmod 600 .env
```

---

### **Causa 6: Permissões Incorretas**

**Sintoma:**
- check.php mostra: `✗ storage/ [SEM PERMISSÃO]`
- Logs: "Permission denied"

**Solução:**
```bash
# Via SSH:
cd ~/admissao.cycode.net
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 storage/
chmod -R 775 public/uploads/
chmod 600 .env
```

---

### **Causa 7: Erro Fatal no Código PHP**

**Sintoma:**
- check.php funciona
- Site mostra 503
- Logs mostram erro PHP

**Solução:**
```bash
# 1. Ver logs de erro
# Via cPanel → Metrics → Errors

# 2. Ou via SSH:
tail -50 ~/logs/php_errors.log

# 3. Ativar debug temporariamente
# .env:
APP_DEBUG=true
APP_ENV=development

# 4. Recarregar site e ver erro
# 5. Corrigir erro
# 6. DESATIVAR debug:
APP_DEBUG=false
APP_ENV=production
```

---

### **Causa 8: Limites de Recursos do Servidor**

**Sintoma:**
- Site intermitente (às vezes funciona)
- Erro 503 em horários específicos
- check.php às vezes funciona

**Solução:**
```bash
# 1. Otimizar .htaccess (remover compressão pesada)

# 2. Aumentar limites via .htaccess:
# Adicionar no início do .htaccess:
php_value memory_limit 256M
php_value max_execution_time 300

# 3. Se não funcionar, adicionar em .user.ini:
# Criar arquivo .user.ini na raiz:
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 20M

# 4. Ou contatar suporte do servidor
```

---

## 📋 Checklist de Troubleshooting

Execute na ordem:

```
[ ] 1. Acessar https://admissao.cycode.net/check.php
[ ] 2. Verificar se há ✗ (erros) no diagnóstico
[ ] 3. Confirmar que vendor/ existe
[ ] 4. Verificar versão PHP >= 8.1
[ ] 5. Testar .htaccess minimal
[ ] 6. Verificar Document Root = /public
[ ] 7. Confirmar que .env existe
[ ] 8. Verificar permissões storage/
[ ] 9. Ver logs de erro do PHP
[ ] 10. Contatar suporte se nada funcionar
```

---

## 🔧 Comandos Úteis

### Ver Logs em Tempo Real
```bash
# Via SSH:
tail -f ~/logs/php_errors.log
tail -f ~/logs/error_log
```

### Testar PHP CLI
```bash
# Via SSH:
php -v
php ~/admissao.cycode.net/public/check.php
```

### Verificar Processo PHP-FPM
```bash
# Ver processos:
ps aux | grep php
```

### Criar Estrutura de Pastas
```bash
# Se storage/ não existe:
cd ~/admissao.cycode.net
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p storage/sessions
mkdir -p public/uploads/avatars
mkdir -p public/uploads/documents
chmod -R 775 storage/
chmod -R 775 public/uploads/
```

---

## 🆘 Último Recurso

### Reinstalar do Zero

```bash
# 1. Backup banco de dados
mysqldump -u cycodene_dbuser -p cycodene_comexames > backup.sql

# 2. Backup arquivos
tar -czf backup-files.tar.gz admissao.cycode.net/

# 3. Remover tudo
rm -rf ~/admissao.cycode.net/*

# 4. Fazer upload novamente
# 5. Seguir DEPLOY_RAPIDO.md do início
```

---

## 📞 Contatar Suporte

**Se nada funcionar:**

1. **Copiar diagnóstico:**
   ```bash
   https://admissao.cycode.net/check.php
   # Salvar resultado completo
   ```

2. **Copiar logs de erro:**
   ```bash
   # Via cPanel → Metrics → Errors
   # Últimas 50 linhas
   ```

3. **Enviar para suporte do servidor:**
   - Informar erro 503
   - Anexar check.php
   - Anexar logs
   - Perguntar sobre PHP-FPM

**Informações para suporte:**
```
Domínio: admissao.cycode.net
Usuário: cycodene
Erro: 503 Service Unavailable
PHP: 8.1+ (verificar versão)
Framework: PHP Puro + Composer
Document Root: /home/cycodene/admissao.cycode.net/public
```

---

## ✅ Verificação Final

```bash
# Se resolveu, testar:
1. https://admissao.cycode.net          → Deve carregar login
2. https://admissao.cycode.net/login    → Deve mostrar formulário
3. https://admissao.cycode.net/assets/  → CSS deve carregar

# Apagar arquivos de teste:
rm public/check.php
rm public/.htaccess.old
rm public/.htaccess.minimal
```

---

## 🎯 Solução Mais Provável

**90% dos casos:**
1. ✗ vendor/ não existe → `composer install`
2. ✗ PHP < 8.1 → Alterar no cPanel
3. ✗ .htaccess problemático → Usar .htaccess.minimal

**Boa sorte! 🚀**
