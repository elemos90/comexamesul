# 📝 Logs do Sistema

Este diretório armazena os logs da aplicação.

## 📁 Arquivos de Log

### Logs Principais

| Arquivo | Descrição | Rotação |
|---------|-----------|---------|
| `app.log` | Log principal da aplicação | Automática |
| `cron.log` | Logs de tarefas cron | Manual |
| `error.log` | Erros PHP (se configurado) | Automática |
| `query.log` | Logs de queries SQL (debug) | Manual |

## 🔧 Configuração

### Arquivo de Log Principal

Configurado automaticamente em `bootstrap.php`:

```php
$logFile = BASE_PATH . '/storage/logs/app.log';
ini_set('error_log', $logFile);
```

### Níveis de Log

- **Error:** Erros fatais
- **Warning:** Avisos
- **Notice:** Notificações
- **Debug:** Informações de debug (apenas desenvolvimento)

## 📊 Monitoramento

### Ver Logs em Tempo Real

```bash
# Linux/Mac
tail -f storage/logs/app.log

# Windows PowerShell
Get-Content storage/logs/app.log -Wait -Tail 50
```

### Pesquisar Erros

```bash
# Linux/Mac
grep "ERROR" storage/logs/app.log | tail -20

# Windows PowerShell
Select-String -Path "storage/logs/app.log" -Pattern "ERROR" | Select-Object -Last 20
```

### Limpar Logs Antigos

```bash
# Manter apenas últimos 7 dias
find storage/logs -name "*.log" -mtime +7 -delete

# Windows PowerShell
Get-ChildItem storage/logs -Filter *.log | Where-Object {$_.LastWriteTime -lt (Get-Date).AddDays(-7)} | Remove-Item
```

## 🔄 Rotação de Logs

### Configuração Recomendada (logrotate)

Criar arquivo `/etc/logrotate.d/comexamesul`:

```
/caminho/do/projeto/storage/logs/*.log {
    daily
    rotate 7
    compress
    missingok
    notifempty
    create 0644 www-data www-data
}
```

### Manualmente

```bash
# Fazer backup e limpar
mv storage/logs/app.log storage/logs/app.log.$(date +%Y%m%d)
touch storage/logs/app.log
chmod 644 storage/logs/app.log
```

## 🚨 Troubleshooting

### Permissões

Se logs não estão sendo escritos:

```bash
# Linux/Mac
chmod -R 755 storage/logs
chown -R www-data:www-data storage/logs

# Verificar
ls -la storage/logs
```

### Verificação Automática

Execute o script de verificação:

```bash
php scripts/check_permissions.php
```

## 📦 .gitignore

Este diretório está no Git, mas os arquivos `.log` são ignorados:

```gitignore
*.log
*.txt
!.gitignore
!README.md
```

## 🔒 Segurança

- ❌ Logs **não devem** ser acessíveis via web
- ✅ Diretório `storage/` fora de `public/`
- ✅ Permissões adequadas (755 para diretório, 644 para arquivos)
- ✅ Rotação automática para evitar crescimento excessivo

---

**Configurado em:** Melhoria #3 - Novembro 2025  
**Documentado em:** `LOGS_PORTAVEIS_2025.md`
