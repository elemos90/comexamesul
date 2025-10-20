# 🖥️ Comandos Úteis - Produção admissao.cycode.net

Comandos frequentes para administração do sistema em produção.

---

## 🔐 Acesso SSH

```bash
# Conectar via SSH
ssh cycodene@57.128.126.160

# Ou via SSH com chave
ssh -i ~/.ssh/id_rsa cycodene@57.128.126.160

# Navegar para pasta do projeto
cd ~/admissao.cycode.net
```

---

## 📂 Gerenciamento de Arquivos

### Navegar e Listar

```bash
# Ir para pasta do projeto
cd ~/admissao.cycode.net

# Listar arquivos
ls -la

# Verificar espaço em disco
du -sh *
df -h

# Encontrar arquivos grandes
find . -type f -size +10M -exec ls -lh {} \;
```

### Permissões

```bash
# Corrigir permissões de storage
chmod -R 775 storage/
chmod -R 775 public/uploads/

# Verificar dono dos arquivos
ls -la storage/

# Mudar proprietário (se necessário)
chown -R cycodene:cycodene storage/
```

---

## 🗄️ Banco de Dados

### MySQL via Linha de Comando

```bash
# Conectar ao MySQL
mysql -u cycodene_dbuser -p cycodene_comexames

# Dentro do MySQL:
# Ver tabelas
SHOW TABLES;

# Ver estrutura
DESCRIBE users;

# Contar registros
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM juries;

# Ver últimos usuários
SELECT id, name, email, role FROM users ORDER BY created_at DESC LIMIT 10;

# Sair
EXIT;
```

### Backup do Banco

```bash
# Backup completo
mysqldump -u cycodene_dbuser -p cycodene_comexames > backup_$(date +%Y%m%d).sql

# Backup comprimido
mysqldump -u cycodene_dbuser -p cycodene_comexames | gzip > backup_$(date +%Y%m%d).sql.gz

# Restaurar backup
mysql -u cycodene_dbuser -p cycodene_comexames < backup_20251017.sql

# Restaurar backup comprimido
gunzip < backup_20251017.sql.gz | mysql -u cycodene_dbuser -p cycodene_comexames
```

---

## 📝 Logs

### Visualizar Logs

```bash
# Log de erros PHP
tail -f ~/logs/php_errors.log

# Últimos 50 erros
tail -50 ~/logs/php_errors.log

# Log da aplicação
tail -f ~/admissao.cycode.net/storage/logs/app.log

# Log do cron
tail -f ~/logs/cron.log

# Buscar erros específicos
grep "error" ~/logs/php_errors.log
grep "500" ~/logs/php_errors.log
```

### Limpar Logs

```bash
# Limpar logs antigos (cuidado!)
> ~/logs/php_errors.log
> ~/admissao.cycode.net/storage/logs/app.log

# Remover logs com mais de 30 dias
find ~/admissao.cycode.net/storage/logs/ -name "*.log" -mtime +30 -delete
```

---

## 🧹 Cache e Limpeza

### Limpar Cache

```bash
# Limpar cache da aplicação
rm -rf ~/admissao.cycode.net/storage/cache/*

# Verificar tamanho do cache
du -sh ~/admissao.cycode.net/storage/cache/

# Limpar sessões antigas
find ~/admissao.cycode.net/storage/sessions/ -mtime +7 -delete
```

### Limpar Uploads Temporários

```bash
# Ver tamanho de uploads
du -sh ~/admissao.cycode.net/public/uploads/

# Listar arquivos grandes
find ~/admissao.cycode.net/public/uploads/ -type f -size +5M -ls
```

---

## 📦 Composer

### Instalar/Atualizar Dependências

```bash
cd ~/admissao.cycode.net

# Instalar dependências (primeira vez)
composer install --no-dev --optimize-autoloader

# Atualizar dependências
composer update --no-dev --optimize-autoloader

# Verificar versões instaladas
composer show

# Verificar autoload
composer dump-autoload --optimize
```

### Verificar Segurança

```bash
# Auditoria de segurança
composer audit

# Verificar dependências desatualizadas
composer outdated
```

---

## 🔄 Atualização do Sistema

### Deploy de Nova Versão

```bash
# 1. Fazer backup antes
bash ~/admissao.cycode.net/scripts/backup_production.sh

# 2. Fazer upload dos novos arquivos via FTP
# (ou usar git pull se configurado)

# 3. Atualizar dependências
cd ~/admissao.cycode.net
composer install --no-dev --optimize-autoloader

# 4. Executar migrations (se houver)
mysql -u cycodene_dbuser -p cycodene_comexames < app/Database/nova_migration.sql

# 5. Limpar cache
rm -rf storage/cache/*

# 6. Verificar funcionamento
curl -I https://admissao.cycode.net
```

---

## ⏰ Cron Jobs

### Verificar Cron

```bash
# Listar cron jobs ativos
crontab -l

# Editar cron jobs
crontab -e

# Ver última execução
tail -20 ~/logs/cron.log

# Testar manualmente
/usr/bin/php ~/admissao.cycode.net/app/Cron/check_deadlines.php
```

---

## 🔒 Segurança

### Alterar Senha do Banco

```bash
# Conectar ao MySQL como root
mysql -u root -p

# Alterar senha
ALTER USER 'cycodene_dbuser'@'localhost' IDENTIFIED BY 'NOVA_SENHA_FORTE';
FLUSH PRIVILEGES;
EXIT;

# Atualizar .env
nano ~/admissao.cycode.net/.env
# Alterar DB_PASSWORD
```

### Verificar Arquivos Sensíveis

```bash
# Verificar se .env está protegido
ls -la ~/admissao.cycode.net/.env
# Deve mostrar: -rw------- (600)

# Proteger .env
chmod 600 ~/admissao.cycode.net/.env

# Verificar .htaccess
cat ~/admissao.cycode.net/public/.htaccess | grep -i "deny"
```

### Ver Tentativas de Login Suspeitas

```bash
# Ver logs de autenticação
grep "login" ~/admissao.cycode.net/storage/logs/app.log | tail -20

# Ver IPs bloqueados por rate limit
grep "rate limit" ~/logs/php_errors.log
```

---

## 📊 Monitoramento

### Performance

```bash
# Tempo de resposta do site
time curl -I https://admissao.cycode.net

# Usar curl com detalhes
curl -w "@-" -o /dev/null -s https://admissao.cycode.net << 'EOF'
    time_namelookup:  %{time_namelookup}\n
       time_connect:  %{time_connect}\n
    time_appconnect:  %{time_appconnect}\n
      time_redirect:  %{time_redirect}\n
   time_pretransfer:  %{time_pretransfer}\n
 time_starttransfer:  %{time_starttransfer}\n
                    ----------\n
         time_total:  %{time_total}\n
EOF
```

### Uso de Recursos

```bash
# Processos PHP em execução
ps aux | grep php

# Uso de memória
free -h

# Uso de CPU
top -n 1 | head -20

# Conexões MySQL
mysql -u cycodene_dbuser -p -e "SHOW PROCESSLIST;" cycodene_comexames
```

### Verificar SSL

```bash
# Status do certificado SSL
curl -vI https://admissao.cycode.net 2>&1 | grep -i "SSL"

# Data de expiração
echo | openssl s_client -servername admissao.cycode.net -connect admissao.cycode.net:443 2>/dev/null | openssl x509 -noout -dates
```

---

## 🔍 Troubleshooting

### Erro 500

```bash
# Ver últimos erros
tail -50 ~/logs/php_errors.log

# Verificar permissões
ls -la ~/admissao.cycode.net/storage/

# Verificar .env
cat ~/admissao.cycode.net/.env | grep DB_

# Testar conexão ao banco
mysql -u cycodene_dbuser -p cycodene_comexames -e "SELECT 1;"
```

### Site Lento

```bash
# Verificar tamanho do banco
mysql -u cycodene_dbuser -p -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.TABLES WHERE table_schema = 'cycodene_comexames';"

# Limpar cache
rm -rf ~/admissao.cycode.net/storage/cache/*

# Verificar queries lentas
mysql -u cycodene_dbuser -p cycodene_comexames -e "SHOW PROCESSLIST;"
```

### Emails Não Enviam

```bash
# Verificar configuração SMTP no .env
cat ~/admissao.cycode.net/.env | grep MAIL_

# Testar SMTP manualmente
telnet smtp.gmail.com 587
# CTRL+] para sair

# Ver logs de email
grep -i "mail" ~/logs/php_errors.log
```

---

## 💾 Backup e Restauração

### Backup Rápido

```bash
# Backup completo (DB + arquivos)
bash ~/admissao.cycode.net/scripts/backup_production.sh

# Apenas banco de dados
mysqldump -u cycodene_dbuser -p cycodene_comexames | gzip > ~/backups/db_$(date +%Y%m%d).sql.gz

# Apenas arquivos importantes
tar -czf ~/backups/files_$(date +%Y%m%d).tar.gz \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage/cache/*' \
  ~/admissao.cycode.net
```

### Restauração

```bash
# Restaurar banco
gunzip < ~/backups/db_20251017.sql.gz | mysql -u cycodene_dbuser -p cycodene_comexames

# Restaurar arquivos
cd ~
tar -xzf ~/backups/files_20251017.tar.gz
```

### Download de Backup

```bash
# Via SCP (do seu computador local)
scp cycodene@57.128.126.160:~/backups/full_backup_20251017.tar.gz ./

# Via FTP
# Use FileZilla ou outro cliente FTP
# Navegar para /home/cycodene/backups/
```

---

## 🛠️ Manutenção

### Otimizar Banco de Dados

```bash
mysql -u cycodene_dbuser -p cycodene_comexames -e "OPTIMIZE TABLE users, juries, jury_vigilantes, vacancy_applications;"

# Verificar integridade
mysql -u cycodene_dbuser -p cycodene_comexames -e "CHECK TABLE users, juries;"

# Reparar tabela (se necessário)
mysql -u cycodene_dbuser -p cycodene_comexames -e "REPAIR TABLE users;"
```

### Atualizar Estatísticas

```bash
mysql -u cycodene_dbuser -p cycodene_comexames -e "ANALYZE TABLE users, juries, jury_vigilantes;"
```

---

## 📱 Comandos Quick Reference

```bash
# Status do site
curl -I https://admissao.cycode.net

# Ver erros recentes
tail -20 ~/logs/php_errors.log

# Backup rápido
mysqldump -u cycodene_dbuser -p cycodene_comexames > ~/backup.sql

# Limpar cache
rm -rf ~/admissao.cycode.net/storage/cache/*

# Ver logs em tempo real
tail -f ~/logs/php_errors.log

# Reiniciar PHP (se tiver acesso)
# Via cPanel: Software > Select PHP Version > Restart

# Verificar espaço em disco
df -h
```

---

## 📞 Contatos de Emergência

**Hospedagem**:
- Suporte: suporte@cycode.net
- cPanel: https://cycode.net:2083

**Banco de Dados**:
- Usuário: cycodene_dbuser
- Banco: cycodene_comexames
- Host: localhost

**Aplicação**:
- URL: https://admissao.cycode.net
- Admin: coordenador@admissao.cycode.net

---

**Última atualização**: 17 de Outubro de 2025
