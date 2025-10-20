# 🔄 Sistema de Gestão de Candidaturas - Vigilante - v2.4.1

**Data**: 11/10/2025  
**Versão**: 2.4.1  
**Status**: ✅ Implementado

---

## 🎯 Objetivo

Permitir que vigilantes tenham **controle total** sobre suas candidaturas, podendo:
- ✅ **Cancelar** candidaturas (pendentes ou aprovadas)
- ✅ **Recandidatar-se** (após cancelamento ou rejeição)
- ✅ Com **justificativa obrigatória** quando alocados a júris

---

## 🔄 Fluxos por Status da Candidatura

### **Status: PENDENTE** (Aguardando Aprovação)
```
Vigilante vê candidatura pendente
  ↓
Opções:
  - [Cancelar] → Cancelamento IMEDIATO ✅
    (Sem justificativa, pois ainda não foi aprovada)
  ↓
Status muda para: CANCELADA
```

### **Status: APROVADA** (Aprovada pelo Coordenador)
```
Vigilante vê candidatura aprovada
  ↓
Opções:
  - [Cancelar] → Verifica se está alocado a júris
    ↓
    Se NÃO alocado:
      → Cancelamento IMEDIATO
    ↓
    Se ALOCADO:
      → Formulário de JUSTIFICATIVA
      → Upload de documento (opcional)
      → Solicitação PENDENTE
      → Aguarda aprovação do coordenador
```

### **Status: CANCELADA** (Cancelada pelo Vigilante)
```
Vigilante vê candidatura cancelada
  ↓
Opções:
  - [Recandidatar-se] → Verifica vaga e perfil
    ↓
    Vaga ainda aberta + Perfil completo:
      → Status volta para PENDENTE
      → Aguarda nova aprovação
    ↓
    Vaga fechada ou perfil incompleto:
      → Erro (não pode recandidatar)
```

### **Status: REJEITADA** (Rejeitada pelo Coordenador)
```
Vigilante vê candidatura rejeitada
  ↓
Opções:
  - [Recandidatar-se] → Mesma lógica de CANCELADA
    ↓
    Status volta para PENDENTE
    → Nova chance de aprovação
```

---

## 📊 Matriz de Ações por Status

| Status | Botão | Cor | Ação | Justificativa? |
|--------|-------|-----|------|----------------|
| **Pendente** | Cancelar | Cinza | Cancelamento direto | ❌ Não |
| **Aprovada** | Cancelar | Vermelho | Verifica alocação | ✅ Se alocado |
| **Cancelada** | Recandidatar-se | Azul | Volta para pendente | ❌ Não |
| **Rejeitada** | Recandidatar-se | Azul | Volta para pendente | ❌ Não |

---

## 🛠️ Funcionalidades Implementadas

### **1. Cancelamento Direto (Pendente)** ✅
**Quando:** Candidatura ainda não foi aprovada
**Botão:** "Cancelar" (cinza)
**Fluxo:**
```php
POST /applications/{id}/cancel-direct
  ↓
Verifica:
  - Vigilante é dono da candidatura
  - Status é 'pendente'
  ↓
Muda status para 'cancelada'
  ↓
Mensagem: "Candidatura cancelada com sucesso"
```

**Por que não exige justificativa?**
- Candidatura ainda não foi aprovada
- Vigilante não está alocado a júris
- Sem impacto no planejamento

### **2. Cancelamento com Justificativa (Aprovada)** ✅
**Quando:** Candidatura já foi aprovada
**Botão:** "Cancelar" (vermelho)
**Fluxo:**
```php
GET /availability/{id}/cancel
  ↓
Verifica se está alocado a júris
  ↓
NÃO alocado:
  → Cancelamento direto
  → Status: cancelada
  ↓
ALOCADO:
  → Formulário de justificativa
  → Mostra júris onde está alocado
  → Upload de documento (opcional)
  → Cria solicitação pendente
  → Aguarda aprovação do coordenador
```

### **3. Recandidatura** ✅
**Quando:** Candidatura foi cancelada ou rejeitada
**Botão:** "Recandidatar-se" (azul primário)
**Fluxo:**
```php
POST /applications/{id}/reapply
  ↓
Verifica:
  - Status é 'cancelada' ou 'rejeitada'
  - Vaga ainda está 'aberta'
  - Perfil está completo
  ↓
Se OK:
  → Status volta para 'pendente'
  → applied_at = agora
  → reviewed_at = null
  → reviewed_by = null
  ↓
Mensagem: "Candidatura reenviada! Aguarde aprovação"
```

**Validações da Recandidatura:**
1. ✅ Vaga deve estar aberta
2. ✅ Perfil deve estar completo
3. ✅ Apenas candidaturas canceladas/rejeitadas
4. ✅ Vigilante deve ser dono da candidatura

---

## 🎨 Interface do Usuário

### **Página: `/availability`**

#### **Seção "Minhas Candidaturas"**

**Candidatura PENDENTE:**
```
┌────────────────────────────────────┐
│ 📋 Exames 2025                     │
│ 📅 Candidatou-se: 10/10/2025       │
│ ⏰ Prazo: 15/10/2025               │
│ [Pendente 🟡] [Cancelar]           │
└────────────────────────────────────┘
```

**Candidatura APROVADA:**
```
┌────────────────────────────────────┐
│ 📋 Exames 2025         ✅          │
│ 📅 Candidatou-se: 10/10/2025       │
│ ⏰ Prazo: 15/10/2025               │
│ [Aprovada 🟢] [Cancelar]           │
└────────────────────────────────────┘
```

**Candidatura CANCELADA:**
```
┌────────────────────────────────────┐
│ 📋 Exames 2025                     │
│ 📅 Candidatou-se: 10/10/2025       │
│ ⏰ Prazo: 15/10/2025               │
│ [Cancelada ⚫] [Recandidatar-se]   │
└────────────────────────────────────┘
```

**Candidatura REJEITADA:**
```
┌────────────────────────────────────┐
│ 📋 Exames 2025                     │
│ 📅 Candidatou-se: 10/10/2025       │
│ ⏰ Prazo: 15/10/2025               │
│ [Rejeitada 🔴] [Recandidatar-se]   │
└────────────────────────────────────┘
```

---

## 📂 Arquivos Modificados

### **Controller:**
✅ `app/Controllers/AvailabilityController.php`

**Novos Métodos:**
```php
public function cancelDirect(Request $request)
{
    // Cancela candidatura pendente (sem justificativa)
    // Status: pendente → cancelada
}

public function reapply(Request $request)
{
    // Reativa candidatura cancelada/rejeitada
    // Status: cancelada/rejeitada → pendente
    // Validações:
    //   - Vaga aberta
    //   - Perfil completo
}
```

### **View:**
✅ `app/Views/availability/index.php`

**Alterações na Seção "Minhas Candidaturas":**
```php
// Pendente: botão "Cancelar" (cinza)
// Aprovada: botão "Cancelar" (vermelho)
// Cancelada/Rejeitada: botão "Recandidatar-se" (azul)
```

### **Rotas:**
✅ `app/Routes/web.php`

```php
POST /applications/{id}/cancel-direct  // Cancelar pendente
POST /applications/{id}/reapply        // Recandidatar-se
```

---

## 🧪 Como Testar

### **Teste 1: Cancelar Candidatura Pendente**
1. Login como **vigilante**
2. Candidate-se a uma vaga
3. ✅ Status: **Pendente**
4. Na seção "Minhas Candidaturas", clique **"Cancelar"**
5. ✅ Confirmação aparece
6. Confirme
7. ✅ Status muda para **Cancelada** (imediato, sem justificativa)
8. ✅ Mensagem: "Candidatura cancelada com sucesso"

### **Teste 2: Cancelar Candidatura Aprovada (Sem Alocação)**
1. Tenha candidatura **aprovada** pelo coordenador
2. **NÃO esteja** alocado a júris
3. Clique **"Cancelar"**
4. ✅ Cancelamento imediato (sem justificativa)
5. ✅ Status: **Cancelada**

### **Teste 3: Cancelar Candidatura Aprovada (Com Alocação)**
1. Tenha candidatura **aprovada**
2. **SEJA alocado** a 1+ júris (via planning)
3. Clique **"Cancelar"**
4. ✅ Abre formulário de justificativa
5. ✅ Mostra júris alocados
6. Preencha justificativa (20+ caracteres)
7. (Opcional) Anexe documento
8. Envie
9. ✅ Solicitação criada (pendente)
10. ✅ Aguarda aprovação do coordenador

### **Teste 4: Recandidatar-se (Cancelada)**
1. Tenha candidatura **cancelada**
2. Vaga ainda está **aberta**
3. Perfil está **completo**
4. Veja botão **"Recandidatar-se"** (azul)
5. Clique
6. ✅ Confirmação: "Deseja recandidatar-se a esta vaga?"
7. Confirme
8. ✅ Status volta para **Pendente**
9. ✅ Mensagem: "Candidatura reenviada! Aguarde aprovação"
10. ✅ Aparece novamente para o coordenador aprovar

### **Teste 5: Recandidatar-se (Rejeitada)**
1. Tenha candidatura **rejeitada** pelo coordenador
2. Vaga ainda **aberta**
3. Clique **"Recandidatar-se"**
4. ✅ Status volta para **Pendente**
5. ✅ Nova chance de aprovação

### **Teste 6: Recandidatura - Vaga Fechada**
1. Candidatura cancelada
2. Coordenador **fecha a vaga**
3. Tente recandidatar-se
4. ✅ Erro: "Esta vaga não está mais aberta para candidaturas"

### **Teste 7: Recandidatura - Perfil Incompleto**
1. Candidatura cancelada
2. Remova dados do perfil (ex: telefone)
3. Tente recandidatar-se
4. ✅ Erro: "Complete seu perfil antes de se candidatar"
5. ✅ Redireciona para `/profile`

---

## 📊 Casos de Uso Reais

### **Caso 1: Vigilante Mudou de Ideia (Pendente)**
**Situação:** João se candidatou mas desistiu antes da aprovação.

**Fluxo:**
1. João vê candidatura **pendente**
2. Clica **"Cancelar"**
3. ✅ Cancelada instantaneamente
4. Coordenador não vê mais a candidatura dele

### **Caso 2: Vigilante Precisa Cancelar (Aprovado, Sem Júri)**
**Situação:** Maria foi aprovada mas surgiu imprevisto.

**Fluxo:**
1. Maria foi **aprovada**
2. Ainda não foi **alocada** a júris
3. Clica **"Cancelar"**
4. ✅ Cancelamento direto (sem burocracia)

### **Caso 3: Vigilante Precisa Cancelar (Aprovado, Com Júri)**
**Situação:** Pedro foi aprovado e alocado, mas ficou doente.

**Fluxo:**
1. Pedro **aprovado** e **alocado** em 3 júris
2. Clica **"Cancelar"**
3. ✅ Sistema exige justificativa
4. Pedro escreve: "Atestado médico - cirurgia"
5. Anexa PDF do atestado
6. Envia solicitação
7. Coordenador revisa e aprova
8. ✅ Pedro desalocado dos júris

### **Caso 4: Vigilante Foi Rejeitado mas Corrigiu Perfil**
**Situação:** Ana foi rejeitada por perfil incompleto.

**Fluxo:**
1. Ana vê candidatura **rejeitada**
2. Completa perfil (adiciona NIB, NUIT, etc)
3. Volta para página de disponibilidade
4. Clica **"Recandidatar-se"**
5. ✅ Status volta para **pendente**
6. Coordenador vê novamente
7. Perfil agora completo → **aprova**

### **Caso 5: Vigilante Cancelou por Engano**
**Situação:** Carlos cancelou candidatura sem querer.

**Fluxo:**
1. Carlos vê candidatura **cancelada**
2. Vaga ainda **aberta**
3. Clica **"Recandidatar-se"**
4. ✅ Status volta para **pendente**
5. Aguarda nova aprovação
6. Coordenador aprova novamente

---

## 🔐 Validações de Segurança

### **Cancelamento Direto:**
1. ✅ Apenas vigilante dono da candidatura
2. ✅ Apenas status 'pendente'
3. ✅ CSRF token obrigatório

### **Cancelamento com Justificativa:**
1. ✅ Apenas vigilante dono
2. ✅ Apenas status 'aprovada'
3. ✅ Verifica alocação a júris
4. ✅ Justificativa mínimo 20 caracteres (se alocado)
5. ✅ Upload validado (tipo e tamanho)

### **Recandidatura:**
1. ✅ Apenas vigilante dono
2. ✅ Apenas status 'cancelada' ou 'rejeitada'
3. ✅ Vaga deve estar 'aberta'
4. ✅ Perfil deve estar completo
5. ✅ CSRF token obrigatório

---

## 📝 Logs de Atividade

### **Cancelamento Direto (Pendente):**
```sql
SELECT * FROM activity_log 
WHERE entity = 'vacancy_applications' 
  AND action = 'cancel_direct';
```

### **Recandidatura:**
```sql
SELECT * FROM activity_log 
WHERE entity = 'vacancy_applications' 
  AND action = 'reapply';

-- Metadata inclui:
{
    "vacancy_id": 1,
    "vigilante_id": 5,
    "previous_status": "cancelada" // ou "rejeitada"
}
```

---

## 🔄 Ciclo de Vida Completo de uma Candidatura

```
1. CRIAÇÃO
   Vigilante clica "Candidatar-me"
   ↓
   Status: PENDENTE
   ↓

2. REVISÃO DO COORDENADOR
   Coordenador aprova ou rejeita
   ↓
   Status: APROVADA ou REJEITADA
   ↓

3. POSSÍVEIS CAMINHOS:

   3A. APROVADA → Vigilante alocado a júris
       ↓
       Vigilante pode cancelar (com justificativa)
       ↓
       Status: PENDENTE (solicitação)
       ↓
       Coordenador aprova cancelamento
       ↓
       Status: CANCELADA

   3B. APROVADA → Vigilante cancela (sem alocação)
       ↓
       Status: CANCELADA (direto)

   3C. REJEITADA → Vigilante corrige perfil
       ↓
       Clica "Recandidatar-se"
       ↓
       Status: PENDENTE (nova chance)

   3D. CANCELADA → Vigilante muda de ideia
       ↓
       Clica "Recandidatar-se"
       ↓
       Status: PENDENTE (nova chance)
```

---

## 📊 Estatísticas Úteis

### **Contagem por Status:**
```php
$model = new VacancyApplication();
$myApps = $model->getByVigilante($vigilanteId);

$stats = [
    'pendente' => 0,
    'aprovada' => 0,
    'rejeitada' => 0,
    'cancelada' => 0,
];

foreach ($myApps as $app) {
    $stats[$app['status']]++;
}

// Exemplo:
// pendente: 2
// aprovada: 3
// rejeitada: 1
// cancelada: 1
```

### **Taxa de Recandidatura:**
```sql
SELECT 
    COUNT(CASE WHEN action = 'reapply' THEN 1 END) as recandidaturas,
    COUNT(CASE WHEN action = 'cancel_direct' THEN 1 END) as cancelamentos
FROM activity_log 
WHERE entity = 'vacancy_applications' 
  AND user_id = :vigilante_id;
```

---

## 🚧 Melhorias Futuras (v2.5)

### **1. Histórico de Status**
- [ ] Tabela `application_status_history`
- [ ] Rastrear todas as mudanças de status
- [ ] Ver timeline completa da candidatura

### **2. Limite de Recandidaturas**
- [ ] Máximo 3 recandidaturas por vaga
- [ ] Evita spam de candidaturas

### **3. Prazo para Recandidatura**
- [ ] Só pode recandidatar após X dias
- [ ] Evita cancelar/recandidatar imediatamente

### **4. Notificações**
- [ ] Email quando candidatura for cancelada
- [ ] Email quando recandidatura for aprovada
- [ ] Notificação ao coordenador (nova recandidatura)

### **5. Motivo de Rejeição**
- [ ] Coordenador pode escrever motivo ao rejeitar
- [ ] Vigilante vê motivo e pode corrigir
- [ ] Melhora comunicação

---

## ✅ Checklist de Implementação

### **Backend:**
- [x] Método `cancelDirect()` - cancelar pendente
- [x] Método `reapply()` - recandidatar
- [x] Validações de status
- [x] Validações de vaga aberta
- [x] Validações de perfil completo
- [x] Logs de atividade

### **Frontend:**
- [x] Botão "Cancelar" (pendente - cinza)
- [x] Botão "Cancelar" (aprovada - vermelho)
- [x] Botão "Recandidatar-se" (azul)
- [x] Confirmações JavaScript
- [x] Cores por status

### **Rotas:**
- [x] POST `/applications/{id}/cancel-direct`
- [x] POST `/applications/{id}/reapply`

### **Validações:**
- [x] CSRF tokens
- [x] Ownership (vigilante dono)
- [x] Status válido
- [x] Vaga aberta
- [x] Perfil completo

---

## 🎉 Status Final

**Implementação**: ✅ **Concluída (100%)**

### **Funcional:**
- ✅ Cancelar candidatura pendente (direto)
- ✅ Cancelar candidatura aprovada (com/sem justificativa)
- ✅ Recandidatar-se (cancelada ou rejeitada)
- ✅ Validações completas
- ✅ Logs de auditoria
- ✅ Interface intuitiva
- ✅ Confirmações de segurança

### **Próxima Fase:**
- ⏳ Histórico de mudanças
- ⏳ Limites de recandidatura
- ⏳ Notificações por email
- ⏳ Motivos de rejeição visíveis

---

**🚀 Vigilantes agora têm controle total sobre suas candidaturas!**

Sistema completo com 3 ações principais:
1. **Candidatar-se** a vagas abertas
2. **Cancelar** candidatura (com justificativa se necessário)
3. **Recandidatar-se** após cancelamento/rejeição

Tudo com validações robustas e fluxos transparentes!
