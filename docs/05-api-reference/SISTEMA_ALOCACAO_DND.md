# 📚 Sistema de Alocação Drag-and-Drop

## Visão Geral

Sistema completo de alocação inteligente de vigilantes e supervisores com interface **Drag-and-Drop**, validações em tempo real, equilíbrio de carga automático e prevenção de conflitos de horário.

---

## 🎯 Funcionalidades Principais

### 1. **Interface Drag-and-Drop Intuitiva**
- Arraste vigilantes e supervisores diretamente para os júris
- Feedback visual em tempo real (verde/âmbar/vermelho)
- Tooltips informativos durante o arraste
- Contadores de slots em tempo real

### 2. **Validações Automáticas**
- **Conflitos de horário**: Impede alocação de pessoa em júris sobrepostos
- **Capacidade de vigilantes**: Respeita limite configurado por júri (padrão: 2)
- **Supervisor único**: Um supervisor por júri, permite substituição
- **Disponibilidade**: Verifica se vigilante está disponível
- **Elegibilidade**: Valida se pessoa tem permissões corretas

### 3. **Equilíbrio de Carga (Algoritmo Greedy)**
- **Score de carga**: Vigilância = 1 ponto, Supervisão = 2 pontos
- **Distribuição justa**: Prioriza pessoas com menor carga atual
- **Desvio padrão**: Monitora e minimiza desigualdade
- **Badges visuais**: Cores indicam nível de carga (verde/amarelo/vermelho)

### 4. **Auto-Alocação Inteligente**
- **Rápida**: Aloca vigilantes em um júri específico
- **Completa**: Aloca todos os júris de uma disciplina simultaneamente
- **Heurística**: Usa algoritmo Greedy para distribuição equilibrada
- **Sem conflitos**: Respeita todas as restrições de horário

### 5. **Métricas e KPIs em Tempo Real**
- Total de júris
- Slots disponíveis vs alocados
- Júris sem supervisor
- Desvio padrão da carga
- Qualidade do equilíbrio (Excelente/Bom/Melhorar)
- Taxa média de ocupação

---

## 🗄️ Estrutura do Banco de Dados

### Views Criadas (5)

#### `vw_vigilante_workload`
Calcula carga de trabalho por pessoa:
```sql
- n_vigias: Número de vigilâncias
- n_supervisoes: Número de supervisões
- score: n_vigias * 1 + n_supervisoes * 2
```

#### `vw_jury_slots`
Mostra ocupação de cada júri:
```sql
- vigilantes_allocated: Vigilantes já alocados
- vigilantes_capacity: Capacidade máxima
- vigilantes_available: Slots restantes
- occupancy_status: incomplete|full|overfilled
```

#### `vw_eligible_vigilantes`
Lista vigilantes elegíveis por júri (sem conflitos):
```sql
- has_conflict: 0 = elegível, 1 = conflito de horário
- workload_score: Carga atual do vigilante
```

#### `vw_eligible_supervisors`
Lista supervisores elegíveis por júri:
```sql
- has_conflict: 0 = elegível, 1 = conflito
- supervision_count: Número de supervisões atuais
```

#### `vw_allocation_stats`
Estatísticas globais:
```sql
- total_capacity: Soma de todos os slots
- avg_workload_score: Carga média
- workload_std_deviation: Desvio padrão
- vigilantes_without_allocation: Pessoas sem nenhuma alocação
```

### Triggers de Validação (3)

#### `trg_check_vigilantes_capacity`
- Impede exceder capacidade de vigilantes por júri
- Dispara antes de INSERT em `jury_vigilantes`

#### `trg_check_vigilante_conflicts`
- Detecta conflitos de horário de vigilantes
- Valida sobreposição de intervalos de tempo

#### `trg_check_supervisor_conflicts`
- Detecta conflitos de horário de supervisores
- Dispara ao atualizar `supervisor_id` em `juries`

---

## 🚀 Como Usar

### 1. Executar Migration (Primeira vez apenas)

```bash
cd c:/xampp/htdocs/comexamesul
php scripts/run_allocation_migration.php
```

Isso criará:
- Campos `vigilantes_capacity`, `requires_supervisor` em `juries`
- Campos auxiliares em `jury_vigilantes`
- 5 views de consulta
- 3 triggers de validação
- Índices para performance

### 2. Acessar Interface de Planejamento

Navegue para: **http://localhost/juries/planning**

Ou clique no menu: **Júris → Planejamento**

### 3. Alocar Manualmente (Drag-and-Drop)

#### **Alocar Vigilante:**
1. Na lista da esquerda, encontre o vigilante desejado
2. Arraste-o para a zona de **Vigilantes** do júri
3. Observe o feedback visual:
   - 🟢 **Verde**: Pode alocar sem problemas
   - 🟡 **Âmbar**: Pode alocar, mas piora equilíbrio de carga
   - 🔴 **Vermelho**: Não pode alocar (conflito ou capacidade)
4. Solte para confirmar alocação

#### **Alocar Supervisor:**
1. Na lista de **Supervisores** (inferior esquerda)
2. Arraste para a zona de **Supervisor** do júri
3. Se já houver supervisor, será substituído (com confirmação)

#### **Remover Alocação:**
- Clique no **✕** ao lado do nome da pessoa alocada
- Confirme a remoção

### 4. Auto-Alocação Rápida

#### **Auto-Alocar Um Júri:**
1. Clique no botão **"Auto"** no cartão do júri
2. Confirme a operação
3. Algoritmo Greedy selecionará vigilantes com menor carga
4. Slots serão preenchidos respeitando capacidade

#### **Auto-Alocar Disciplina Completa:**
1. Clique em **"⚡ Auto-Alocar Completo"** no cabeçalho da disciplina
2. Confirme a operação
3. TODOS os júris da disciplina serão alocados automaticamente
4. Distribui vigilantes equilibradamente entre salas

---

## 📊 Interpretando as Métricas

### Desvio Padrão da Carga
- **≤ 1.0**: Excelente equilíbrio 🟢
- **1.0 - 2.0**: Bom equilíbrio 🟡
- **> 2.0**: Precisa melhorar 🔴

### Badges de Carga (Pessoas)
- **Verde (0-1)**: Carga leve
- **Amarelo (2-3)**: Carga moderada
- **Vermelho (4+)**: Carga pesada

### Status de Ocupação (Júris)
- **incomplete**: Faltam vigilantes
- **full**: Capacidade completa
- **overfilled**: Acima da capacidade (erro)

---

## 🔧 Configurações Avançadas

### Ajustar Capacidade de Vigilantes

Por padrão, cada júri aceita **2 vigilantes**. Para alterar:

1. **Via Interface**: Ao criar/editar júri, defina `vigilantes_capacity`
2. **Via SQL**: 
```sql
UPDATE juries SET vigilantes_capacity = 3 WHERE id = 123;
```

### Ajustar Pesos do Algoritmo

No arquivo `AllocationService.php`:

```php
const WEIGHT_VIGILANCE = 1;      // Peso de uma vigilância
const WEIGHT_SUPERVISION = 2;    // Peso de uma supervisão
const BALANCE_TOLERANCE = 1.0;   // Tolerância de desvio
```

- **Aumentar `WEIGHT_SUPERVISION`**: Torna supervisões "mais pesadas"
- **Reduzir `BALANCE_TOLERANCE`**: Algoritmo ficará mais rigoroso

### Desabilitar Triggers (Não recomendado)

```sql
DROP TRIGGER IF EXISTS trg_check_vigilantes_capacity;
DROP TRIGGER IF EXISTS trg_check_vigilante_conflicts;
DROP TRIGGER IF EXISTS trg_check_supervisor_conflicts;
```

---

## 🔌 Endpoints de API

### POST `/api/allocation/can-assign`
Valida se pode alocar sem executar:
```json
{
  "vigilante_id": 5,
  "jury_id": 10,
  "type": "vigilante"
}
```

**Resposta:**
```json
{
  "can_assign": true,
  "reason": "Vigilante tem carga acima da média",
  "severity": "warning"
}
```

### POST `/api/allocation/auto-allocate-jury`
Auto-aloca um júri específico:
```json
{
  "jury_id": 10,
  "csrf": "..."
}
```

### POST `/api/allocation/auto-allocate-discipline`
Auto-aloca todos os júris de uma disciplina:
```json
{
  "subject": "Matemática I",
  "exam_date": "2025-11-15",
  "csrf": "..."
}
```

### POST `/api/allocation/swap`
Troca vigilantes entre júris:
```json
{
  "from_vigilante_id": 5,
  "to_vigilante_id": 8,
  "jury_id": 10,
  "csrf": "..."
}
```

### GET `/api/allocation/metrics`
Obtém métricas detalhadas:
```json
{
  "success": true,
  "metrics": {
    "total_juries": 45,
    "slots_available": 12,
    "conflicts_count": 0,
    "avg_occupancy_percent": 87.5,
    "balance_quality": "excellent"
  }
}
```

### GET `/api/allocation/eligible-vigilantes/{juryId}`
Lista vigilantes elegíveis para um júri.

### GET `/api/allocation/eligible-supervisors/{juryId}`
Lista supervisores elegíveis para um júri.

---

## 🛠️ Resolução de Problemas

### "Capacidade máxima de vigilantes atingida"
- Verifique o campo `vigilantes_capacity` do júri
- Aumente a capacidade se necessário
- Ou remova um vigilante existente antes de adicionar outro

### "Vigilante já está alocado em júri com horário conflitante"
- Vigilante tem outro júri no mesmo dia/horário
- Verifique a sobreposição de horários
- Remova a alocação conflitante ou escolha outro vigilante

### "Supervisor já está alocado em júri com horário conflitante"
- Supervisor não pode estar em dois júris simultaneamente
- Escolha outro supervisor ou ajuste horários

### Views não encontradas (vw_*)
- Execute novamente: `php scripts/run_allocation_migration.php`
- Verifique permissões de banco de dados

### Drag-and-drop não funciona
- Verifique se SortableJS está carregado (F12 → Console)
- Limpe cache do navegador (Ctrl+F5)
- Verifique se está em `/juries/planning`

---

## 🧪 Testes Recomendados

### Teste 1: Conflito de Horário
1. Crie 2 júris da mesma disciplina, mesmo dia, horários sobrepostos
2. Aloque vigilante A no Júri 1
3. Tente alocar vigilante A no Júri 2
4. **Esperado**: Erro de conflito

### Teste 2: Capacidade Máxima
1. Crie júri com capacidade 2
2. Aloque 2 vigilantes
3. Tente alocar um 3º vigilante
4. **Esperado**: Erro de capacidade

### Teste 3: Auto-Alocação Equilibrada
1. Crie 6 júris da mesma disciplina
2. Tenha 6 vigilantes disponíveis com cargas variadas
3. Execute "Auto-Alocar Completo"
4. **Esperado**: Vigilantes com menor carga são escolhidos primeiro

### Teste 4: Supervisor Único
1. Aloque supervisor A no Júri 1
2. Arraste supervisor B para o Júri 1
3. **Esperado**: Supervisor A é substituído por B

---

## 📈 Melhorias Futuras

- [ ] Desfazer/Refazer (Undo/Redo) de alocações
- [ ] Histórico de mudanças por júri
- [ ] Exportar planejamento para Excel
- [ ] Notificações de conflitos por e-mail
- [ ] Algoritmo Min-Cost Flow (otimização global)
- [ ] Preferências de horário por vigilante
- [ ] Previsão de disponibilidade futura
- [ ] Dashboard analítico com gráficos

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Consulte esta documentação
2. Verifique logs em `storage/logs/`
3. Teste endpoints via Postman/Insomnia
4. Execute migration novamente se necessário

---

## ⚖️ Licença

Mesmo do projeto ComExamesSul.

---

**Desenvolvido**: 09/10/2025  
**Versão**: 1.0  
**Status**: ✅ Produção Ready
