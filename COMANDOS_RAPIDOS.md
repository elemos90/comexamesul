# ⚡ Comandos Rápidos - Sistema Drag-and-Drop

## 🚀 Instalação em 3 Passos

### 1️⃣ Executar Migration
```bash
cd c:/xampp/htdocs/comexamesul
php scripts/run_allocation_migration.php
```

### 2️⃣ Verificar Instalação
```bash
php scripts/verify_allocation_system.php
```

### 3️⃣ Acessar Sistema
```
http://localhost/juries/planning
```

---

## 🔍 Comandos de Verificação

### Ver Views Criadas
```sql
SHOW FULL TABLES WHERE Table_type = 'VIEW';
```

### Ver Triggers Ativos
```sql
SHOW TRIGGERS LIKE 'jury%';
```

### Ver Estatísticas de Alocação
```sql
SELECT * FROM vw_allocation_stats;
```

### Ver Carga de Vigilantes
```sql
SELECT * FROM vw_vigilante_workload 
ORDER BY workload_score DESC;
```

### Ver Júris com Slots
```sql
SELECT * FROM vw_jury_slots 
WHERE occupancy_status = 'incomplete';
```

---

## 🧪 Comandos de Teste

### Testar API: Validar Alocação
```bash
curl -X POST http://localhost/api/allocation/can-assign \
  -H "Content-Type: application/json" \
  -d '{"vigilante_id": 1, "jury_id": 1, "type": "vigilante"}'
```

### Testar API: Métricas
```bash
curl http://localhost/api/allocation/metrics
```

### Testar API: Slots de um Júri
```bash
curl http://localhost/api/allocation/jury-slots/1
```

---

## 🔧 Comandos de Manutenção

### Resetar Todas as Alocações (CUIDADO!)
```sql
DELETE FROM jury_vigilantes;
UPDATE juries SET supervisor_id = NULL;
```

### Atualizar Capacidade de Todos os Júris
```sql
UPDATE juries SET vigilantes_capacity = 2;
```

### Recriar Views (se corrompidas)
```bash
php scripts/run_allocation_migration.php
```

### Ver Logs de Atividade
```sql
SELECT * FROM activity_log 
WHERE entity = 'jury_vigilantes' 
ORDER BY created_at DESC 
LIMIT 20;
```

---

## 📊 Queries Úteis

### Conflitos Detectados
```sql
SELECT DISTINCT u.name, u.email, j1.subject, j1.exam_date
FROM jury_vigilantes jv1
INNER JOIN juries j1 ON j1.id = jv1.jury_id
INNER JOIN users u ON u.id = jv1.vigilante_id
INNER JOIN jury_vigilantes jv2 ON jv2.vigilante_id = jv1.vigilante_id AND jv2.id != jv1.id
INNER JOIN juries j2 ON j2.id = jv2.jury_id
WHERE j1.exam_date = j2.exam_date
  AND (j1.start_time < j2.end_time AND j2.start_time < j1.end_time);
```

### Top 10 Vigilantes com Mais Carga
```sql
SELECT name, email, workload_score, vigilance_count, supervision_count
FROM vw_vigilante_workload
ORDER BY workload_score DESC
LIMIT 10;
```

### Júris Sem Vigilantes
```sql
SELECT j.id, j.subject, j.exam_date, j.location, j.room
FROM juries j
LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id
WHERE jv.id IS NULL
ORDER BY j.exam_date, j.start_time;
```

### Júris Sem Supervisor
```sql
SELECT id, subject, exam_date, location, room
FROM juries
WHERE supervisor_id IS NULL
  AND requires_supervisor = 1
ORDER BY exam_date, start_time;
```

### Estatísticas por Data de Exame
```sql
SELECT exam_date,
  COUNT(*) as total_juries,
  COUNT(DISTINCT subject) as total_disciplines,
  SUM(candidates_quota) as total_candidates,
  COUNT(DISTINCT supervisor_id) as total_supervisors
FROM juries
GROUP BY exam_date
ORDER BY exam_date;
```

---

## 🎯 Atalhos de Desenvolvimento

### Iniciar XAMPP
```bash
# Windows
"C:\xampp\xampp-control.exe"
```

### Ver Logs de Erro PHP
```bash
tail -f c:/xampp/apache/logs/error.log
```

### Limpar Cache (se houver)
```bash
rm -rf storage/cache/*
```

### Verificar Sintaxe PHP
```bash
php -l app/Controllers/JuryController.php
php -l app/Services/AllocationService.php
```

---

## 🔐 Comandos de Segurança

### Ver Últimas Alocações (Auditoria)
```sql
SELECT a.*, u.name as user_name
FROM activity_log a
LEFT JOIN users u ON u.id = a.user_id
WHERE a.entity = 'jury_vigilantes'
ORDER BY a.created_at DESC
LIMIT 20;
```

### Ver Quem Alocou em um Júri
```sql
SELECT jv.*, u.name as assigned_by_name
FROM jury_vigilantes jv
LEFT JOIN users u ON u.id = jv.assigned_by
WHERE jv.jury_id = 1;
```

---

## 📱 Testes de Interface

### Abrir Planejamento
```
http://localhost/juries/planning
```

### Abrir Júris
```
http://localhost/juries
```

### Ver Dashboard
```
http://localhost/dashboard
```

### Ver Locais
```
http://localhost/locations
```

---

## 🐛 Debug

### Ativar Modo Debug (se disponível)
Edite `.env`:
```
APP_DEBUG=true
```

### Ver Todas as Rotas
```bash
php -r "require 'bootstrap.php'; print_r(\$router->routes ?? 'No router');"
```

### Testar Conexão com Banco
```bash
php -r "require 'bootstrap.php'; echo 'DB: ' . (database() ? 'OK' : 'FAIL');"
```

---

## 💡 Dicas

### Backup Antes de Testar
```bash
# Windows (MySQL)
"C:\xampp\mysql\bin\mysqldump.exe" -u root comexamesul > backup_antes_dnd.sql
```

### Restaurar Backup
```bash
# Windows
"C:\xampp\mysql\bin\mysql.exe" -u root comexamesul < backup_antes_dnd.sql
```

### Ver Performance de Queries
```sql
EXPLAIN SELECT * FROM vw_vigilante_workload WHERE user_id = 1;
```

---

## 📞 Resolução Rápida de Problemas

### Problema: "Table doesn't exist"
```bash
php scripts/run_allocation_migration.php
```

### Problema: "Trigger doesn't exist"
```bash
php scripts/run_allocation_migration.php
```

### Problema: "Access denied"
Verifique `.env`:
```
DB_USERNAME=root
DB_PASSWORD=
```

### Problema: "Drag não funciona"
1. Limpe cache: Ctrl+F5
2. Verifique console: F12
3. Verifique URL: `/juries/planning`

---

**Guia atualizado**: 09/10/2025  
**Para mais informações**: Ver `SISTEMA_ALOCACAO_DND.md`
