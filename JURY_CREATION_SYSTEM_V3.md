# 🎯 SISTEMA DE CRIAÇÃO DE JÚRIS V3.0 - COMPLETO

## 📊 Visão Geral

Sistema redesenhado para seguir a lógica hierárquica apresentada na imagem de referência:

```
1. Selecionar DISCIPLINA/EXAME
2. Definir DATA e HORÁRIO (único para todas as salas)
3. Escolher LOCAL
4. Adicionar MÚLTIPLAS SALAS com número de candidatos
```

---

## 🎨 FRONTEND (manage_vacancy.php)

### **Modal Redesenhado**

#### **Estrutura em 4 Passos**

```
┌─────────────────────────────────────────────────┐
│ ➕ Criar Júris para Exame                      │
├─────────────────────────────────────────────────┤
│ 1️⃣  DISCIPLINA/EXAME                            │
│    [Autocomplete com disciplinas existentes]    │
├─────────────────────────────────────────────────┤
│ 2️⃣  DATA E HORÁRIO                              │
│    [Data] [Início] [Fim] ✓ Duração: 120 min    │
├─────────────────────────────────────────────────┤
│ 3️⃣  LOCAL                                        │
│    [Dropdown com locais cadastrados]            │
├─────────────────────────────────────────────────┤
│ 4️⃣  SALAS E CANDIDATOS                          │
│    ┌─────────────────────────────┬───────────┐ │
│    │ Sala                        │ Candidatos│ │
│    ├─────────────────────────────┼───────────┤ │
│    │ Sala 39 CEAD (cap: 40)      │ 22        │ │
│    │ Sala 26 Bloco C (cap: 35)   │ 30        │ │
│    │ Sala 38 Comp. (cap: 45)     │ 40        │ │
│    └─────────────────────────────┴───────────┘ │
│    [+ Adicionar Sala]                           │
│    Total: 3 salas | 92 candidatos               │
└─────────────────────────────────────────────────┘
```

### **Funcionalidades JavaScript**

1. **loadMasterData()** - Carrega locais, salas e disciplinas via API
2. **addRoomRow()** - Adiciona linha de sala dinamicamente
3. **removeRoomRow()** - Remove sala da lista
4. **updateRoomsSummary()** - Calcula totais (salas + candidatos)
5. **validateCreateTimeRange()** - Valida horários em tempo real

### **Validações Frontend**

✅ Local deve ser selecionado antes de adicionar salas  
✅ Salas filtradas por local escolhido  
✅ Horário de fim > horário de início  
✅ Mínimo 1 sala deve ser adicionada  
✅ Não pode selecionar a mesma sala duas vezes  

---

## 🔧 BACKEND (JuryController.php)

### **Novos Endpoints Criados**

#### **1. GET `/api/master-data/locations-rooms`**

**Descrição:** Retorna locais e salas ativas para popular o modal

**Resposta:**
```json
{
  "success": true,
  "locations": [
    {
      "id": 1,
      "name": "Campus da Ponta-Gea",
      "code": "PONTA-GEA",
      "capacity": 500
    }
  ],
  "rooms": [
    {
      "id": 1,
      "location_id": 1,
      "code": "A101",
      "name": "Sala A101",
      "capacity": 40,
      "location_name": "Campus da Ponta-Gea"
    }
  ]
}
```

---

#### **2. GET `/api/vacancies/{vacancy_id}/subjects`**

**Descrição:** Retorna disciplinas já usadas na vaga para autocomplete

**Resposta:**
```json
{
  "success": true,
  "subjects": [
    "INGLÊS",
    "MATEMÁTICA",
    "FÍSICA",
    "QUÍMICA"
  ]
}
```

---

#### **3. POST `/juries/create-bulk`**

**Descrição:** Cria múltiplos júris de uma vez (mesma disciplina/horário, várias salas)

**Request:**
```json
{
  "vacancy_id": 1,
  "subject": "INGLÊS",
  "exam_date": "2025-01-31",
  "start_time": "10:30",
  "end_time": "12:30",
  "location_id": 1,
  "rooms": [
    {
      "room_id": 1,
      "candidates_quota": 22
    },
    {
      "room_id": 2,
      "candidates_quota": 30
    },
    {
      "room_id": 3,
      "candidates_quota": 40
    }
  ],
  "csrf": "token_here"
}
```

**Resposta (Sucesso Total):**
```json
{
  "success": true,
  "message": "3 júri(s) criado(s) com sucesso para INGLÊS!",
  "created_count": 3,
  "created_juries": [
    {"id": 1, "room": "Sala 39", "candidates": 22},
    {"id": 2, "room": "Sala 26", "candidates": 30},
    {"id": 3, "room": "Sala 38", "candidates": 40}
  ]
}
```

**Resposta (Conflito):**
```json
{
  "success": false,
  "message": "⚠️ CONFLITO: Já existem júris de INGLÊS nesta data com horário diferente (08:00-10:00). Uma disciplina só pode ter um horário por data.",
  "conflicts": [
    {
      "room_code": "Sala 39",
      "error": "Sala Sala 39 já alocada para MATEMÁTICA (10:00-12:00)"
    }
  ]
}
```

**Resposta (Sucesso Parcial):**
```json
{
  "success": true,
  "message": "2 júri(s) criado(s) com sucesso! 1 com conflitos.",
  "created_count": 2,
  "created_juries": [...],
  "conflicts": [...],
  "partial": true
}
```

### **Validações Backend**

✅ **CSRF Token** - Segurança  
✅ **Campos obrigatórios** - vacancy_id, subject, exam_date, times, location_id, rooms  
✅ **Mínimo 1 sala** - Não pode criar júri sem salas  
✅ **Disciplina + Data + Horário únicos** - Mesma disciplina não pode ter horários diferentes na mesma data  
✅ **Conflito de sala** - Mesma sala não pode ter júris sobrepostos  
✅ **Sala duplicada** - Não pode selecionar a mesma sala duas vezes  
✅ **Local e sala existentes** - Verifica se IDs são válidos  

### **Regras de Negócio Implementadas**

#### **Regra 1: Uma disciplina = Um horário por data**
```
❌ INVÁLIDO:
- INGLÊS às 08:00-10:00
- INGLÊS às 10:30-12:30  (mesmo dia)

✅ VÁLIDO:
- INGLÊS às 10:30-12:30 em Sala 39
- INGLÊS às 10:30-12:30 em Sala 26  (mesmo horário)
```

#### **Regra 2: Uma sala = Um júri por horário**
```
❌ INVÁLIDO:
- Sala 39: INGLÊS 10:00-12:00
- Sala 39: MATEMÁTICA 10:30-12:30  (sobreposição)

✅ VÁLIDO:
- Sala 39: INGLÊS 10:00-12:00
- Sala 39: MATEMÁTICA 14:00-16:00  (horários diferentes)
```

---

## 🛣️ ROTAS (web.php)

```php
// Nova API: Criação em Lote de Júris
$router->post('/juries/create-bulk', 'JuryController@createBulk', [
    'AuthMiddleware', 
    'RoleMiddleware:coordenador,membro', 
    'CsrfMiddleware'
]);

// API: Dados mestre para criação de júris
$router->get('/api/master-data/locations-rooms', 'JuryController@getMasterDataLocationsRooms', [
    'AuthMiddleware', 
    'RoleMiddleware:coordenador,membro'
]);

$router->get('/api/vacancies/{id}/subjects', 'JuryController@getVacancySubjects', [
    'AuthMiddleware', 
    'RoleMiddleware:coordenador,membro'
]);
```

---

## 🔄 FLUXO COMPLETO

### **1. Usuário Abre Modal**
```
Usuário clica: [➕ Criar Novo Júri]
    ↓
JavaScript: showCreateJuryModal()
    ↓
Chama: loadMasterData()
    ↓
API GET /api/master-data/locations-rooms
API GET /api/vacancies/{id}/subjects
    ↓
Preenche dropdowns e autocomplete
```

### **2. Usuário Preenche Formulário**
```
1. Digita "INGLÊS" (autocomplete sugere)
2. Seleciona data: 31/01/2025
3. Seleciona horário: 10:30 - 12:30
4. Seleciona local: "Campus da Ponta-Gea"
5. Clica [+ Adicionar Sala]
   - Seleciona "Sala 39" + 22 candidatos
   - Seleciona "Sala 26" + 30 candidatos
   - Seleciona "Sala 38" + 40 candidatos
6. Verifica resumo: 3 salas | 92 candidatos
7. Clica [Criar Júris]
```

### **3. Backend Processa**
```
POST /juries/create-bulk
    ↓
Validar CSRF
    ↓
Validar campos obrigatórios
    ↓
Verificar horário único para disciplina
    ↓
Para cada sala:
    - Verificar conflito de horário
    - Criar júri individual
    - Log de auditoria
    ↓
Retornar resultado (sucesso/parcial/erro)
```

### **4. Feedback ao Usuário**
```
✅ Sucesso: Toast verde + Reload em 1.5s
⚠️ Parcial: Toast laranja com detalhes
❌ Erro: Toast vermelho + manter no modal
```

---

## 📋 ESTRUTURA DE DADOS

### **Tabela: juries**

```sql
CREATE TABLE juries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vacancy_id INT NOT NULL,
    subject VARCHAR(180) NOT NULL,
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location_id INT NULL,           -- FK para exam_locations
    location VARCHAR(120) NOT NULL,  -- Nome textual (redundante)
    room_id INT NULL,                -- FK para exam_rooms
    room VARCHAR(60) NOT NULL,       -- Código textual (redundante)
    candidates_quota INT NOT NULL,
    notes TEXT NULL,
    supervisor_id INT NULL,
    created_by INT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    
    FOREIGN KEY (vacancy_id) REFERENCES exam_vacancies(id),
    FOREIGN KEY (location_id) REFERENCES exam_locations(id),
    FOREIGN KEY (room_id) REFERENCES exam_rooms(id),
    FOREIGN KEY (supervisor_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Índices para performance
CREATE INDEX idx_juries_vacancy ON juries(vacancy_id);
CREATE INDEX idx_juries_subject_date ON juries(subject, exam_date);
CREATE INDEX idx_juries_room_date ON juries(room_id, exam_date);
CREATE INDEX idx_juries_exam_schedule ON juries(exam_date, start_time, end_time);
```

---

## 🎯 BENEFÍCIOS DO NOVO SISTEMA

### **1. Eficiência**
- ✅ Criar 10+ júris em uma única operação
- ✅ Reduz cliques de ~50 para ~10
- ✅ Menos erros de digitação (autocomplete)

### **2. Validações Robustas**
- ✅ Impossível criar conflitos de horário
- ✅ Garante disciplina = um horário por data
- ✅ Previne double-booking de salas

### **3. UX Melhorada**
- ✅ Interface guiada passo-a-passo
- ✅ Feedback em tempo real
- ✅ Resumo visual antes de criar
- ✅ Tratamento de erros parciais

### **4. Manutenibilidade**
- ✅ Código modular e reutilizável
- ✅ Validações centralizadas no backend
- ✅ Logs de auditoria automáticos
- ✅ Fácil adicionar novas validações

---

## 🧪 TESTES RECOMENDADOS

### **Casos de Teste**

#### **CT-01: Criação Normal**
```
Input: 1 disciplina + 3 salas sem conflitos
Expected: 3 júris criados com sucesso
```

#### **CT-02: Conflito de Horário (Disciplina)**
```
Input: INGLÊS 10:00-12:00 (já existe INGLÊS 08:00-10:00 no mesmo dia)
Expected: Erro - horário diferente para mesma disciplina
```

#### **CT-03: Conflito de Horário (Sala)**
```
Input: Sala 39 10:00-12:00 (já ocupada 09:00-11:00)
Expected: Sala ignorada + outras criadas (sucesso parcial)
```

#### **CT-04: Sala Duplicada**
```
Input: Mesma sala selecionada 2x
Expected: Erro - sala duplicada
```

#### **CT-05: Sem Salas**
```
Input: 0 salas adicionadas
Expected: Erro frontend - adicione pelo menos 1 sala
```

#### **CT-06: Horário Inválido**
```
Input: Fim (10:00) < Início (12:00)
Expected: Validação frontend - horário inválido
```

#### **CT-07: Local sem Salas**
```
Input: Selecionar local vazio + clicar "Adicionar Sala"
Expected: Toast warning - este local não tem salas
```

---

## 🚀 DEPLOY

### **Checklist**

- [x] Backend: 3 métodos adicionados ao `JuryController`
- [x] Routes: 3 rotas adicionadas ao `web.php`
- [x] Frontend: Modal redesenhado no `manage_vacancy.php`
- [x] JavaScript: 8 funções implementadas
- [x] Validações: Frontend + Backend
- [x] Feedback: Toasts + Loading states
- [ ] **Teste em ambiente de desenvolvimento**
- [ ] **Testar todos os casos de erro**
- [ ] **Deploy em produção**

---

## 📖 DOCUMENTAÇÃO DA API

### **Exemplo de Uso com cURL**

```bash
# 1. Obter locais e salas
curl -X GET http://localhost/api/master-data/locations-rooms \
  -H "Cookie: session_id=..." \
  -H "X-Requested-With: XMLHttpRequest"

# 2. Obter disciplinas de uma vaga
curl -X GET http://localhost/api/vacancies/1/subjects \
  -H "Cookie: session_id=..." \
  -H "X-Requested-With: XMLHttpRequest"

# 3. Criar júris em lote
curl -X POST http://localhost/juries/create-bulk \
  -H "Content-Type: application/json" \
  -H "Cookie: session_id=..." \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{
    "vacancy_id": 1,
    "subject": "INGLÊS",
    "exam_date": "2025-01-31",
    "start_time": "10:30",
    "end_time": "12:30",
    "location_id": 1,
    "rooms": [
      {"room_id": 1, "candidates_quota": 22},
      {"room_id": 2, "candidates_quota": 30}
    ],
    "csrf": "abc123..."
  }'
```

---

## ✅ CONCLUSÃO

Sistema completamente reformulado para:
1. ✅ Seguir a lógica hierárquica da imagem de referência
2. ✅ Permitir criação em lote de múltiplos júris
3. ✅ Usar dados mestre (locais/salas cadastrados)
4. ✅ Validar conflitos de horário automaticamente
5. ✅ Garantir integridade: disciplina = 1 horário/data

**Status:** 🟢 **PRONTO PARA TESTES**
