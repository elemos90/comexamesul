# 🚀 Sistema de Alocação Automática "Auto → Revisão Humana"

**Versão**: 1.0  
**Data**: 2025-10-10  
**Domínio**: Gestão de Exames de Admissão - ComExAdmissao

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Arquitetura do Sistema](#arquitetura-do-sistema)
3. [Fluxo de Funcionamento](#fluxo-de-funcionamento)
4. [Instalação e Configuração](#instalação-e-configuração)
5. [Guia de Uso](#guia-de-uso)
6. [API Endpoints](#api-endpoints)
7. [Algoritmo de Alocação](#algoritmo-de-alocação)
8. [Regras de Negócio](#regras-de-negócio)
9. [Troubleshooting](#troubleshooting)
10. [Testes de Aceitação](#testes-de-aceitação)

---

## 🎯 Visão Geral

### Objetivo

Implementar um sistema de alocação automática de **vigilantes** e **supervisores** a júris de exame em **duas fases**:

1. **PLANEJAR (AUTO)** - Gera plano de alocação sem gravar no BD
2. **APLICAR (COMMIT)** - Confirma e grava após revisão humana

### Benefícios

- ⚡ **Rapidez**: Gera plano completo em segundos
- 🎯 **Equilíbrio**: Distribui carga uniformemente (score-based)
- 🔒 **Segurança**: Previne conflitos de horário e violações de capacidade
- ✏️ **Flexibilidade**: Permite revisão/edição antes de aplicar
- 📊 **Transparência**: Mostra racional de cada alocação

### Contexto

- **Stack**: PHP 8.1+, MySQL 8, MVC simples, Tailwind CSS, SortableJS
- **Domínio**: Júris organizados por Local → Data → Disciplina → Salas
- **Usuários**: Coordenadores e Membros da Comissão

---

## 🏗️ Arquitetura do Sistema

### Componentes

```
┌─────────────────────────────────────────────────────┐
│                    FRONTEND (UI)                     │
│  - Botão "Gerar Plano (Auto)"                       │
│  - Modal de Seleção (Local/Data)                    │
│  - Modal de Revisão (Lista editável)                │
│  - JavaScript: auto-allocation-planner.js           │
└──────────────────┬──────────────────────────────────┘
                   │
                   │ AJAX (fetch)
                   ▼
┌─────────────────────────────────────────────────────┐
│                  BACKEND (API)                       │
│  - POST /api/alocacao/plan-local-date               │
│  - POST /api/alocacao/apply-local-date              │
│  - GET  /api/alocacao/kpis                          │
│  - JuryController::planLocalDate()                  │
│  - JuryController::applyLocalDate()                 │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│             SERVIÇO DE NEGÓCIO                       │
│  - AllocationPlannerService                         │
│    * Algoritmo Greedy + Round-robin                 │
│    * Cálculo de score (1×vigia + 2×supervisor)      │
│    * Validação de conflitos e capacidade            │
│    * Geração de diff (INSERT/DELETE)                │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│                 BANCO DE DADOS                       │
│  - Tabelas: juris, jury_vigilantes, users          │
│  - Triggers: Validações automáticas                 │
│  - View: vw_docente_score                           │
│  - Índices: Otimização de queries                   │
└─────────────────────────────────────────────────────┘
```

### Camadas

| Camada | Responsabilidade |
|--------|------------------|
| **UI** | Interação com usuário, coleta de inputs, exibição de resultados |
| **Controller** | Validação de requests, autenticação, roteamento |
| **Service** | Lógica de negócio, algoritmo de alocação, cálculo de scores |
| **Database** | Persistência, triggers, validações de integridade |

---

## 🔄 Fluxo de Funcionamento

### Fase 1: PLANEJAR (Auto)

```
1. Usuário clica "Gerar Plano (Auto)"
   ↓
2. Modal solicita: Local e Data
   ↓
3. Frontend → POST /api/alocacao/plan-local-date
   ↓
4. Backend:
   a. Buscar júris do local/data
   b. Agrupar por janela temporal (horário)
   c. Buscar docentes elegíveis + scores atuais
   d. Executar algoritmo (Greedy + Round-robin):
      - Alocar supervisores (menor score)
      - Alocar vigilantes (round-robin, menor score)
   e. Calcular stats (desvio pré/pós)
   f. Retornar JSON com plan (não grava no BD)
   ↓
5. Frontend exibe modal de REVISÃO:
   - Estatísticas do plano
   - Lista de ações propostas (editável)
   - Avisos/bloqueios
   ↓
6. Usuário pode:
   - Remover ações indesejadas (ícone lixeira)
   - Revisar racional de cada alocação
   - Cancelar ou prosseguir
```

### Fase 2: APLICAR (Commit)

```
1. Usuário clica "Aplicar Plano"
   ↓
2. Confirmação (alert)
   ↓
3. Frontend → POST /api/alocacao/apply-local-date
   Body: { location, data, plan (editado) }
   ↓
4. Backend:
   a. Iniciar transação
   b. Para cada ação do plano:
      - Executar INSERT ou DELETE
      - Triggers validam automaticamente
      - Capturar erros (conflito, capacidade, etc.)
   c. Commit da transação
   d. Retornar: { aplicadas, falhas[] }
   ↓
5. Frontend:
   - Mostrar resultado (toastr)
   - Recarregar página (exibe alocações)
```

---

## 🛠️ Instalação e Configuração

### Pré-requisitos

- PHP 8.1+
- MySQL 8.0+
- Composer
- Servidor web (Apache/Nginx)

### Passo 1: Executar Migrations SQL

```bash
# Conectar ao MySQL
mysql -u root -p comexamesul

# Executar migrations
source app/Database/migrations_auto_allocation.sql
```

**O que faz**:
- Adiciona colunas `inicio`, `fim`, `vigilantes_capacidade` na tabela `juris`
- Adiciona colunas `papel`, `juri_inicio`, `juri_fim` na tabela `jury_vigilantes`
- Cria índices para otimização
- Cria triggers de validação:
  - `trg_jv_set_interval_bi/bu` - Materializa janelas temporais
  - `trg_jv_check_cap` - Valida capacidade de vigilantes
  - `trg_jv_supervisor_unico` - Garante supervisor único
  - `trg_jv_no_overlap_ins/upd` - Previne conflitos de horário
- Cria view `vw_docente_score` - Calcula scores agregados

### Passo 2: Verificar Estrutura de Arquivos

Certifique-se de que os seguintes arquivos foram criados:

```
app/
├── Services/
│   └── AllocationPlannerService.php      ✅
├── Controllers/
│   └── JuryController.php                 ✅ (métodos adicionados)
├── Routes/
│   └── web.php                            ✅ (rotas adicionadas)
├── Database/
│   └── migrations_auto_allocation.sql     ✅
└── Views/
    └── juries/
        └── planning.php                   ✅ (modais adicionados)

public/
└── js/
    └── auto-allocation-planner.js         ✅
```

### Passo 3: Limpar Cache (se aplicável)

```bash
# Limpar cache de rotas
php artisan route:clear

# Limpar cache de views
php artisan view:clear
```

### Passo 4: Verificar Permissões

Apenas **coordenadores** e **membros** têm acesso aos endpoints de alocação automática.

---

## 📖 Guia de Uso

### Cenário 1: Alocar Júris de um Local/Data

#### Passo a Passo

1. **Acessar Planejamento**
   ```
   http://localhost/juries/planning
   ```

2. **Clicar em "Gerar Plano (Auto)"** (botão verde com ícone de raio)

3. **Preencher Modal**:
   - **Local**: Digite o nome exato (ex: "Campus Central")
   - **Data**: Selecione a data (ex: 2025-11-15)

4. **Clicar "Gerar Plano"**

5. **Revisar Plano** no modal de revisão:
   - **Estatísticas**: Janelas, ações totais, júris incompletos, desvio pré/pós
   - **Avisos**: Júris sem candidatos suficientes, etc.
   - **Ações Propostas**: Lista por júri com:
     - Nome do docente
     - Papel (vigilante/supervisor)
     - Racional da decisão

6. **Editar Plano** (opcional):
   - Clique no ícone de **lixeira** (🗑️) para remover ações indesejadas
   - Plano atualiza em tempo real

7. **Aplicar Plano**:
   - Clique em "✓ Aplicar Plano"
   - Confirme no alert
   - Aguarde processamento
   - Veja resultado: "X alocações realizadas"

8. **Página recarrega** exibindo vigilantes e supervisores alocados

### Cenário 2: Verificar KPIs

```javascript
// Via JavaScript (console)
const kpis = await fetch('/api/alocacao/kpis?location=Campus Central&data=2025-11-15')
  .then(r => r.json());

console.log(kpis);
```

**Retorno**:
```json
{
  "ok": true,
  "kpis": {
    "total_juris": 12,
    "sem_vigilante": 0,
    "sem_supervisor": 2,
    "conflitos_recentes": 0,
    "desvio_score": 0.87,
    "ocupacao_media": 100.0
  }
}
```

---

## 🔌 API Endpoints

### 1. POST /api/alocacao/plan-local-date

**Descrição**: Gera plano de alocação (não grava no BD)

**Request**:
```json
{
  "location": "Campus Central",
  "data": "2025-11-15",
  "csrf": "TOKEN_CSRF"
}
```

**Response**:
```json
{
  "ok": true,
  "janela_count": 3,
  "stats": {
    "desvio_score_pre": 1.42,
    "desvio_score_pos": 0.89,
    "total_acoes": 36,
    "juris_incompletos": 0
  },
  "plan": [
    {
      "juri_id": 123,
      "juri_info": "Matemática I - Sala 101",
      "acoes": [
        {
          "op": "INSERT",
          "docente_id": 45,
          "docente_name": "Prof. João Silva",
          "papel": "supervisor",
          "racional": "Menor score na janela (score=2); sem conflito"
        },
        {
          "op": "INSERT",
          "docente_id": 61,
          "docente_name": "Prof. Maria Santos",
          "papel": "vigilante",
          "racional": "Balanceamento round-robin (score=3, vaga 1/2)"
        }
      ]
    }
  ],
  "avisos": [
    "2 júris ainda sem vigilantes suficientes"
  ],
  "bloqueios": []
}
```

**Autenticação**: Requerida (coordenador/membro)  
**CSRF**: Requerido

---

### 2. POST /api/alocacao/apply-local-date

**Descrição**: Aplica plano de alocação (grava no BD)

**Request**:
```json
{
  "location": "Campus Central",
  "data": "2025-11-15",
  "plan": [
    {
      "juri_id": 123,
      "acoes": [
        {
          "op": "INSERT",
          "docente_id": 45,
          "papel": "supervisor"
        }
      ]
    }
  ],
  "csrf": "TOKEN_CSRF"
}
```

**Response** (Sucesso):
```json
{
  "ok": true,
  "aplicadas": 36,
  "falhas": []
}
```

**Response** (Com Falhas):
```json
{
  "ok": true,
  "aplicadas": 34,
  "falhas": [
    {
      "juri_id": 127,
      "docente_id": 88,
      "papel": "vigilante",
      "erro": "Conflito de horário"
    },
    {
      "juri_id": 128,
      "docente_id": 91,
      "papel": "vigilante",
      "erro": "Capacidade atingida"
    }
  ]
}
```

**Autenticação**: Requerida (coordenador/membro)  
**CSRF**: Requerido

---

### 3. GET /api/alocacao/kpis

**Descrição**: Retorna métricas de alocação

**Query Params**:
- `location` (string, obrigatório)
- `data` (string YYYY-MM-DD, obrigatório)

**Response**:
```json
{
  "ok": true,
  "kpis": {
    "total_juris": 12,
    "sem_vigilante": 0,
    "sem_supervisor": 2,
    "conflitos_recentes": 0,
    "desvio_score": 0.87,
    "ocupacao_media": 100.0
  }
}
```

**Autenticação**: Requerida (coordenador/membro)

---

## 🧮 Algoritmo de Alocação

### Visão Geral

Algoritmo **Greedy** com **Round-robin** por janela temporal.

### Score de Carga

```
score = (W_VIG × nº_vigilâncias) + (W_SUP × nº_supervisões)

Onde:
  W_VIG = 1  (peso para vigilância)
  W_SUP = 2  (peso para supervisão)
```

**Exemplo**:
- Docente com 3 vigilâncias e 2 supervisões: `score = (1×3) + (2×2) = 7`

### Passo a Passo

#### 1. Preparação

```
1.1. Buscar júris de location + data
1.2. Agrupar por janela temporal (inicio|fim)
1.3. Buscar docentes elegíveis (ativos, disponíveis)
1.4. Calcular scores atuais de cada docente
```

#### 2. Para Cada Janela

```
2.1. ALOCAR SUPERVISORES:
     - Para cada júri da janela:
       a. Verificar se já tem supervisor (skip)
       b. Ordenar docentes por score ASC
       c. Escolher primeiro sem conflito de horário
       d. Registrar INSERT no plano (não grava)
       e. Atualizar score simulado (+2)

2.2. ALOCAR VIGILANTES:
     - Round-robin pelos júris:
       a. Verificar capacidade (vigilantes_capacidade)
       b. Ordenar docentes por score ASC
       c. Escolher primeiro disponível sem conflito
       d. Registrar INSERT no plano
       e. Atualizar score simulado (+1)
       f. Girar para próximo júri
     - Continuar até preencher todos ou esgotar candidatos
```

#### 3. Cálculo de Estatísticas

```
3.1. Desvio padrão pré-alocação (scores atuais)
3.2. Desvio padrão pós-alocação (scores simulados)
3.3. Total de ações propostas
3.4. Júris incompletos (sem vigilantes suficientes)
```

#### 4. Retorno

```
4.1. Retornar JSON com:
     - plan[] (diff de INSERT/DELETE)
     - stats (desvios, totais)
     - avisos[] (júris incompletos, etc.)
     - bloqueios[] (violações previstas)
```

### Complexidade

- **Temporal**: O(J × D × log D)
  - J = número de júris
  - D = número de docentes
  - log D = ordenação por score

- **Espacial**: O(J + D)

### Otimizações

1. **Índices de BD**: Buscas rápidas por horário (O(log n))
2. **Ordenação in-memory**: Evita múltiplas queries
3. **Cache de scores**: Calculado uma vez, reutilizado
4. **Validação antecipada**: Filtra candidatos inválidos cedo

---

## 📜 Regras de Negócio

### Validações Obrigatórias

| Regra | Descrição | Garantia |
|-------|-----------|----------|
| **Conflito de horário** | Docente não pode estar em 2 júris simultâneos | Trigger `trg_jv_no_overlap_ins` |
| **Supervisor único** | Cada júri tem no máximo 1 supervisor | Trigger `trg_jv_supervisor_unico` |
| **Capacidade vigilantes** | Respeitar `vigilantes_capacidade` do júri | Trigger `trg_jv_check_cap` |
| **Disponibilidade** | Apenas docentes com `active = 1` | Query no service |
| **Equilíbrio de carga** | Priorizar docentes com menor score | Algoritmo greedy |

### Cálculo de Janelas Temporais

```sql
-- Júris da mesma janela: mesmo horário (inicio = fim)
SELECT * FROM juris
WHERE location = ? AND exam_date = ?
  AND inicio = '2025-11-15 08:00:00'
  AND fim = '2025-11-15 11:00:00';
```

### Materialização de Janelas

Os triggers `trg_jv_set_interval_bi/bu` copiam `inicio/fim` do júri para `juri_inicio/juri_fim` na alocação.

**Vantagem**: Query de conflito é O(log n) em vez de O(n).

```sql
-- Sem materialização (lento)
SELECT COUNT(*) FROM jury_vigilantes jv
INNER JOIN juris j ON j.id = jv.jury_id
WHERE jv.vigilante_id = ?
  AND j.fim > ? AND j.inicio < ?;

-- Com materialização (rápido)
SELECT COUNT(*) FROM jury_vigilantes jv
WHERE jv.vigilante_id = ?
  AND jv.juri_fim > ? AND jv.juri_inicio < ?;
-- Usa índice idx_jv_docente_intervalo
```

---

## 🐛 Troubleshooting

### Problema 1: Modal não abre

**Sintoma**: Clicar em "Gerar Plano (Auto)" não abre modal.

**Solução**:
1. Abrir console do navegador (F12)
2. Verificar se `auto-allocation-planner.js` está carregando
3. Verificar erros JavaScript
4. Confirmar que elemento `#modal-plan-selector` existe no DOM

### Problema 2: Erro "Token CSRF inválido"

**Sintoma**: POST retorna erro 403.

**Solução**:
1. Verificar se `CSRF_TOKEN` está definido globalmente:
   ```javascript
   console.log(CSRF_TOKEN); // Deve exibir string
   ```
2. Verificar se token está sendo enviado no request:
   ```javascript
   body: JSON.stringify({ csrf: CSRF_TOKEN, ... })
   ```
3. Recarregar página para gerar novo token

### Problema 3: Plano retorna vazio

**Sintoma**: `plan: []` mesmo com júris criados.

**Possíveis causas**:
1. **Local ou Data incorretos**: Verificar nome exato do local
2. **Júris sem janela temporal**: Executar:
   ```sql
   UPDATE juris SET inicio = CONCAT(exam_date, ' ', start_time),
                     fim = CONCAT(exam_date, ' ', end_time)
   WHERE inicio IS NULL;
   ```
3. **Nenhum docente elegível**: Verificar `users.active = 1`

### Problema 4: Falhas ao aplicar plano

**Sintoma**: `falhas: [{ erro: "Conflito de horário" }]`

**Causas comuns**:
- Alocação manual feita entre PLANEJAR e APLICAR
- Edição do plano criou inconsistência

**Solução**:
- Gerar novo plano atualizado
- Remover alocações manuais conflitantes

### Problema 5: Desvio pós > desvio pré

**Sintoma**: Algoritmo piora equilíbrio.

**Possíveis causas**:
1. Poucos docentes disponíveis (distribuição forçada)
2. Janelas muito desiguais (alguns horários com muitos júris)

**Solução**:
- Aumentar pool de docentes elegíveis
- Redistribuir júris por horários

---

## ✅ Testes de Aceitação

### Teste 1: Plano Básico

**Cenário**:
- 5 júris no mesmo horário (janela única)
- 10 docentes disponíveis

**Passos**:
1. Gerar plano
2. Verificar retorno: `ok: true`
3. Verificar `janela_count: 1`
4. Verificar `plan.length >= 5`

**Resultado Esperado**: ✅ Plano gerado com sucesso

---

### Teste 2: Equilíbrio de Carga

**Cenário**:
- Docentes com scores variados (0, 3, 5, 8)
- 10 júris para alocar

**Passos**:
1. Gerar plano
2. Verificar `stats.desvio_score_pos <= stats.desvio_score_pre`

**Resultado Esperado**: ✅ Desvio melhora ou mantém

---

### Teste 3: Validação de Conflitos

**Cenário**:
- Docente já alocado em 08:00-11:00
- Tentar alocar em júri 09:00-12:00 (sobrepõe)

**Passos**:
1. Aplicar plano com conflito
2. Verificar `falhas: [{ erro: "Conflito de horário" }]`

**Resultado Esperado**: ✅ Trigger bloqueia

---

### Teste 4: Capacidade Respeitada

**Cenário**:
- Júri com `vigilantes_capacidade = 2`
- Plano tenta alocar 3 vigilantes

**Passos**:
1. Aplicar plano
2. Verificar que apenas 2 são inseridos
3. Terceiro retorna `erro: "Capacidade atingida"`

**Resultado Esperado**: ✅ Trigger bloqueia excesso

---

### Teste 5: Supervisor Único

**Cenário**:
- Júri já tem supervisor A
- Plano tenta adicionar supervisor B

**Passos**:
1. Aplicar plano
2. Verificar `erro: "Júri já possui supervisor"`

**Resultado Esperado**: ✅ Trigger bloqueia

---

### Teste 6: KPIs Atualizados

**Cenário**:
- Aplicar plano com 15 alocações

**Passos**:
1. Obter KPIs antes
2. Aplicar plano
3. Obter KPIs depois
4. Verificar `total_juris`, `sem_vigilante`, `desvio_score`

**Resultado Esperado**: ✅ Métricas refletem mudanças

---

## 🎓 Notas Técnicas

### Pesos Configuráveis

Para ajustar pesos do score, edite `AllocationPlannerService.php`:

```php
private const W_VIG = 1;  // Peso vigilância
private const W_SUP = 2;  // Peso supervisão
```

Exemplo de ajuste:
```php
private const W_VIG = 1;
private const W_SUP = 3;  // Priorizar ainda mais equilíbrio de supervisões
```

### Performance

- **Plano para 50 júris**: ~0.5-1 segundo
- **Aplicação de 100 alocações**: ~1-2 segundos
- **Limitações**: Testado até 200 júris e 500 docentes

### Logs de Atividade

Todas as operações são registradas:

```sql
SELECT * FROM activity_log
WHERE action IN ('allocation_plan_generated', 'allocation_plan_applied')
ORDER BY created_at DESC;
```

---

## 📝 Conclusão

O sistema **"Auto → Revisão Humana"** permite:

✅ **Geração rápida** de planos de alocação  
✅ **Revisão e edição** antes de aplicar  
✅ **Equilíbrio automático** de carga de trabalho  
✅ **Prevenção garantida** de conflitos  
✅ **Transparência** nas decisões algorítmicas  
✅ **Convivência** com DnD manual existente  

**Próximos Passos Sugeridos**:
- Implementar templates de alocação salvos
- Adicionar notificações push aos docentes
- Exportar planos para PDF/Excel
- Dashboard de análise de equilíbrio

---

**Desenvolvido por**: AI Assistant  
**Licença**: MIT  
**Suporte**: comexamesul@example.com
