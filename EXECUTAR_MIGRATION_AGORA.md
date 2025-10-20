# ⚠️ EXECUTAR MIGRATION - DADOS MESTRES

**ERRO**: `Table 'comexamesul.exam_locations' doesn't exist`

**CAUSA**: As tabelas não foram criadas no banco de dados.

**SOLUÇÃO**: Executar migration via phpMyAdmin (2 minutos)

---

## 🚀 Passo a Passo RÁPIDO

### 1️⃣ Abrir phpMyAdmin

No navegador, acesse:
```
http://localhost/phpmyadmin
```

### 2️⃣ Selecionar Banco de Dados

Na coluna da **esquerda**, clique em:
```
comexamesul
```

### 3️⃣ Abrir Aba SQL

No topo da página, clique na aba:
```
SQL
```

### 4️⃣ Abrir Arquivo SQL

Abra este arquivo no seu editor de texto:
```
c:\xampp\htdocs\comexamesul\app\Database\migrations_master_data_simple.sql
```

**Atalho**: Use o VS Code ou Notepad++

### 5️⃣ Copiar TODO o Conteúdo

Selecione **TUDO** (Ctrl+A) e copie (Ctrl+C)

### 6️⃣ Colar no phpMyAdmin

No campo de texto grande do phpMyAdmin, cole (Ctrl+V)

### 7️⃣ Executar

Clique no botão **"Executar"** (canto inferior direito)

### 8️⃣ Verificar Sucesso

Deve aparecer:
```
✅ Tabelas criadas!
Disciplinas: 10
Locais: 4
Salas: 19
```

### 9️⃣ Recarregar Página

Volte para:
```
http://localhost/master-data/locations
```

E recarregue (F5)

---

## ✅ Verificação

Execute esta query no phpMyAdmin para confirmar:

```sql
SHOW TABLES LIKE '%exam%';
```

**Deve retornar**:
- exam_locations
- exam_rooms
- exam_vacancies
- exam_reports

---

## 🆘 Se Ainda Der Erro

### Erro: "Access denied"
**Solução**: Verifique se está usando o usuário `root` sem senha no phpMyAdmin

### Erro: "Foreign key constraint"
**Solução**: Execute primeiro as migrations principais:
```sql
-- No phpMyAdmin, execute:
SHOW TABLES;
-- Verifique se a tabela 'users' existe
```

Se não existir, execute primeiro:
```
app/Database/migrations.sql
app/Database/seed.sql
```

### Erro persiste?
Execute estas queries manualmente no phpMyAdmin:

```sql
-- Verificar se banco existe
SHOW DATABASES LIKE 'comexamesul';

-- Usar banco
USE comexamesul;

-- Verificar tabelas
SHOW TABLES;

-- Se tabela users não existir, crie primeiro:
-- Cole o conteúdo de: app/Database/migrations.sql
-- Depois: app/Database/seed.sql
-- Por fim: app/Database/migrations_master_data_simple.sql
```

---

## 📸 Screenshots Esperados

### Passo 3 - Aba SQL
Você deve ver um campo de texto grande para colar o SQL.

### Passo 8 - Resultado
```
Mostrando registros 0 - 0 (1 total, Query levou 0.0234 segundos.)

✅ Tabelas criadas!
Disciplinas: 10
Locais: 4
Salas: 19
```

---

## 🎯 Após Executar com Sucesso

1. Recarregue a página: http://localhost/master-data/locations
2. Deve mostrar 4 cards de locais
3. Acesse: http://localhost/master-data/disciplines
4. Deve mostrar 10 disciplinas

---

**Tempo total**: 2-3 minutos  
**Dificuldade**: Fácil (copiar e colar)
