# 🐛 RESOLVER "Erro Interno" - Guia Completo

## ❌ Problema

Ao clicar em "⚡ Sugestões Top-3", aparece mensagem:
```
"Erro interno"
```

## 🔍 Causas Prováveis

### Causa #1: Coluna `experiencia_supervisao` não existe ⚠️ MAIS PROVÁVEL
O código tenta buscar essa coluna, mas ela pode não existir na tabela `users`.

### Causa #2: Colunas de janelas temporais não existem
Campos `inicio`, `fim` na tabela `juries`.

### Causa #3: Nenhum docente disponível
Todos os docentes estão inativos ou indisponíveis.

---

## ✅ SOLUÇÃO RÁPIDA (3 Passos)

### Passo 1: Executar Script de Debug

**Abra no NAVEGADOR**:
```
http://localhost/debug_top3.php
```

Este script vai:
- ✓ Mostrar o erro EXATO
- ✓ Verificar sessão
- ✓ Verificar docentes
- ✓ Testar API diretamente
- ✓ Mostrar exceção capturada

**Procure por**: Seção "EXCEÇÃO CAPTURADA" (texto vermelho)

---

### Passo 2: Adicionar Coluna `experiencia_supervisao`

**Via phpMyAdmin**:

1. Abrir: `http://localhost/phpmyadmin`
2. Selecionar banco: `comexamesul`
3. Clicar na aba "SQL"
4. Copiar e colar:

```sql
-- Verificar se coluna existe
SELECT COUNT(*) AS existe
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME = 'experiencia_supervisao';

-- Se retornar 0, adicionar coluna:
ALTER TABLE users 
ADD COLUMN experiencia_supervisao INT DEFAULT 0 
COMMENT 'Experiência em supervisão (0-10)';

-- Popular com valores de exemplo
UPDATE users 
SET experiencia_supervisao = FLOOR(RAND() * 10) 
WHERE role IN ('coordenador', 'membro', 'docente');
```

5. Clicar "Executar"

**OU executar script pronto**:
```
Copiar: app/Database/add_experiencia_supervisao.sql
Colar no phpMyAdmin → SQL → Executar
```

---

### Passo 3: Ativar Docentes

**Via phpMyAdmin** (aba SQL):

```sql
-- Ver status atual
SELECT 
    COUNT(*) AS total,
    SUM(active = 1) AS ativos,
    SUM(available_for_vigilance = 1) AS disponiveis
FROM users
WHERE role IN ('coordenador', 'membro', 'docente');

-- Ativar TODOS os docentes
UPDATE users 
SET 
    active = 1,
    available_for_vigilance = 1
WHERE role IN ('coordenador', 'membro', 'docente');

-- Verificar resultado
SELECT id, name, active, available_for_vigilance
FROM users
WHERE role IN ('coordenador', 'membro', 'docente')
LIMIT 10;
```

---

## 🧪 TESTAR NOVAMENTE

### 1. Limpar Cache do Navegador
```
Ctrl + Shift + Delete → Limpar cache
OU
Ctrl + F5 (hard refresh)
```

### 2. Testar Debug
```
http://localhost/debug_top3.php
```

**Resultado esperado**: 
- ✅ "Usuário autenticado"
- ✅ "Júri encontrado"
- ✅ "Controller executado"
- ✅ "JSON válido"
- ✅ "API retornou sucesso!"

### 3. Testar Interface
```
http://localhost/juries/planning
```

Clicar: **"⚡ Sugestões Top-3"**

**Resultado esperado**: Popover com 3 sugestões

---

## 🔍 DIAGNÓSTICO AVANÇADO

### Ver Logs do Apache

**Windows**:
```
C:\xampp\apache\logs\error.log
```

**Procurar por**:
- "Erro em suggest/top3:"
- "PDOException"
- "Unknown column"

**Últimas 50 linhas**:
```powershell
Get-Content C:\xampp\apache\logs\error.log -Tail 50
```

### Verificar Colunas Manualmente

```sql
-- Colunas de users
SHOW COLUMNS FROM users;

-- Colunas de juries
SHOW COLUMNS FROM juries;

-- Verificar específicas
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME IN (
      'campus', 
      'active', 
      'available_for_vigilance', 
      'experiencia_supervisao'
  );
```

### Verificar Júris

```sql
-- Verificar júris com janelas
SELECT 
    COUNT(*) AS total,
    SUM(inicio IS NOT NULL) AS com_inicio,
    SUM(fim IS NOT NULL) AS com_fim
FROM juries;

-- Ver um júri completo
SELECT * FROM juries LIMIT 1;
```

---

## 🛠️ CORREÇÕES APLICADAS

### ✅ Fallback para `experiencia_supervisao`

O código agora verifica se a coluna existe antes de usá-la:

```php
// Antes ❌
SELECT experiencia_supervisao FROM users

// Agora ✅
if (coluna existe) {
    SELECT experiencia_supervisao FROM users
} else {
    SELECT 0 AS experiencia_supervisao FROM users
}
```

Mesmo sem a coluna, o sistema funciona (todos com aptidão 0).

---

## 🐛 PROBLEMAS ESPECÍFICOS

### Erro: "Unknown column 'experiencia_supervisao'"

**Solução**: Executar Passo 2 (adicionar coluna)

### Erro: "Unknown column 'inicio'"

**Solução**: Executar migrations:
```sql
source C:/xampp/htdocs/comexamesul/app/Database/migrations_auto_allocation_simple.sql
```

### Erro: "Nenhum docente disponível"

**Solução**: Executar Passo 3 (ativar docentes)

### Erro: "Júri não encontrado"

**Solução**: Criar júri via interface:
```
/juries/planning → "Criar Exames por Local"
```

### Erro: "CSRF token inválido" (ao aplicar)

**Solução**: Recarregar página (Ctrl+F5)

---

## 📊 CHECKLIST DE VERIFICAÇÃO

Execute este SQL para verificar tudo:

```sql
-- 1. Verificar estrutura
SELECT 'Verificando estrutura...' AS etapa;

SELECT 
    'users' AS tabela,
    COLUMN_NAME,
    CASE 
        WHEN COLUMN_NAME IN ('campus', 'active', 'available_for_vigilance') THEN '✅ OBRIGATÓRIA'
        WHEN COLUMN_NAME = 'experiencia_supervisao' THEN '⚠️ RECOMENDADA'
        ELSE '✓ OK'
    END AS status
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME IN (
      'campus', 
      'active', 
      'available_for_vigilance', 
      'experiencia_supervisao'
  );

-- 2. Verificar dados
SELECT 'Verificando dados...' AS etapa;

SELECT 
    COUNT(*) AS total_docentes,
    SUM(active = 1) AS ativos,
    SUM(available_for_vigilance = 1) AS disponiveis,
    SUM(active = 1 AND available_for_vigilance = 1) AS elegiveis
FROM users
WHERE role IN ('coordenador', 'membro', 'docente');

-- 3. Verificar júris
SELECT 'Verificando júris...' AS etapa;

SELECT 
    COUNT(*) AS total_juris,
    SUM(inicio IS NOT NULL AND fim IS NOT NULL) AS com_janelas
FROM juries;

-- 4. Diagnóstico final
SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM juries WHERE inicio IS NOT NULL) = 0 
        THEN '❌ Execute migrations! Júris sem janelas temporais.'
        WHEN (SELECT COUNT(*) FROM users WHERE active=1 AND available_for_vigilance=1) < 3
        THEN '⚠️ Ative mais docentes! Menos de 3 disponíveis.'
        ELSE '✅ TUDO OK! Sistema pronto.'
    END AS diagnostico;
```

---

## 🎯 RESUMO EXECUTIVO

| Ação | Comando | Obrigatório? |
|------|---------|--------------|
| **1. Ver erro exato** | `http://localhost/debug_top3.php` | ✅ SIM |
| **2. Adicionar coluna** | Executar SQL acima | ⚠️ Recomendado |
| **3. Ativar docentes** | `UPDATE users SET active=1...` | ✅ SIM |
| **4. Testar novamente** | `/juries/planning` | ✅ SIM |

---

## 🆘 AINDA COM ERRO?

### Copie e envie:

1. **Output do debug**:
   ```
   http://localhost/debug_top3.php
   → Copiar toda a seção "EXCEÇÃO CAPTURADA"
   ```

2. **Últimas linhas do log**:
   ```powershell
   Get-Content C:\xampp\apache\logs\error.log -Tail 20
   ```

3. **Estrutura das tabelas**:
   ```sql
   SHOW CREATE TABLE users;
   SHOW CREATE TABLE juries;
   SHOW CREATE TABLE jury_vigilantes;
   ```

---

**Correção aplicada**: 2025-10-10 12:49  
**Arquivo**: SuggestController.php  
**Mudança**: Fallback para coluna experiencia_supervisao  
**Status**: ✅ Pronto para teste  

**Próxima ação**: Executar `http://localhost/debug_top3.php` 🔍
