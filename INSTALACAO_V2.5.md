# 🚀 Guia de Instalação - Melhorias v2.5

**Data**: 11/10/2025  
**Sistema**: Candidaturas de Vigilantes - Melhorias Completas

---

## 📋 Pré-requisitos

- PHP 8.1+
- MySQL 8+
- Sistema v2.4.1 já instalado e funcionando
- Acesso ao servidor (SSH ou terminal)

---

## 🔧 Instalação Passo a Passo

### **Passo 1: Backup**

Faça backup completo antes de iniciar:

```bash
# Backup do banco de dados
mysqldump -u usuario -p comexamesul > backup_antes_v2.5_$(date +%Y%m%d).sql

# Backup dos arquivos
cp -r /caminho/do/projeto /caminho/do/backup
```

### **Passo 2: Executar Migrations**

```bash
cd /caminho/do/projeto

# Instalar melhorias v2.5
php scripts/install_v2.5_improvements.php
```

**Saída Esperada:**
```
=========================================
INSTALAÇÃO - Melhorias v2.5
=========================================

🔌 Conectando ao banco de dados...
✅ Conexão estabelecida!

🔧 Executando migrations...
  ✅ Tabela criada: application_status_history
  ✅ Tabela criada: email_notifications
  ✅ Tabela alterada: vacancy_applications
  ✅ Trigger criado: trg_application_status_history
  ✅ View criada: v_application_stats

✅ INSTALAÇÃO CONCLUÍDA COM SUCESSO!
```

### **Passo 3: Verificar Instalação**

```bash
php scripts/verify_v2.5_system.php
```

**Saída Esperada:**
```
=========================================
VERIFICAÇÃO - Sistema v2.5
=========================================

📦 Verificando Models...
  ✅ ApplicationStatusHistory
  ✅ EmailNotification

🔧 Verificando Services...
  ✅ EmailNotificationService
  ✅ ApplicationStatsService

✅ SISTEMA v2.5 FUNCIONANDO CORRETAMENTE!
```

### **Passo 4: Configurar Cron Jobs**

#### **Linux/Mac:**

Edite o crontab:
```bash
crontab -e
```

Adicione as linhas:
```bash
# Enviar emails pendentes a cada 5 minutos
*/5 * * * * php /caminho/completo/app/Cron/send_emails.php >> /caminho/completo/storage/logs/emails.log 2>&1

# Verificar prazos de candidatura diariamente às 9h
0 9 * * * php /caminho/completo/scripts/check_deadlines_cron.php >> /caminho/completo/storage/logs/deadlines.log 2>&1
```

#### **Windows (Task Scheduler):**

1. Abrir **Agendador de Tarefas**
2. Criar **Nova Tarefa**:

**Tarefa 1: Enviar Emails**
- Nome: `ComExamesul - Enviar Emails`
- Disparador: Repetir a cada 5 minutos
- Ação: `php.exe`
- Argumentos: `c:\xampp\htdocs\comexamesul\app\Cron\send_emails.php`

**Tarefa 2: Verificar Prazos**
- Nome: `ComExamesul - Verificar Prazos`
- Disparador: Diariamente às 9:00
- Ação: `php.exe`
- Argumentos: `c:\xampp\htdocs\comexamesul\scripts\check_deadlines_cron.php`

### **Passo 5: Configurar Email (Opcional)**

Edite o arquivo `.env`:

```env
# Configuração de Email
MAIL_FROM=noreply@unilicungo.ac.mz
MAIL_FROM_NAME=Comissão de Exames
```

**Nota**: O sistema usa a função `mail()` do PHP por padrão. Para usar SMTP, considere configurar um serviço externo (SendGrid, Mailgun, etc).

### **Passo 6: Testar Manualmente**

Teste o envio de emails:
```bash
php app/Cron/send_emails.php
```

Teste verificação de prazos:
```bash
php scripts/check_deadlines_cron.php
```

---

## ✅ Verificação Final

Acesse as novas páginas:

1. **Dashboard de Candidaturas**: `http://seusite.com/applications/dashboard`
2. **Histórico de Candidatura**: Clique em qualquer candidatura e veja o histórico
3. **Relatório**: `http://seusite.com/applications/export`

---

## 🆕 Funcionalidades Disponíveis

### **1. Histórico de Status** ✅
- Timeline completa de mudanças
- Rastreamento de quem alterou e quando
- Motivos registrados

### **2. Motivos de Rejeição Visíveis** ✅
- Coordenadores podem escrever motivos
- Vigilantes veem feedback claro
- Melhora comunicação

### **3. Notificações por Email** ✅
- Candidatura aprovada
- Candidatura rejeitada (com motivo)
- Prazo próximo (48h antes)
- Nova candidatura (para coordenadores)
- Solicitação de cancelamento

### **4. Limite de Recandidaturas** ✅
- Máximo 3 recandidaturas por vaga
- Previne spam
- Contador visível

### **5. Dashboard de Candidaturas** ✅
- Estatísticas gerais
- Gráficos por status
- Top vigilantes ativos
- Tempo médio de revisão
- Exportação de relatórios

### **6. Pré-visualização de Vagas** ⏳
- (Implementação futura)

---

## 🧪 Testes Recomendados

### **Teste 1: Histórico de Status**
1. Crie uma candidatura
2. Aprove a candidatura
3. Acesse `/applications/{id}/history`
4. ✅ Verifique a timeline completa

### **Teste 2: Motivo de Rejeição**
1. Vá para `/applications`
2. Rejeite uma candidatura com motivo
3. Login como vigilante
4. ✅ Verifique se o motivo aparece

### **Teste 3: Notificações por Email**
1. Execute: `php app/Cron/send_emails.php`
2. ✅ Verifique os logs
3. ✅ Confirme recebimento de emails

### **Teste 4: Limite de Recandidaturas**
1. Cancele e recandidature 3 vezes
2. Na 4ª tentativa:
3. ✅ Verifique bloqueio com mensagem clara

### **Teste 5: Dashboard**
1. Acesse `/applications/dashboard`
2. ✅ Verifique estatísticas
3. ✅ Exporte relatório CSV

---

## 📊 Estrutura de Banco de Dados

### **Novas Tabelas:**

#### `application_status_history`
- Histórico de mudanças de status
- Quem alterou e quando
- Motivo da alteração

#### `email_notifications`
- Fila de emails pendentes
- Status (pending, sent, failed)
- Retry automático

### **Novas Colunas:**

#### `vacancy_applications`
- `rejection_reason` - Motivo da rejeição
- `reapply_count` - Contador de recandidaturas

### **Novas Views:**

- `v_application_stats` - Estatísticas gerais
- `v_top_vigilantes` - Top vigilantes ativos
- `v_applications_by_day` - Candidaturas por dia

---

## 🔍 Troubleshooting

### **Problema: Migrations falharam**

**Solução:**
```bash
# Verificar permissões do banco
SHOW GRANTS FOR CURRENT_USER;

# Executar manualmente
mysql -u usuario -p comexamesul < app/Database/migrations_v2.5.sql
```

### **Problema: Emails não estão sendo enviados**

**Solução 1 - Verificar função mail():**
```php
<?php
// Criar arquivo test_mail.php
$success = mail('seuemail@exemplo.com', 'Teste', 'Mensagem de teste');
echo $success ? 'Email enviado!' : 'Falha ao enviar';
```

**Solução 2 - Verificar logs:**
```bash
tail -f storage/logs/emails.log
```

**Solução 3 - Configurar SMTP externo:**
- Considere usar SendGrid, Mailgun ou similar

### **Problema: Cron jobs não executam**

**Linux/Mac:**
```bash
# Verificar se cron está rodando
systemctl status cron

# Ver logs do cron
tail -f /var/log/syslog | grep CRON
```

**Windows:**
- Verificar Agendador de Tarefas
- Ver histórico de execução
- Verificar permissões do PHP

### **Problema: Dashboard vazio**

**Solução:**
```bash
# Popular histórico retroativamente
php scripts/populate_history.php

# Verificar views
mysql -u usuario -p comexamesul -e "SELECT * FROM v_application_stats;"
```

---

## 📈 Monitoramento

### **Logs Importantes:**

```bash
# Logs de email
tail -f storage/logs/emails.log

# Logs de prazos
tail -f storage/logs/deadlines.log

# Logs gerais
tail -f storage/logs/app.log
```

### **Queries Úteis:**

```sql
-- Emails pendentes
SELECT COUNT(*) FROM email_notifications WHERE status = 'pending';

-- Emails falhados
SELECT * FROM email_notifications WHERE status = 'failed' ORDER BY created_at DESC LIMIT 10;

-- Estatísticas gerais
SELECT * FROM v_application_stats;

-- Top vigilantes
SELECT * FROM v_top_vigilantes LIMIT 10;
```

---

## 🔄 Rollback (Se Necessário)

Se algo der errado, você pode reverter:

```bash
# Restaurar banco de dados
mysql -u usuario -p comexamesul < backup_antes_v2.5_YYYYMMDD.sql

# Restaurar arquivos
rm -rf /caminho/do/projeto
cp -r /caminho/do/backup /caminho/do/projeto
```

**Arquivos para Remover (se rollback manual):**
- `app/Models/ApplicationStatusHistory.php`
- `app/Models/EmailNotification.php`
- `app/Services/EmailNotificationService.php`
- `app/Services/ApplicationStatsService.php`
- `app/Controllers/ApplicationDashboardController.php`
- `app/Views/applications/history.php`
- `app/Views/applications/dashboard.php`
- `app/Cron/send_emails.php`

---

## 📞 Suporte

Em caso de dúvidas:

1. Verificar documentação: `PROPOSTA_MELHORIAS_CANDIDATURAS.md`
2. Executar verificação: `php scripts/verify_v2.5_system.php`
3. Ver logs detalhados
4. Criar issue no repositório

---

## ✅ Checklist de Instalação

- [ ] Backup realizado
- [ ] Migrations executadas com sucesso
- [ ] Verificação passou sem erros
- [ ] Cron jobs configurados
- [ ] Emails testados e funcionando
- [ ] Dashboard acessível
- [ ] Histórico de candidaturas funcional
- [ ] Limite de recandidaturas ativo
- [ ] Motivos de rejeição visíveis
- [ ] Sistema em produção

---

**🎉 Instalação Concluída! Sistema v2.5 Pronto para Uso!**

Data de Instalação: ___/___/_____  
Instalado por: _____________________  
Versão: 2.5.0
