# 🔄 Sistema de Alteração de Disponibilidade - v2.3.1

**Data**: 11/10/2025  
**Versão**: 2.3.1  
**Status**: ✅ Implementado

---

## 🎯 Objetivo

Permitir que vigilantes alterem sua **disponibilidade geral** (disponível ↔ indisponível) para alocação em júris, com **justificativa obrigatória** quando já estiverem alocados.

---

## 🔄 Diferença: Cancelamento vs Alteração

### **Cancelamento de Candidatura**
- ❌ Cancela uma **candidatura específica** a uma vaga
- Desvincula o vigilante daquela vaga/concurso
- Tipo: `cancelamento`

### **Alteração de Disponibilidade** (NOVO)
- 🔄 Altera **status geral** de disponibilidade
- Afeta **todas** as alocações atuais e futuras
- Tipos: 
  - Disponível → Indisponível
  - Indisponível → Disponível
- Tipo: `alteracao`

---

## 🔄 Fluxos do Sistema

### **Cenário 1: Vigilante NÃO Alocado**
```
Vigilante clica botão para alterar status
  ↓
Sistema verifica alocações
  ↓
NÃO tem alocação → Mudança IMEDIATA ✅
  ↓
Status atualizado instantaneamente
```

### **Cenário 2: Vigilante JÁ Alocado**
```
Vigilante clica botão para alterar status
  ↓
Sistema verifica alocações
  ↓
TEM alocação → Exige JUSTIFICATIVA
  ↓
Formulário com:
  - Lista de júris alocados
  - Justificativa (mínimo 20 chars)
  - Upload opcional de documento
  ↓
Solicitação → Status PENDENTE
  ↓
Coordenador revisa e aprova/rejeita
```

---

## 🎨 Interface do Usuário

### **Seção "Disponibilidade Geral" (`/availability`)**

#### **Quando DISPONÍVEL:**
```
┌────────────────────────────────────┐
│ Disponibilidade Geral              │
│ Status: [Disponível ✓]             │
├────────────────────────────────────┤
│ ┌──────────┐  ┌──────────┐        │
│ │Disponível│  │Indisponível│       │
│ │    ✓     │  │  (clique)  │       │
│ │ [ATUAL]  │  │  [MUDAR]   │       │
│ └──────────┘  └──────────┘        │
└────────────────────────────────────┘
```

#### **Quando INDISPONÍVEL:**
```
┌────────────────────────────────────┐
│ Disponibilidade Geral              │
│ Status: [Indisponível ✗]           │
├────────────────────────────────────┤
│ ┌──────────┐  ┌──────────┐        │
│ │Disponível│  │Indisponível│       │
│ │ (clique) │  │     ✗      │       │
│ │ [MUDAR]  │  │  [ATUAL]   │       │
│ └──────────┘  └──────────┘        │
└────────────────────────────────────┘
```

### **Alerta de Solicitação Pendente:**
Se houver solicitação pendente, mostra aviso amarelo:
```
⚠️ Solicitação de Alteração Pendente
   Você tem uma solicitação aguardando aprovação
```

---

## 📂 Arquivos Implementados

### **Controller:**
- ✅ `app/Controllers/AvailabilityController.php`
  - `requestAvailabilityChange()` - Verificar e processar mudança
  - `submitAvailabilityChange()` - Enviar solicitação com justificativa

### **View:**
- ✅ `app/Views/availability/request_change.php` - Formulário de justificativa
- ✅ `app/Views/availability/index.php` - Seção de controle adicionada

### **Rotas:**
```php
GET  /availability/change/{status}  // 0=indisponível, 1=disponível
POST /availability/change/submit
```

---

## 🛠️ Funcionalidades Implementadas

### **1. Verificação Automática de Alocação**
```php
// Controller verifica se está alocado
$allocations = $juryVigilanteModel->getByVigilante($vigilanteId);

if (empty($allocations)) {
    // Mudança direta
    $userModel->updateUser($vigilanteId, [
        'available_for_vigilance' => $newStatus
    ]);
} else {
    // Exige justificativa
    return view('availability/request_change');
}
```

### **2. Toggle de Status Visual**
- ✅ Cards clicáveis com status atual destacado
- ✅ Cores dinâmicas (verde/cinza)
- ✅ Ícones indicativos

### **3. Formulário de Justificativa**
**Elementos:**
- 📋 Lista de júris alocados (visual destacado)
- ✍️ Justificativa obrigatória (mínimo 20 caracteres)
- 📎 Upload opcional de documento
- ⚠️ Avisos sobre impacto da mudança

### **4. Solicitações Reutilizam Infraestrutura**
- Usa mesma tabela `availability_change_requests`
- Diferencia por campo `request_type`:
  - `'cancelamento'` - Cancelar candidatura
  - `'alteracao'` - Alterar disponibilidade

---

## 🧪 Como Testar

### **Teste 1: Mudança Direta (Sem Alocação)**
1. Login como vigilante
2. **NÃO esteja** alocado a nenhum júri
3. Vá em **Disponibilidade** (`/availability`)
4. Veja seção "Disponibilidade Geral"
5. Clique no card oposto ao status atual
6. ✅ Deve mudar instantaneamente
7. ✅ Mensagem: "Disponibilidade atualizada"

**Exemplo:**
- Status atual: Indisponível
- Clica em "Disponível"
- ✅ Muda para disponível imediatamente

### **Teste 2: Mudança com Justificativa (Com Alocação)**
1. Login como vigilante
2. **SEJA alocado** a 1+ júris
3. Vá em **Disponibilidade**
4. Clique no card oposto ao status atual
5. ✅ Deve abrir formulário de justificativa
6. ✅ Deve mostrar lista de júris alocados
7. Preencha justificativa (20+ caracteres)
8. (Opcional) Anexe documento
9. Envie solicitação
10. ✅ Mensagem: "Solicitação enviada"
11. ✅ Alerta amarelo aparece na página

### **Teste 3: Solicitação Pendente**
1. Com solicitação pendente existente
2. Recarregue página de disponibilidade
3. ✅ Deve mostrar alerta amarelo no topo
4. ✅ Botão de mudança ainda disponível (pode criar nova)

### **Teste 4: Disponível → Indisponível (Com Alocação)**
1. Vigilante DISPONÍVEL e ALOCADO
2. Clica "Indisponível"
3. ✅ Formulário cor vermelha
4. ✅ Aviso: "Você será desalocado dos júris"
5. Envia justificativa
6. ✅ Solicitação criada

### **Teste 5: Indisponível → Disponível (Com Alocação)**
1. Vigilante INDISPONÍVEL mas (teoricamente) ALOCADO
2. Clica "Disponível"
3. ✅ Formulário cor verde
4. Envia justificativa
5. ✅ Solicitação criada

---

## 📊 Dados Armazenados

### **Tabela: `availability_change_requests`**
```sql
INSERT INTO availability_change_requests (
    vigilante_id,
    application_id,          -- Candidatura aprovada do vigilante
    request_type,            -- 'alteracao'
    reason,                  -- Justificativa
    attachment_path,         -- Documento (se anexado)
    has_allocation,          -- 1 se alocado, 0 se não
    jury_details,            -- JSON: {new_status, juries:[...]}
    status,                  -- 'pendente'
    ...
);
```

**Exemplo de `jury_details`:**
```json
{
  "new_status": 0,
  "juries": [
    {
      "jury_id": 5,
      "subject": "Matemática",
      "exam_date": "2025-01-15",
      "location": "Campus A",
      "room": "101"
    }
  ]
}
```

---

## 🔐 Validações

### **Backend:**
1. ✅ Apenas vigilante logado
2. ✅ Verifica alocações automaticamente
3. ✅ Justificativa obrigatória se alocado (mínimo 20 chars)
4. ✅ Validação de arquivo (tipo e tamanho)
5. ✅ Requer candidatura aprovada para vincular

### **Frontend:**
6. ✅ Toggle visual do status atual
7. ✅ Alerta de solicitação pendente
8. ✅ Mensagens claras de sucesso/erro
9. ✅ Upload drag & drop

---

## 🎯 Diferenças de Comportamento

| Situação | Cancelamento | Alteração Disponibilidade |
|----------|--------------|---------------------------|
| **O que altera** | Candidatura específica | Status geral |
| **Afeta** | 1 vaga | Todas alocações |
| **Quando exige justificativa** | Se alocado | Se alocado |
| **Campo `request_type`** | `'cancelamento'` | `'alteracao'` |
| **Campo `jury_details`** | Array de júris | `{new_status, juries}` |
| **Botão na interface** | "Cancelar" (candidatura) | Cards de status |

---

## 📋 Logs de Atividade

### **Mudança Direta:**
```sql
SELECT * FROM activity_log 
WHERE entity = 'users' 
  AND action = 'update_availability'
  AND JSON_EXTRACT(metadata, '$.direct') = true;
```

### **Solicitação de Mudança:**
```sql
SELECT * FROM activity_log 
WHERE entity = 'availability_change_requests' 
  AND action = 'create_change';
```

---

## 🚧 Próximas Implementações (v2.4)

### **Interface de Revisão (Coordenador)**
- [ ] Listar solicitações pendentes (cancelamento + alteração)
- [ ] Ver detalhes:
  - Tipo: cancelamento ou alteração
  - Novo status (se alteração)
  - Júris afetados
  - Justificativa e documento
- [ ] Aprovar/rejeitar solicitações
- [ ] Ao aprovar alteração de disponibilidade:
  - Atualizar campo `available_for_vigilance`
  - Se mudou para indisponível: desalocar dos júris
  - Notificar vigilante

### **Notificações**
- [ ] Email ao vigilante (solicitação enviada)
- [ ] Email ao coordenador (nova solicitação)
- [ ] Email ao vigilante (aprovada/rejeitada)
- [ ] Indicar novo status após aprovação

### **Dashboard**
- [ ] Estatísticas de solicitações
- [ ] Gráfico: cancelamentos vs alterações
- [ ] Motivos mais comuns

---

## ✅ Checklist de Implementação

### **Backend:**
- [x] Método `requestAvailabilityChange()`
- [x] Método `submitAvailabilityChange()`
- [x] Verificação automática de alocação
- [x] Mudança direta (sem alocação)
- [x] Reutilização da tabela existente
- [x] Upload de documentos
- [x] Validações completas

### **Frontend:**
- [x] Seção "Disponibilidade Geral"
- [x] Cards clicáveis de status
- [x] Alerta de solicitação pendente
- [x] Formulário `request_change.php`
- [x] Cores dinâmicas (verde/vermelho)
- [x] Upload drag & drop

### **Rotas:**
- [x] GET `/availability/change/{status}`
- [x] POST `/availability/change/submit`

### **Pendente (v2.4):**
- [ ] Interface de revisão (coordenador)
- [ ] Aprovação de alterações
- [ ] Desalocação automática
- [ ] Notificações

---

## 🎉 Status Final

**Implementação**: ✅ **Concluída (100% Vigilante)**

### **Funcional:**
- ✅ Vigilante pode alterar disponibilidade
- ✅ Sistema detecta alocação automaticamente
- ✅ Mudança direta se não alocado
- ✅ Justificativa obrigatória se alocado
- ✅ Upload de documentos
- ✅ Interface intuitiva
- ✅ Logs de auditoria

### **Próxima Fase:**
- ⏳ Interface de revisão para coordenadores
- ⏳ Fluxo de aprovação/rejeição
- ⏳ Atualização automática de status
- ⏳ Desalocação automática

---

## 📸 Casos de Uso Completos

### **Caso 1: Vigilante Quer Ficar Indisponível (Sem Júris)**
**Situação:** João está disponível mas não foi alocado ainda.
**Ação:**
1. João clica em "Indisponível"
2. ✅ Sistema muda status instantaneamente
3. ✅ João não aparece mais para novos júris

### **Caso 2: Vigilante Quer Ficar Indisponível (Com Júris)**
**Situação:** Maria está disponível e alocada em 3 júris.
**Ação:**
1. Maria clica em "Indisponível"
2. ✅ Sistema mostra os 3 júris
3. Maria escreve: "Cirurgia marcada para essa semana"
4. Maria anexa atestado médico
5. ✅ Solicitação criada (status pendente)
6. Coordenador aprova
7. ✅ Maria desalocada dos 3 júris
8. ✅ Status muda para indisponível

### **Caso 3: Vigilante Quer Voltar a Ficar Disponível**
**Situação:** Pedro está indisponível há 2 meses.
**Ação:**
1. Pedro clica em "Disponível"
2. Se não tem júris → mudança imediata
3. Se tem júris (raro) → justificativa
4. ✅ Pedro volta a aparecer para alocações futuras

---

**🚀 Sistema completo e funcional para vigilantes!**

Vigilantes agora têm controle total sobre sua disponibilidade, com processo transparente e justificado.
