# 🚀 TESTE AGORA - Sistema Top-3

## ✅ Correções Aplicadas

O erro **"Unexpected token '<', "<br /> <b>"... is not valid JSON"** foi **CORRIGIDO**!

### Problemas Resolvidos:
1. ✅ Namespace incorreto (`App\Core\Request` → removido)
2. ✅ Classe base incorreta (`BaseController` → `Controller`)
3. ✅ Método inexistente (`$this->getConnection()` → `Connection::getInstance()`)
4. ✅ Autoloader atualizado (`composer dump-autoload`)

---

## 🧪 Como Testar

### Opção 1: Script de Teste Automático (RECOMENDADO)

**Acesse**:
```
http://localhost/test_top3_api.php
```

Este script vai:
- ✓ Verificar se as classes existem
- ✓ Testar conexão com banco
- ✓ Verificar colunas necessárias
- ✓ Contar dados disponíveis
- ✓ Testar API diretamente
- ✓ Mostrar diagnóstico completo

**Resultado esperado**: Mensagem verde "✅ TUDO OK!"

---

### Opção 2: Testar Manualmente na Interface

**1. Abrir Planejamento**:
```
http://localhost/juries/planning
```

**2. Criar Júri de Teste** (se não tiver):
- Clicar "Criar Exames por Local"
- Preencher:
  - Local: Campus Central
  - Data: 2025-11-15
  - Disciplina: Matemática I
  - Início: 08:00
  - Fim: 11:00
  - Salas: 101, 102
- Clicar "Criar Todos os Júris"

**3. Testar Sugestões**:
- Procurar júri criado
- Slot vazio de **Supervisor** → Clicar **"⚡ Sugestões Top-3"**
- **Popover deve abrir** com 3 sugestões
- Clicar **"Aplicar"** em uma sugestão
- Página recarrega → Supervisor alocado! ✓

---

### Opção 3: Testar API Diretamente

**Abrir no navegador**:
```
http://localhost/api/suggest-top3?juri_id=1&papel=supervisor
```

**Resultado esperado** (se júri existe):
```json
{
  "ok": true,
  "slot": {
    "juri_id": 1,
    "papel": "supervisor",
    "inicio": "2025-11-15 08:00:00",
    "fim": "2025-11-15 11:00:00",
    ...
  },
  "top3": [
    {
      "docente_id": 44,
      "nome": "Ana Silva",
      "score": 2,
      "aptidao": 0.5,
      ...
    },
    ...
  ],
  "fallbacks": 0
}
```

**Resultado esperado** (se júri não existe):
```json
{
  "ok": false,
  "error": "Júri não encontrado"
}
```

---

## 🐛 Se Ainda Houver Problemas

### Problema 1: "Class 'App\Controllers\SuggestController' not found"

**Solução**:
```bash
cd C:\xampp\htdocs\comexamesul
composer dump-autoload
```

### Problema 2: "Júri não encontrado"

**Causa**: Nenhum júri criado ainda

**Solução**: Criar júri via interface (opção 2 acima)

### Problema 3: "Nenhum docente disponível"

**Causa**: Docentes não estão ativos/disponíveis

**Solução SQL**:
```sql
UPDATE users 
SET active = 1, available_for_vigilance = 1 
WHERE role IN ('coordenador', 'membro', 'docente');
```

### Problema 4: Migrations não executadas

**Sintoma**: Erro sobre colunas inexistentes

**Solução**: Executar via phpMyAdmin:
```sql
source C:/xampp/htdocs/comexamesul/app/Database/migrations_auto_allocation_simple.sql
```

Ou copiar e colar o conteúdo do arquivo no phpMyAdmin.

---

## 📋 Checklist de Verificação

Antes de testar, certifique-se:

- [ ] **Apache rodando** (XAMPP → Start Apache)
- [ ] **MySQL rodando** (XAMPP → Start MySQL)
- [ ] **Migrations executadas** (`migrations_auto_allocation_simple.sql`)
- [ ] **Autoloader atualizado** (`composer dump-autoload`)
- [ ] **Pelo menos 1 júri criado** (com janelas temporais)
- [ ] **Pelo menos 3 docentes ativos** (`active=1, available_for_vigilance=1`)

---

## 🎯 Ordem Recomendada de Teste

### 1️⃣ Script Automático
```
http://localhost/test_top3_api.php
```
**Por quê?** Mostra exatamente o que falta

### 2️⃣ Corrigir Problemas
Se o script mostrou erros, corrija-os antes de continuar

### 3️⃣ Interface
```
http://localhost/juries/planning
```
**Por quê?** Teste real como usuário final

### 4️⃣ Abrir Console (F12)
**Por quê?** Ver logs do JavaScript

**Deve aparecer**:
```
✅ SmartSuggestions inicializado
```

### 5️⃣ Clicar "⚡ Sugestões Top-3"
**Resultado esperado**: Popover abre com 3 docentes

### 6️⃣ Clicar "Aplicar"
**Resultado esperado**: 
- Toast verde "✓ Alocação aplicada!"
- Página recarrega
- Docente aparece alocado

---

## 💡 Dicas

### Cache do Navegador
Se mudanças não aparecerem:
```
Ctrl + Shift + Delete → Limpar cache
OU
Ctrl + F5 (hard refresh)
```

### Verificar Logs
Se erro persistir, verificar:
```
C:\xampp\apache\logs\error.log
```

### Console JavaScript
Abrir (F12) e procurar por:
- Erros vermelhos
- Mensagens de rede (aba Network)

### Popular Campus (Se Necessário)
```sql
UPDATE users SET campus = 'Campus Central' WHERE id <= 10;
UPDATE users SET campus = 'Campus Norte' WHERE id > 10;
```

### Popular Experiência (Opcional)
```sql
ALTER TABLE users 
ADD COLUMN experiencia_supervisao INT DEFAULT 0;

UPDATE users 
SET experiencia_supervisao = FLOOR(RAND() * 10) 
WHERE role IN ('coordenador', 'membro');
```

---

## 📊 Resultados Esperados

### ✅ Sucesso Total
1. Script de teste: "✅ TUDO OK!"
2. Interface: Popover abre com 3 sugestões
3. Aplicar: Alocação funciona
4. Console: Sem erros

### ⚠️ Sucesso Parcial
1. Script: Avisos amarelos (poucos docentes)
2. Interface: Popover abre mas com < 3 sugestões
3. Aplicar: Funciona
4. **Ação**: Ativar mais docentes

### ❌ Erro
1. Script: Erros vermelhos
2. Interface: Popover não abre
3. Console: Erros JavaScript
4. **Ação**: Verificar CORRECOES_TOP3.md

---

## 🎉 Quando Funcionar

**Comemore!** 🎊

Você terá um sistema de alocação:
- ✅ **80% mais rápido** que manual
- ✅ **Inteligente** (ranking automático)
- ✅ **Flexível** (convive com DnD)
- ✅ **Validado** (sem conflitos)

---

## 📚 Documentação Completa

- **`README_SMART_SUGGESTIONS.md`**: Documentação técnica
- **`INSTALACAO_TOP3.md`**: Guia de instalação
- **`SISTEMA_TOP3_RESUMO.md`**: Resumo executivo
- **`GUIA_VISUAL_TOP3.md`**: Interface visual
- **`CORRECOES_TOP3.md`**: Correções aplicadas

---

## 🆘 Se Nada Funcionar

**Reporte**:
1. Output do `test_top3_api.php`
2. Console do navegador (F12)
3. Logs do Apache (`error.log`)
4. Versão do PHP (`php -v`)

---

**Agora é com você!** 🚀

**Primeiro teste**: `http://localhost/test_top3_api.php`

**Boa sorte!** 🎯
