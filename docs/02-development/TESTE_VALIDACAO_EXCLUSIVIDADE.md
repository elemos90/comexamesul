# 🧪 Teste de Validação: Exclusividade por Júri

## ✅ Validação Implementada

**Regra**: Uma pessoa **NÃO** pode ser supervisor E vigilante no **MESMO júri**.

---

## 🔬 Casos de Teste

### Teste 1: Tentar Adicionar Vigilante que Já é Supervisor ❌

**Cenário**:
1. Júri A tem Dr. João Silva como **supervisor**
2. Tentar adicionar Dr. João Silva como **vigilante** no Júri A

**Resultado Esperado**:
```
❌ CONFLITO: Esta pessoa já é SUPERVISOR deste júri. 
Uma pessoa não pode ser supervisor e vigilante no mesmo júri.
```

**Como Testar**:
```
1. Acesse: alocar_equipe.php
2. Encontre um júri com supervisor alocado
3. No dropdown de vigilantes, selecione o mesmo supervisor
4. Clique "Adicionar Vigilante"
5. Verá a mensagem de erro
```

---

### Teste 2: Tentar Adicionar Supervisor que Já é Vigilante ❌

**Cenário**:
1. Júri B tem Maria Santos como **vigilante**
2. Tentar adicionar Maria Santos como **supervisor** no Júri B

**Resultado Esperado**:
```
❌ CONFLITO: Esta pessoa já é VIGILANTE deste júri. 
Uma pessoa não pode ser supervisor e vigilante no mesmo júri.
```

**Como Testar**:
```
1. Acesse: alocar_equipe.php
2. Encontre um júri com vigilante alocado
3. No dropdown de supervisor, selecione o mesmo vigilante
4. Clique "Alocar Supervisor"
5. Verá a mensagem de erro
```

---

### Teste 3: Mesma Pessoa em Júris Diferentes ✅

**Cenário**:
1. Dr. João Silva é **supervisor** do Júri A (MAT1, Campus Central)
2. Adicionar Dr. João Silva como **vigilante** no Júri C (QUI1, Campus Central, horário diferente)

**Resultado Esperado**:
```
✅ Vigilante alocado com sucesso!
```

**Nota**: Permitido pois são júris **diferentes**. Apenas validará conflito de horário normal.

**Como Testar**:
```
1. Acesse: alocar_equipe.php
2. Alocar pessoa como supervisor em um júri
3. Alocar mesma pessoa como vigilante em OUTRO júri (horário diferente)
4. Deve funcionar sem erro
```

---

### Teste 4: Distribuição Automática ✅

**Cenário**:
1. Executar distribuição automática
2. Sistema deve evitar alocar mesma pessoa como supervisor E vigilante no mesmo júri

**Resultado Esperado**:
```
✅ Sistema automaticamente pula pessoas que já estão alocadas em outra função
```

**Como Testar**:
```
1. Criar alguns júris sem alocação
2. Acesse: distribuicao_automatica.php
3. Ver sugestões geradas
4. Verificar que nenhuma pessoa aparece 2x no mesmo júri
5. Aplicar sugestões
6. Verificar no mapa de alocações
```

---

## 📊 Matriz de Validação

| Júri | Pessoa | Função Atual | Tentar Adicionar | Resultado |
|------|---------|--------------|------------------|-----------|
| Júri A | João | Supervisor | Vigilante | ❌ ERRO |
| Júri A | Maria | Vigilante | Supervisor | ❌ ERRO |
| Júri A | Pedro | - | Supervisor | ✅ OK |
| Júri A | Pedro | Supervisor | Vigilante | ❌ ERRO |
| Júri B | João | - | Vigilante | ✅ OK (júri diferente) |
| Júri B | João | Vigilante | Supervisor | ❌ ERRO (mesmo júri) |

---

## 🔍 Verificação SQL

### Verificar Duplicações

```sql
-- Verificar se alguém é supervisor E vigilante no mesmo júri
SELECT 
    j.id as jury_id,
    j.exam_date,
    d.code as discipline,
    u.name as person_name,
    'SUPERVISOR' as role1,
    'VIGILANTE' as role2
FROM juries j
INNER JOIN jury_vigilantes jv ON jv.jury_id = j.id
INNER JOIN users u ON u.id = j.supervisor_id AND u.id = jv.vigilante_id
LEFT JOIN disciplines d ON d.id = j.discipline_id;
```

**Resultado esperado**: `0 linhas` (nenhuma duplicação)

---

## 🎯 Fluxo de Validação

### Ao Adicionar Vigilante

```
1. Receber jury_id e vigilante_id
2. Verificar: SELECT supervisor_id FROM juries WHERE id = jury_id
3. Se supervisor_id == vigilante_id: ERRO
4. Se não: Verificar conflitos de horário
5. Se OK: Inserir em jury_vigilantes
```

### Ao Adicionar Supervisor

```
1. Receber jury_id e supervisor_id
2. Verificar: SELECT * FROM jury_vigilantes WHERE jury_id = jury_id AND vigilante_id = supervisor_id
3. Se encontrou: ERRO
4. Se não: Verificar conflitos de local
5. Se OK: UPDATE juries SET supervisor_id
```

---

## 🤖 Distribuição Automática

### Lógica Implementada

```php
// Ao sugerir supervisor
foreach ($supervisors as $sup) {
    // Verificar se já é vigilante neste júri
    if (in_array($sup['id'], $juryVigilantesIds)) {
        continue; // Pular
    }
    // ... resto da validação
}

// Ao sugerir vigilantes
foreach ($vigilantes as $vig) {
    // Verificar se é o supervisor deste júri
    if ($vig['id'] == $jury['supervisor_id']) {
        continue; // Pular
    }
    // Verificar se é o supervisor sugerido
    if ($vig['id'] == $suggestion['supervisor_id']) {
        continue; // Pular
    }
    // ... resto da validação
}
```

---

## ✅ Checklist de Testes

- [ ] **Teste 1**: Adicionar vigilante que já é supervisor ❌
- [ ] **Teste 2**: Adicionar supervisor que já é vigilante ❌
- [ ] **Teste 3**: Mesma pessoa em júris diferentes ✅
- [ ] **Teste 4**: Distribuição automática não gera conflitos ✅
- [ ] **Teste 5**: Mensagens de erro são claras
- [ ] **Teste 6**: Não há duplicações no banco (SQL check)

---

## 🎓 Cenário Real de Teste

### Setup Inicial

```sql
-- Criar júri de teste
INSERT INTO juries (exam_date, start_time, end_time, ...) 
VALUES ('2025-11-15', '08:00:00', '11:00:00', ...);

-- Alocar supervisor
UPDATE juries SET supervisor_id = 5 WHERE id = 1;
```

### Teste Manual

```
1. Acesse: http://localhost/comexamesul/public/alocar_equipe.php
2. Encontre o Júri 1
3. Tentar adicionar user_id=5 como vigilante
4. Ver erro: "Esta pessoa já é SUPERVISOR deste júri"
```

### Teste Automático

```
1. Acesse: distribuicao_automatica.php
2. Gerar sugestões
3. Verificar que user_id=5 não aparece como vigilante do Júri 1
4. Aplicar sugestões
5. Confirmar no banco: user_id=5 não está em jury_vigilantes para jury_id=1
```

---

## 📝 Logs de Validação

Ao tentar violar a regra, o sistema registra:

```
[2025-11-11 14:30:00] Tentativa de alocar vigilante 5 no júri 1
[2025-11-11 14:30:00] VALIDAÇÃO FALHOU: Pessoa já é supervisor
[2025-11-11 14:30:00] Mensagem exibida ao usuário
```

---

## 🎉 Resultado Final

✅ **Sistema garante**:
- Ninguém é supervisor E vigilante no mesmo júri
- Mensagens claras de erro
- Validação em alocação manual
- Validação em distribuição automática
- Zero duplicações no banco de dados

---

**Próximo Teste**: Execute os 6 casos acima e confirme! 🧪
