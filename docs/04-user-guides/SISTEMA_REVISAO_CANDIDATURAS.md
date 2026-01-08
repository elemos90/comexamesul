# 👨‍💼 Sistema de Revisão de Candidaturas - v2.4

**Data**: 11/10/2025  
**Versão**: 2.4  
**Status**: ✅ Implementado

---

## 🎯 Objetivo

Permitir que **coordenadores e membros** revisem, aprovem ou rejeitem candidaturas de vigilantes às vagas publicadas, com opções de aprovação/rejeição individual ou em massa.

---

## 🔄 Fluxo Completo: Vigilante → Coordenador

### **1. Vigilante Candidata-se**
```
Vigilante vê vaga aberta → Clica "Candidatar-me"
  ↓
Sistema cria registro com status = 'pendente'
  ↓
Vigilante aguarda aprovação
```

### **2. Coordenador Revisa**
```
Coordenador acessa "Candidaturas"
  ↓
Seleciona vaga no dropdown
  ↓
Vê lista de candidaturas pendentes
  ↓
Opções:
  - Aprovar uma por uma
  - Rejeitar uma por uma
  - Aprovar TODAS de uma vez
  - Rejeitar TODAS de uma vez
```

### **3. Após Aprovação**
```
Status muda para 'aprovada'
  ↓
Vigilante pode ser alocado a júris
  ↓
Aparece na lista de vigilantes disponíveis (planning)
```

---

## 📊 Interface do Coordenador

### **Página: `/applications`**

#### **1. Seletor de Vaga**
```
┌─────────────────────────────────┐
│ Selecione uma Vaga:             │
│ [Dropdown: Exames 2025 ▼]       │
└─────────────────────────────────┘
```

#### **2. Estatísticas (Cards)**
```
┌────────┬────────┬────────┬────────┐
│ Total  │Pendente│Aprovada│Rejeitada│
│   15   │   5    │   8    │   2    │
└────────┴────────┴────────┴────────┘
```

#### **3. Ações em Massa (Se houver pendentes)**
```
⚠️ Ações em Massa
   [✓ Aprovar Todas (5)]  [✗ Rejeitar Todas]
```

#### **4. Lista de Candidaturas**
```
┌─────────────────────────────────────────┐
│ 👤 João Silva                           │
│    📧 joao@email.com                    │
│    📅 Candidatou-se: 10/10/2025 14:30   │
│    [Pendente] [✓ Aprovar] [✗ Rejeitar] │
├─────────────────────────────────────────┤
│ 👤 Maria Santos                         │
│    📧 maria@email.com                   │
│    🎓 Elegível Supervisor               │
│    [Aprovada] Revisado por Pedro em ... │
└─────────────────────────────────────────┘
```

---

## 🛠️ Funcionalidades Implementadas

### **1. Filtro por Vaga** ✅
- Dropdown com todas as vagas
- Ao selecionar, carrega candidaturas automaticamente

### **2. Estatísticas em Tempo Real** ✅
- Total de candidaturas
- Pendentes (amarelo)
- Aprovadas (verde)
- Rejeitadas (vermelho)

### **3. Aprovação Individual** ✅
**Botão:** "✓ Aprovar"
- Muda status para `aprovada`
- Registra quem aprovou e quando
- Log de atividade

### **4. Rejeição Individual** ✅
**Botão:** "✗ Rejeitar"
- Muda status para `rejeitada`
- Confirmação antes de rejeitar
- Registra quem rejeitou e quando

### **5. Aprovação em Massa** ✅
**Botão:** "✓ Aprovar Todas (N)"
- Aprova TODAS as candidaturas pendentes de uma vaga
- Confirmação obrigatória
- Mensagem: "Todas as X candidaturas foram aprovadas"

### **6. Rejeição em Massa** ✅
**Botão:** "✗ Rejeitar Todas"
- Rejeita TODAS as candidaturas pendentes
- Confirmação obrigatória
- Útil para encerrar vagas rapidamente

---

## 📂 Arquivos Criados/Modificados

### **Criados:**
1. ✅ `app/Controllers/ApplicationReviewController.php`
   - `index()` - Listar candidaturas
   - `approve()` - Aprovar individual
   - `reject()` - Rejeitar individual
   - `approveAll()` - Aprovar todas
   - `rejectAll()` - Rejeitar todas

2. ✅ `app/Views/applications/index.php`
   - Interface completa de revisão

### **Modificados:**
1. ✅ `app/Routes/web.php`
   - Rotas de candidaturas
2. ✅ `app/Views/partials/sidebar.php`
   - Novo item "Candidaturas" no menu

---

## 🔌 Rotas Implementadas

```php
GET  /applications                 // Listar candidaturas
POST /applications/{id}/approve    // Aprovar individual
POST /applications/{id}/reject     // Rejeitar individual
POST /applications/approve-all     // Aprovar todas
POST /applications/reject-all      // Rejeitar todas
```

---

## 🗄️ Estrutura de Dados

### **Tabela: `vacancy_applications`**

**Campos relevantes:**
```sql
status ENUM('pendente','aprovada','rejeitada','cancelada')
reviewed_at DATETIME NULL           -- Quando foi revisada
reviewed_by INT NULL                -- ID do coordenador que revisou
```

**Antes da revisão:**
```json
{
  "status": "pendente",
  "reviewed_at": null,
  "reviewed_by": null
}
```

**Após aprovação:**
```json
{
  "status": "aprovada",
  "reviewed_at": "2025-10-11 18:30:00",
  "reviewed_by": 1
}
```

---

## 🧪 Como Testar

### **Teste 1: Visualizar Candidaturas**
1. Login como **coordenador**
2. Menu: **Candidaturas**
3. Selecione vaga no dropdown
4. ✅ Deve mostrar:
   - Estatísticas
   - Lista de candidaturas
   - Botões de ação

### **Teste 2: Aprovar Individual**
1. Na lista, encontre candidatura **pendente**
2. Clique **"✓ Aprovar"**
3. ✅ Status muda para **Aprovada** (verde)
4. ✅ Mostra quem aprovou e quando
5. ✅ Botões de ação desaparecem

### **Teste 3: Rejeitar Individual**
1. Encontre candidatura **pendente**
2. Clique **"✗ Rejeitar"**
3. ✅ Confirmação aparece
4. Confirme
5. ✅ Status muda para **Rejeitada** (vermelho)

### **Teste 4: Aprovar Todas**
1. Tenha 3+ candidaturas **pendentes**
2. Veja seção "Ações em Massa"
3. Clique **"✓ Aprovar Todas (X)"**
4. ✅ Confirmação: "Deseja aprovar TODAS as X candidaturas?"
5. Confirme
6. ✅ Todas mudam para **Aprovada**
7. ✅ Mensagem: "Todas as X candidaturas foram aprovadas"

### **Teste 5: Rejeitar Todas**
1. Tenha candidaturas pendentes
2. Clique **"✗ Rejeitar Todas"**
3. Confirme
4. ✅ Todas rejeitadas

### **Teste 6: Sem Candidaturas**
1. Selecione vaga sem candidaturas
2. ✅ Mostra mensagem:
   - Ícone cinza
   - "Nenhuma candidatura ainda"
   - "Vigilantes ainda não se candidataram"

### **Teste 7: Filtro por Vaga**
1. Crie 2 vagas diferentes
2. Candidatos se candidatam a ambas
3. Selecione vaga 1
4. ✅ Mostra apenas candidaturas da vaga 1
5. Mude para vaga 2
6. ✅ Mostra apenas candidaturas da vaga 2

---

## 🔐 Permissões

### **Acesso à Interface:**
- ✅ **Coordenador** - acesso total
- ✅ **Membro** - acesso total
- ❌ **Vigilante** - sem acesso

### **Ações Permitidas:**
| Ação | Coordenador | Membro | Vigilante |
|------|-------------|--------|-----------|
| Ver candidaturas | ✅ | ✅ | ❌ |
| Aprovar individual | ✅ | ✅ | ❌ |
| Rejeitar individual | ✅ | ✅ | ❌ |
| Aprovar todas | ✅ | ✅ | ❌ |
| Rejeitar todas | ✅ | ✅ | ❌ |

---

## 📊 Casos de Uso Completos

### **Caso 1: Aprovação Normal**
**Cenário:** Coordenador revisa 10 candidaturas.

**Fluxo:**
1. Coordenador seleciona vaga "Exames 2025"
2. Vê 10 candidaturas pendentes
3. Revisa perfil de cada candidato
4. Aprova 8 individualmente
5. Rejeita 2 por perfil incompleto
6. ✅ Resultado: 8 aprovadas, 2 rejeitadas

### **Caso 2: Aprovação Rápida (Massa)**
**Cenário:** Vaga com prazo curto, todos os candidatos são qualificados.

**Fluxo:**
1. Coordenador vê 20 candidaturas pendentes
2. Sabe que todos passaram validação de perfil
3. Clica **"Aprovar Todas (20)"**
4. Confirma
5. ✅ 20 candidaturas aprovadas em 1 clique

### **Caso 3: Encerramento de Vaga**
**Cenário:** Vaga foi fechada, candidaturas tardias devem ser rejeitadas.

**Fluxo:**
1. Vaga fechada há 2 dias
2. 5 vigilantes se candidataram após o prazo
3. Coordenador clica **"Rejeitar Todas"**
4. ✅ 5 candidaturas rejeitadas

### **Caso 4: Revisão Parcial**
**Cenário:** Coordenador precisa pausar revisão.

**Fluxo:**
1. Coordenador aprova 5 de 15 candidaturas
2. Precisa sair
3. Volta depois
4. ✅ Vê 10 pendentes restantes
5. ✅ 5 aprovadas ainda marcadas como aprovadas

---

## 🎨 Design da Interface

### **Cores por Status:**
| Status | Badge | Background |
|--------|-------|------------|
| Pendente | Amarelo | Amarelo claro (destaque) |
| Aprovada | Verde | Branco |
| Rejeitada | Vermelho | Branco |
| Cancelada | Cinza | Branco |

### **Ícones:**
- 📊 Estatísticas com ícones coloridos
- 👤 Avatar com iniciais do vigilante
- 📧 Email
- 📞 Telefone
- 📅 Data de candidatura
- 🎓 Badge "Elegível Supervisor"

---

## 📝 Logs de Atividade

Todas as ações são registradas em `activity_log`:

### **Aprovação Individual:**
```sql
entity: 'vacancy_applications'
action: 'approve'
metadata: {
    vacancy_id: 1,
    vigilante_id: 5
}
```

### **Aprovação em Massa:**
```sql
entity: 'vacancy_applications'
action: 'approve_bulk'
metadata: {
    vacancy_id: 1,
    vigilante_id: 5
}
```

### **Rejeição:**
```sql
entity: 'vacancy_applications'
action: 'reject' ou 'reject_bulk'
```

### **Consultar Logs:**
```sql
SELECT * FROM activity_log 
WHERE entity = 'vacancy_applications' 
  AND action IN ('approve', 'reject', 'approve_bulk', 'reject_bulk')
ORDER BY created_at DESC;
```

---

## 🔄 Integração com Outros Sistemas

### **1. Planning de Júris**
```
Candidatura APROVADA → Vigilante disponível para alocação
```

No planning (`/juries/planning`), apenas vigilantes com candidaturas **aprovadas** aparecem para drag & drop.

### **2. Disponibilidade**
```
Vigilante vê suas candidaturas em /availability
  ↓
Status atualizado automaticamente:
  - Pendente (amarelo)
  - Aprovada (verde)
  - Rejeitada (vermelho)
```

### **3. Dashboard (Futuro)**
Estatísticas de candidaturas no dashboard do coordenador:
- Total de pendentes (todas as vagas)
- Vagas com mais candidaturas
- Taxa de aprovação

---

## 📈 Estatísticas Úteis

### **Por Vaga:**
```php
$model = new VacancyApplication();
$stats = $model->countByStatus($vacancyId);

// Retorna:
[
    'pendente' => 5,
    'aprovada' => 10,
    'rejeitada' => 2,
    'cancelada' => 1
]
```

### **Taxa de Aprovação:**
```php
$total = $stats['aprovada'] + $stats['rejeitada'];
$taxa = $total > 0 ? ($stats['aprovada'] / $total) * 100 : 0;
// Ex: 83.33% de aprovação
```

---

## 🚧 Melhorias Futuras (v2.5)

### **1. Notificações por Email**
- [ ] Email ao vigilante quando candidatura for aprovada
- [ ] Email ao vigilante quando candidatura for rejeitada
- [ ] Email ao coordenador quando nova candidatura chegar

### **2. Comentários/Notas**
- [ ] Coordenador pode adicionar nota ao aprovar/rejeitar
- [ ] Vigilante pode ver motivo da rejeição

### **3. Filtros Avançados**
- [ ] Filtrar por status (pendente, aprovada, rejeitada)
- [ ] Filtrar por data de candidatura
- [ ] Buscar por nome do vigilante

### **4. Exportação**
- [ ] Exportar lista de candidaturas (CSV/PDF)
- [ ] Relatório de aprovação por vaga

### **5. Histórico de Revisão**
- [ ] Ver histórico de mudanças de status
- [ ] Quem aprovou/rejeitou e quando

---

## ✅ Checklist de Implementação

### **Backend:**
- [x] Controller `ApplicationReviewController`
- [x] Método `index()` - listar
- [x] Método `approve()` - aprovar individual
- [x] Método `reject()` - rejeitar individual
- [x] Método `approveAll()` - aprovar todas
- [x] Método `rejectAll()` - rejeitar todas
- [x] Validações de permissão
- [x] Logs de atividade

### **Frontend:**
- [x] View `applications/index.php`
- [x] Seletor de vaga (dropdown)
- [x] Cards de estatísticas
- [x] Seção de ações em massa
- [x] Lista de candidaturas
- [x] Botões de ação individual
- [x] Confirmações JavaScript
- [x] Estados vazios

### **Rotas:**
- [x] GET `/applications`
- [x] POST `/applications/{id}/approve`
- [x] POST `/applications/{id}/reject`
- [x] POST `/applications/approve-all`
- [x] POST `/applications/reject-all`

### **Menu:**
- [x] Item "Candidaturas" no sidebar
- [x] Visível para coordenador e membro
- [x] Oculto para vigilante

---

## 🎉 Status Final

**Implementação**: ✅ **Concluída (100%)**

### **Funcional:**
- ✅ Coordenador pode ver todas as candidaturas
- ✅ Filtro por vaga funcionando
- ✅ Estatísticas em tempo real
- ✅ Aprovação individual
- ✅ Rejeição individual
- ✅ Aprovação em massa (todas de uma vez)
- ✅ Rejeição em massa
- ✅ Logs de auditoria
- ✅ Interface intuitiva
- ✅ Confirmações antes de ações destrutivas

### **Próxima Fase (v2.5):**
- ⏳ Notificações por email
- ⏳ Sistema de notas/comentários
- ⏳ Filtros avançados
- ⏳ Exportação de relatórios

---

**🚀 Sistema completo e pronto para uso!**

Coordenadores agora têm controle total sobre aprovação de candidaturas, com opções rápidas para gerenciar grandes volumes.
