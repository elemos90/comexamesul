# ✅ Sistema de Encerramento Automático de Vagas

**Data**: 12/10/2025  
**Versão**: 2.5.1  
**Funcionalidade**: Fechar automaticamente vagas quando o prazo expirar

---

## 🎯 Objetivo

Garantir que as vagas sejam **fechadas automaticamente** assim que o prazo (`deadline_at`) for atingido, sem necessidade de intervenção manual.

---

## ⚙️ Como Funciona

### **1. Método `closeExpired()` no Model**

**Arquivo**: `app/Models/ExamVacancy.php`

```php
public function closeExpired(): int
{
    $sql = "UPDATE {$this->table} 
            SET status = 'fechada', updated_at = :updated 
            WHERE status = 'aberta' 
            AND deadline_at < :now";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        'updated' => now(),
        'now' => now(),
    ]);
    
    return $stmt->rowCount(); // Retorna quantas vagas foram fechadas
}
```

**Lógica**:
- Busca todas as vagas com `status = 'aberta'`
- Verifica se `deadline_at < agora`
- Atualiza status para `'fechada'`
- Retorna o número de vagas fechadas

---

### **2. Encerramento Automático (3 Métodos)**

#### **Método A: Cron Job (Recomendado)** ⭐

**Script**: `scripts/check_deadlines_cron.php`

**Executa**:
1. ✅ Fecha vagas expiradas
2. 📧 Notifica sobre prazos próximos (48h)
3. 📊 Gera relatório

**Configuração no Crontab** (executar a cada hora):
```bash
0 * * * * php /caminho/comexamesul/scripts/check_deadlines_cron.php >> /caminho/storage/logs/deadlines.log 2>&1
```

**Windows Task Scheduler**:
- **Nome**: Fechar Vagas Expiradas
- **Programa**: `C:\xampp\php\php.exe`
- **Argumentos**: `C:\xampp\htdocs\comexamesul\scripts\check_deadlines_cron.php`
- **Disparador**: A cada 1 hora
- **Log**: `C:\xampp\htdocs\comexamesul\storage\logs\deadlines.log`

---

#### **Método B: Verificação em Tempo Real**

**Implementado em**:
- ✅ `DashboardController@index` (linha 31)
- ✅ `VacancyController@index` (linha 21)
- ✅ `AvailabilityController@index` (linha 33)

Sempre que um usuário acessa essas páginas:
```php
$vacancies->closeExpired(); // Fecha vagas expiradas
$openVacancies = $vacancies->openVacancies(); // Retorna apenas abertas
```

**Vantagens**:
- ✅ Funciona sem cron job
- ✅ Atualização em tempo real
- ✅ Não depende de agendamento

**Desvantagens**:
- ⚠️ Só executa quando alguém acessa as páginas
- ⚠️ Não envia notificações

---

#### **Método C: Script Manual**

**Script**: `scripts/close_expired_vacancies.php`

**Execução manual**:
```bash
php scripts/close_expired_vacancies.php
```

**Saída**:
```
==============================================
  FECHAMENTO DE VAGAS EXPIRADAS
==============================================

📋 Verificando vagas abertas...
   Vagas abertas: 3

📊 Detalhes das vagas abertas:
   • Vigilância Matemática 2025
     Prazo: 10/10/2025 23:59
     Status: ❌ EXPIRADA

   • Vigilância Física 2025
     Prazo: 15/10/2025 18:00
     Status: ✅ Ativa

🔒 Fechando vagas expiradas...
   ✅ 1 vaga(s) fechada(s) com sucesso!

==============================================
📊 RESUMO
==============================================
Antes:   3 vaga(s) aberta(s)
Fechadas: 1 vaga(s)
Depois:  2 vaga(s) aberta(s)
==============================================

✅ Operação concluída com sucesso!
```

---

## 📊 Fluxo de Encerramento

```
┌─────────────────────────────────────┐
│  Vaga Criada                        │
│  status: 'aberta'                   │
│  deadline_at: '2025-10-15 23:59:00' │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Verificação Automática             │
│  (Cron Job ou Acesso à Página)      │
└──────────────┬──────────────────────┘
               │
               ▼
        ┌──────────────┐
        │ Prazo Expirou? │
        │ (now > deadline)│
        └──┬────────┬────┘
           │        │
      SIM  │        │ NÃO
           ▼        ▼
    ┌──────────┐  ┌──────────┐
    │ FECHAR   │  │ MANTER   │
    │ status=  │  │ ABERTA   │
    │ 'fechada'│  └──────────┘
    └──────────┘
```

---

## 🧪 Testes

### **Teste 1: Manual**

```bash
# 1. Acessar o banco de dados
mysql -u root -p comexamesul

# 2. Criar vaga de teste expirada
INSERT INTO exam_vacancies (title, description, deadline_at, status, created_by, created_at, updated_at)
VALUES ('Vaga Teste Expirada', 'Teste', '2020-01-01 00:00:00', 'aberta', 1, NOW(), NOW());

# 3. Executar script de fechamento
php scripts/close_expired_vacancies.php

# 4. Verificar se fechou
SELECT id, title, status, deadline_at FROM exam_vacancies WHERE title = 'Vaga Teste Expirada';
# Deve mostrar status = 'fechada'
```

### **Teste 2: Verificação em Tempo Real**

1. Criar vaga expirada no banco
2. Acessar `/dashboard`
3. A vaga deve ser fechada automaticamente
4. Não aparecerá em "Vagas Abertas"

### **Teste 3: Cron Job**

```bash
# Executar manualmente o cron
php scripts/check_deadlines_cron.php

# Verificar log
cat storage/logs/deadlines.log
```

---

## 📁 Arquivos Modificados/Criados

### **Criados**:
1. ✅ `scripts/close_expired_vacancies.php` - Script manual
2. ✅ `ENCERRAMENTO_AUTOMATICO_VAGAS.md` - Esta documentação

### **Modificados**:
1. ✅ `scripts/check_deadlines_cron.php`
   - Adicionado: Chamada a `closeExpired()` no início
   - Melhorado: Output formatado em 3 etapas

2. ✅ `app/Controllers/DashboardController.php`
   - Adicionado: `$vacancies->closeExpired()` (linha 31)

3. ✅ `app/Controllers/VacancyController.php`
   - Adicionado: `$model->closeExpired()` (linha 21)

4. ✅ `app/Controllers/AvailabilityController.php`
   - Adicionado: `$vacancyModel->closeExpired()` (linha 33)

---

## 🔧 Configuração Recomendada

### **Windows (Task Scheduler)**

**Tarefa 1: Fechar Vagas**
```
Nome: ComExamesul - Fechar Vagas Expiradas
Programa: C:\xampp\php\php.exe
Argumentos: C:\xampp\htdocs\comexamesul\scripts\check_deadlines_cron.php
Disparador: A cada 1 hora
Início em: 00:00
```

**Tarefa 2: Verificação Diária**
```
Nome: ComExamesul - Relatório de Vagas
Programa: C:\xampp\php\php.exe
Argumentos: C:\xampp\htdocs\comexamesul\scripts\close_expired_vacancies.php
Disparador: Diariamente às 08:00
```

### **Linux (Crontab)**

```bash
# Editar crontab
crontab -e

# Adicionar linhas:

# A cada hora: verificar e fechar vagas
0 * * * * php /var/www/comexamesul/scripts/check_deadlines_cron.php >> /var/www/comexamesul/storage/logs/deadlines.log 2>&1

# Diariamente às 8h: relatório
0 8 * * * php /var/www/comexamesul/scripts/close_expired_vacancies.php >> /var/www/comexamesul/storage/logs/vacancies.log 2>&1
```

---

## 📊 Monitoramento

### **Verificar Log do Cron**

```bash
# Ver últimas execuções
tail -n 50 storage/logs/deadlines.log

# Procurar por erros
grep "ERRO" storage/logs/deadlines.log

# Contar vagas fechadas hoje
grep "fechada(s)" storage/logs/deadlines.log | grep "$(date +%Y-%m-%d)"
```

### **Verificar no Banco**

```sql
-- Vagas fechadas nas últimas 24h
SELECT id, title, deadline_at, updated_at
FROM exam_vacancies
WHERE status = 'fechada'
AND updated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY updated_at DESC;

-- Vagas abertas expiradas (não deveria ter!)
SELECT id, title, deadline_at
FROM exam_vacancies
WHERE status = 'aberta'
AND deadline_at < NOW();
```

---

## ⚠️ Troubleshooting

### **Problema: Vagas não fecham automaticamente**

**Possíveis Causas**:
1. ❌ Cron job não está configurado
2. ❌ Script tem erro de sintaxe
3. ❌ Permissões de arquivo incorretas

**Solução**:
```bash
# Testar script manualmente
php scripts/close_expired_vacancies.php

# Verificar permissões
chmod +x scripts/*.php

# Ver log de erros PHP
tail -f /var/log/php_errors.log
```

### **Problema: Cron não executa**

**Verificar**:
```bash
# Linux: ver se cron está rodando
systemctl status cron

# Ver tarefas agendadas
crontab -l

# Executar manualmente
/usr/bin/php /caminho/scripts/check_deadlines_cron.php
```

**Windows**:
- Abrir "Agendador de Tarefas"
- Procurar "ComExamesul"
- Ver "Histórico" para verificar execuções
- Clicar com botão direito → "Executar" para teste manual

---

## 📈 Melhorias Futuras

1. **Dashboard de Admin**: Mostrar vagas fechadas recentemente
2. **Notificação**: Email para coordenador quando vaga for fechada
3. **Reabertura**: Permitir coordenador reabrir vaga fechada
4. **Estatísticas**: Gráfico de vagas fechadas por mês
5. **Auto-extensão**: Estender prazo automaticamente se nenhuma candidatura

---

## ✅ Conclusão

O sistema agora **fecha vagas automaticamente** de 3 formas:
1. ⭐ **Cron Job** (a cada hora) - Recomendado
2. 🔄 **Tempo Real** (ao acessar páginas) - Backup
3. 🖱️ **Manual** (script direto) - Emergência

**Resultado**: ✅ Vagas NUNCA ficam abertas após o prazo!

---

**🎉 Sistema de encerramento automático implementado e funcionando!**
