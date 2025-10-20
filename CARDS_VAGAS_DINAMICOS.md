# 🎨 Cards de Vagas Dinâmicos - Atualização em Tempo Real

**Data**: 11/10/2025  
**Versão**: 2.7.1  
**Status**: ✅ Implementado

---

## 🎯 Objetivo

Fazer com que os cards das "Vagas Abertas" reflitam **automaticamente** o status da candidatura do vigilante, atualizando cores, badges e ações disponíveis.

---

## ✨ O Que Foi Implementado

### **1. Cores Dinâmicas dos Cards**

Os cards agora mudam de cor baseado no status da candidatura:

```
┌─────────────────────────────────────────┐
│  Status: APROVADA                       │
│  Cor: Verde (border-green-400)          │
│  Fundo: bg-green-50                     │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Status: PENDENTE                       │
│  Cor: Amarelo (border-yellow-400)       │
│  Fundo: bg-yellow-50                    │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Status: REJEITADA                      │
│  Cor: Vermelho (border-red-300)         │
│  Fundo: bg-red-50                       │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Status: CANCELADA                      │
│  Cor: Cinza (border-gray-300)           │
│  Fundo: bg-gray-50                      │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Status: SEM CANDIDATURA                │
│  Cor: Padrão (border-gray-200)          │
│  Fundo: Branco                          │
└─────────────────────────────────────────┘
```

---

### **2. Badges de Status**

Cada card mostra um badge visual com o status:

```
✅ APROVADO    - Verde  (bg-green-200 text-green-800)
⏳ PENDENTE    - Amarelo (bg-yellow-200 text-yellow-800)
❌ REJEITADO   - Vermelho (bg-red-200 text-red-800)
🚫 CANCELADO   - Cinza   (bg-gray-300 text-gray-700)
```

---

### **3. Ações Contextuais**

As ações disponíveis mudam automaticamente baseado no status:

#### **3.1. Candidatura APROVADA** ✅
```
┌──────────────────────────────────────┐
│ ✅ Candidatura Aprovada [APROVADO]  │
│                                      │
│ (Sem ações - já foi aprovado)       │
└──────────────────────────────────────┘
```

#### **3.2. Candidatura PENDENTE** ⏳
```
┌──────────────────────────────────────┐
│ ⏳ Aguardando Aprovação [PENDENTE]  │
│                                      │
│ [Cancelar Candidatura]               │
└──────────────────────────────────────┘
```
**Ação:** Pode cancelar a candidatura

#### **3.3. Candidatura REJEITADA** ❌
```
┌──────────────────────────────────────┐
│ ❌ Candidatura Rejeitada [REJEITADO]│
│                                      │
│ [Recandidatar-me]                    │
└──────────────────────────────────────┘
```
**Ação:** Pode recandidatar-se (se prazo não expirou)

#### **3.4. Candidatura CANCELADA** 🚫
```
┌──────────────────────────────────────┐
│ 🚫 Candidatura Cancelada [CANCELADO]│
│                                      │
│ [Recandidatar-me]                    │
└──────────────────────────────────────┘
```
**Ação:** Pode recandidatar-se (se prazo não expirou)

#### **3.5. SEM CANDIDATURA** 🆕
```
┌──────────────────────────────────────┐
│ Vaga X - Descrição...                │
│                                      │
│ [➕ Candidatar-me]                   │
└──────────────────────────────────────┘
```
**Ação:** Pode candidatar-se pela primeira vez

#### **3.6. PRAZO EXPIRADO** ⏰
```
┌──────────────────────────────────────┐
│ Vaga X - Descrição...                │
│                                      │
│ ⏰ Prazo Encerrado                   │
└──────────────────────────────────────┘
```
**Ação:** Nenhuma (prazo encerrado)

#### **3.7. PERFIL INCOMPLETO** 🔒
```
┌──────────────────────────────────────┐
│ Vaga X - Descrição...                │
│                                      │
│ [🔒 Complete o Perfil]               │
└──────────────────────────────────────┘
```
**Ação:** Precisa completar perfil primeiro

---

## 🔄 Fluxo de Atualização

### **Cenário 1: Candidatura → Aprovação**
```
1. Vigilante se candidata
   → Card fica AMARELO
   → Badge: PENDENTE
   → Botão: "Cancelar Candidatura"

2. Coordenador aprova
   → Card fica VERDE
   → Badge: APROVADO
   → Sem botões (já aprovado)
```

### **Cenário 2: Candidatura → Rejeição → Recandidatura**
```
1. Vigilante se candidata
   → Card: AMARELO (PENDENTE)

2. Coordenador rejeita
   → Card: VERMELHO (REJEITADO)
   → Botão: "Recandidatar-me"

3. Vigilante se recandidata
   → Card: AMARELO (PENDENTE novamente)
   → Botão: "Cancelar Candidatura"
```

### **Cenário 3: Candidatura → Cancelamento → Recandidatura**
```
1. Vigilante se candidata
   → Card: AMARELO (PENDENTE)

2. Vigilante cancela
   → Card: CINZA (CANCELADO)
   → Botão: "Recandidatar-me"

3. Vigilante se recandidata
   → Card: AMARELO (PENDENTE novamente)
```

---

## 💻 Código Implementado

### **1. Detectar Status da Candidatura**
```php
// Buscar candidatura do vigilante para esta vaga
$myApplication = null;
foreach ($myApplications as $app) {
    if ($app['vacancy_id'] == $vacancy['id']) {
        $myApplication = $app;
        break;
    }
}
```

### **2. Definir Cor do Card**
```php
$cardClass = 'border-gray-200';  // Padrão

if ($myApplication) {
    if ($myApplication['status'] === 'aprovada') {
        $cardClass = 'border-green-400 bg-green-50';
    } elseif ($myApplication['status'] === 'pendente') {
        $cardClass = 'border-yellow-400 bg-yellow-50';
    } elseif ($myApplication['status'] === 'rejeitada') {
        $cardClass = 'border-red-300 bg-red-50';
    } elseif ($myApplication['status'] === 'cancelada') {
        $cardClass = 'border-gray-300 bg-gray-50';
    }
}
```

### **3. Renderizar Card**
```php
<div class="border-2 <?= $cardClass ?> rounded-lg p-5">
    <!-- Conteúdo da vaga -->
    
    <?php if ($myApplication): ?>
        <!-- Mostrar status e ações baseadas no status -->
    <?php else: ?>
        <!-- Mostrar botão "Candidatar-me" -->
    <?php endif; ?>
</div>
```

---

## 🎨 Visual dos Cards

### **Card APROVADO:**
```
┌─────────────────────────────────────────┐
│  [Verde Claro]                          │
│  Vaga de Vigilante - Exame de Física    │
│  Descrição da vaga...                   │
│  📅 Prazo: 20/10/2025 23:59            │
│                                         │
│  ✅ Candidatura Aprovada   [APROVADO]  │
└─────────────────────────────────────────┘
```

### **Card PENDENTE:**
```
┌─────────────────────────────────────────┐
│  [Amarelo Claro]                        │
│  Vaga de Vigilante - Exame de Química   │
│  Descrição da vaga...                   │
│  📅 Prazo: 25/10/2025 23:59            │
│                                         │
│  ⏳ Aguardando Aprovação   [PENDENTE]  │
│  [Cancelar Candidatura]                 │
└─────────────────────────────────────────┘
```

### **Card REJEITADO:**
```
┌─────────────────────────────────────────┐
│  [Vermelho Claro]                       │
│  Vaga de Vigilante - Exame de Biologia  │
│  Descrição da vaga...                   │
│  📅 Prazo: 30/10/2025 23:59            │
│                                         │
│  ❌ Candidatura Rejeitada  [REJEITADO] │
│  [Recandidatar-me]                      │
└─────────────────────────────────────────┘
```

### **Card SEM CANDIDATURA:**
```
┌─────────────────────────────────────────┐
│  [Cinza/Branco]                         │
│  Vaga de Vigilante - Exame de História  │
│  Descrição da vaga...                   │
│  📅 Prazo: 15/10/2025 23:59            │
│                                         │
│  [➕ Candidatar-me]                     │
└─────────────────────────────────────────┘
```

---

## ✅ Vantagens

### **1. Feedback Visual Imediato** 👁️
```
Vigilante vê de relance o status de TODAS as vagas:
- Verde = Aprovado
- Amarelo = Aguardando
- Vermelho = Rejeitado
- Cinza = Cancelado
```

### **2. Ações Contextuais** 🎯
```
Botões mudam automaticamente:
- Pendente → "Cancelar"
- Rejeitado/Cancelado → "Recandidatar-me"
- Sem candidatura → "Candidatar-me"
```

### **3. Experiência Intuitiva** 💡
```
Não precisa entrar em "Minhas Candidaturas"
para ver o status de cada vaga.
```

### **4. Atualização Automática** 🔄
```
Qualquer ação (candidatar, cancelar, aprovar, rejeitar)
reflete IMEDIATAMENTE nos cards.
```

---

## 🧪 Como Testar

### **Teste 1: Candidatura Nova**
1. Acesse `/availability` como vigilante
2. Veja uma vaga SEM candidatura
3. ✅ Card cinza/branco
4. ✅ Botão "Candidatar-me"
5. Clique "Candidatar-me"
6. ✅ Card fica AMARELO
7. ✅ Badge "PENDENTE"
8. ✅ Botão muda para "Cancelar Candidatura"

### **Teste 2: Aprovação**
1. Coordenador aprova a candidatura
2. Vigilante recarrega a página
3. ✅ Card fica VERDE
4. ✅ Badge "APROVADO"
5. ✅ Sem botões de ação

### **Teste 3: Rejeição → Recandidatura**
1. Coordenador rejeita candidatura
2. Vigilante recarrega
3. ✅ Card fica VERMELHO
4. ✅ Badge "REJEITADO"
5. ✅ Botão "Recandidatar-me"
6. Vigilante clica "Recandidatar-me"
7. ✅ Card volta para AMARELO (PENDENTE)

### **Teste 4: Cancelamento → Recandidatura**
1. Vigilante cancela candidatura pendente
2. ✅ Card fica CINZA
3. ✅ Badge "CANCELADO"
4. ✅ Botão "Recandidatar-me"
5. Vigilante se recandidata
6. ✅ Card volta para AMARELO

---

## 📊 Resumo de Estados

| Status | Cor Card | Badge | Ação Disponível |
|--------|----------|-------|-----------------|
| **Sem Candidatura** | Cinza/Branco | - | Candidatar-me |
| **Pendente** | Amarelo | PENDENTE | Cancelar Candidatura |
| **Aprovada** | Verde | APROVADO | (Nenhuma) |
| **Rejeitada** | Vermelho | REJEITADO | Recandidatar-me |
| **Cancelada** | Cinza | CANCELADO | Recandidatar-me |
| **Prazo Expirado** | Padrão | - | (Nenhuma) |
| **Perfil Incompleto** | Padrão | - | Complete o Perfil |

---

## 🎉 Status Final

**✅ IMPLEMENTAÇÃO COMPLETA!**

Os cards de "Vagas Abertas" agora são **dinâmicos e reativos**:
- ✅ Cores mudam automaticamente
- ✅ Badges mostram status claro
- ✅ Ações contextuais baseadas no estado
- ✅ Feedback visual imediato
- ✅ Experiência intuitiva

**Teste agora e veja os cards atualizarem em tempo real!** 🚀
