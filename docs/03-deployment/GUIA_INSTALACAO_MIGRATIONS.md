# 🛠️ Guia de Instalação - Migrations

## ✅ Status Atual

- **Colunas e Índices**: ✅ Instalados
- **View vw_docente_score**: ✅ Criada
- **Triggers**: ⏳ Pendente (executar manualmente)

---

## 📋 Opção 1: Via phpMyAdmin (Recomendado)

### Passo 1: Acessar phpMyAdmin
```
http://localhost/phpmyadmin
```

### Passo 2: Selecionar Banco
- Clicar em **`comexamesul`** no menu lateral

### Passo 3: Abrir SQL
- Clicar na aba **SQL** no topo

### Passo 4: Executar Triggers

Cole o seguinte código SQL e clique em **Executar**:

```sql
DELIMITER $$

-- 1. Materializar janela temporal no INSERT
DROP TRIGGER IF EXISTS trg_jv_set_interval_bi$$

CREATE TRIGGER trg_jv_set_interval_bi
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  SELECT inicio, fim INTO NEW.juri_inicio, NEW.juri_fim
  FROM juries
  WHERE id = NEW.jury_id;
END$$

-- 2. Materializar janela temporal no UPDATE
DROP TRIGGER IF EXISTS trg_jv_set_interval_bu$$

CREATE TRIGGER trg_jv_set_interval_bu
BEFORE UPDATE ON jury_vigilantes
FOR EACH ROW
BEGIN
  IF NEW.jury_id != OLD.jury_id THEN
    SELECT inicio, fim INTO NEW.juri_inicio, NEW.juri_fim
    FROM juries
    WHERE id = NEW.jury_id;
  END IF;
END$$

-- 3. Validar capacidade de vigilantes
DROP TRIGGER IF EXISTS trg_jv_check_cap$$

CREATE TRIGGER trg_jv_check_cap
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE cap INT DEFAULT 0;
  DECLARE qtd INT DEFAULT 0;
  
  IF NEW.papel = 'vigilante' THEN
    SELECT vigilantes_capacidade INTO cap
    FROM juries
    WHERE id = NEW.jury_id;
    
    SELECT COUNT(*) INTO qtd
    FROM jury_vigilantes
    WHERE jury_id = NEW.jury_id AND papel = 'vigilante';
    
    IF qtd >= cap THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Capacidade de vigilantes atingida';
    END IF;
  END IF;
END$$

-- 4. Validar supervisor único
DROP TRIGGER IF EXISTS trg_jv_supervisor_unico$$

CREATE TRIGGER trg_jv_supervisor_unico
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE existe INT DEFAULT 0;
  
  IF NEW.papel = 'supervisor' THEN
    SELECT COUNT(*) INTO existe
    FROM jury_vigilantes
    WHERE jury_id = NEW.jury_id AND papel = 'supervisor';
    
    IF existe > 0 THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Júri já possui supervisor';
    END IF;
  END IF;
END$$

-- 5. Prevenir conflito de horário no INSERT
DROP TRIGGER IF EXISTS trg_jv_no_overlap_ins$$

CREATE TRIGGER trg_jv_no_overlap_ins
BEFORE INSERT ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE confl INT DEFAULT 0;
  DECLARE inicio_novo DATETIME;
  DECLARE fim_novo DATETIME;
  
  SELECT inicio, fim INTO inicio_novo, fim_novo
  FROM juries
  WHERE id = NEW.jury_id;
  
  SELECT COUNT(*) INTO confl
  FROM jury_vigilantes jv
  WHERE jv.vigilante_id = NEW.vigilante_id
    AND jv.juri_inicio IS NOT NULL
    AND jv.juri_fim IS NOT NULL
    AND fim_novo > jv.juri_inicio
    AND inicio_novo < jv.juri_fim;
  
  IF confl > 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Conflito de horário';
  END IF;
END$$

-- 6. Prevenir conflito de horário no UPDATE
DROP TRIGGER IF EXISTS trg_jv_no_overlap_upd$$

CREATE TRIGGER trg_jv_no_overlap_upd
BEFORE UPDATE ON jury_vigilantes
FOR EACH ROW
BEGIN
  DECLARE confl INT DEFAULT 0;
  DECLARE inicio_novo DATETIME;
  DECLARE fim_novo DATETIME;
  
  IF NEW.jury_id != OLD.jury_id OR NEW.vigilante_id != OLD.vigilante_id THEN
    SELECT inicio, fim INTO inicio_novo, fim_novo
    FROM juries
    WHERE id = NEW.jury_id;
    
    SELECT COUNT(*) INTO confl
    FROM jury_vigilantes jv
    WHERE jv.vigilante_id = NEW.vigilante_id
      AND jv.id != NEW.id
      AND jv.juri_inicio IS NOT NULL
      AND jv.juri_fim IS NOT NULL
      AND fim_novo > jv.juri_inicio
      AND inicio_novo < jv.juri_fim;
    
    IF confl > 0 THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Conflito de horário';
    END IF;
  END IF;
END$$

DELIMITER ;
```

### Passo 5: Verificar
Execute para verificar se os triggers foram criados:

```sql
SHOW TRIGGERS LIKE 'jury_vigilantes';
```

Deve retornar 6 triggers.

---

## 📋 Opção 2: Via MySQL Command Line

### Passo 1: Abrir MySQL
```bash
cd C:\xampp\mysql\bin
mysql.exe -u root comexamesul
```

### Passo 2: Executar arquivo
Dentro do MySQL:
```sql
source C:/xampp/htdocs/comexamesul/app/Database/migrations_triggers.sql
```

---

## ✅ Verificação Final

Execute no phpMyAdmin ou MySQL:

```sql
-- 1. Verificar colunas em juries
DESCRIBE juries;
-- Deve mostrar: inicio, fim, vigilantes_capacidade

-- 2. Verificar colunas em jury_vigilantes  
DESCRIBE jury_vigilantes;
-- Deve mostrar: papel, juri_inicio, juri_fim

-- 3. Verificar view
SELECT * FROM vw_docente_score LIMIT 5;

-- 4. Verificar triggers
SHOW TRIGGERS LIKE 'jury_vigilantes';
-- Deve mostrar 6 triggers

-- 5. Verificar índices
SHOW INDEX FROM juries WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM jury_vigilantes WHERE Key_name LIKE 'idx_%';
```

---

## 🎯 Após Instalação

Você pode testar o sistema:

1. Acessar: `http://localhost/juries/planning`
2. Clicar em **"Gerar Plano (Auto)"**
3. Preencher Local e Data
4. Revisar e Aplicar plano

---

## 🐛 Problemas Comuns

### Erro: "Table doesn't exist"
- Verificar se executou `migrations_auto_allocation_simple.sql` primeiro

### Erro: "Trigger already exists"
- Normal, é um aviso ao reexecutar. Pode ignorar.

### Erro: "Syntax error near DELIMITER"
- Usar phpMyAdmin em vez de linha de comando

---

**Status**: Sistema pronto após executar triggers! 🚀
