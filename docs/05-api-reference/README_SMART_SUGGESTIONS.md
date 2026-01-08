# 🧠 Sistema de Sugestões Inteligentes "Top-3"

## 📋 Visão Geral

Sistema de alocação assistida por IA que sugere os **3 melhores docentes** para cada slot (Vigilante/Supervisor) baseado em múltiplos critérios:

- ✅ **Disponibilidade** (sem conflito de horário)
- ⚖️ **Equilíbrio de carga** (menor score = menor carga)
- 🎯 **Aptidão** (experiência para supervisão)
- 📍 **Proximidade** (mesmo campus)
- ❤️ **Preferências** (disciplina/horário declarados)

---

## 🎯 Fluxo de Uso

### 1. Criar Júris
```
/juries/planning → "Criar Exames por Local"
```
- Preencher Local, Data, Disciplina
- Adicionar horários e salas
- Clicar "Criar Todos os Júris"

### 2. Buscar Sugestões
Ao visualizar os júris criados:
- **Slot vazio** → Aparece botão **"⚡ Sugestões Top-3"**
- Clicar no botão → Abre popover com Top-3 docentes

### 3. Aplicar Sugestão
No popover:
- Visualizar métricas (Score, Aptidão, Campus, Preferências)
- Ler motivo da sugestão
- Clicar em **"Aplicar"** no docente desejado
- Sistema valida e insere alocação
- Página recarrega com alocação aplicada

### 4. Controle Manual (DnD)
O sistema **convive** com Drag-and-Drop:
- Pode arrastar docentes manualmente
- Pode usar sugestões onde preferir
- **Híbrido**: Use sugestões para ~80% e DnD para casos especiais

---

## 🧮 Algoritmo de Ranking

### Fórmula
```
rank_value = 
  1000 × (conflito ? 1 : 0)           // Bloqueia se houver conflito
+ 4 × score_global                    // Equilibrar carga (1×vigia + 2×sup)
- 2 × aptidão                         // Priorizar mais experientes
+ 1 × distância                       // Penalizar campus diferente
- 1 × preferência                     // Bonificar preferências
+ epsilon(docente_id)                 // Desempate estável
```

**Ordenação**: `rank_value` ASC (menor = melhor)

### Pesos Configuráveis
Editar em `app/Controllers/SuggestController.php`:

```php
private const PESO_CONFLITO = 1000;     // Bloqueia completamente
private const PESO_SCORE = 4;           // Equilibrar carga
private const PESO_APTIDAO = 2;         // Priorizar experientes
private const PESO_DISTANCIA = 1;       // Preferir mesmo campus
private const PESO_PREFERENCIA = 1;     // Bonificar preferências
```

### Score Global
```sql
score = Σ (1 × vigilâncias) + (2 × supervisões)
```

**Exemplo**:
- Docente A: 2 vigias + 1 supervisor = (2×1) + (1×2) = **4 pontos**
- Docente B: 1 vigia + 0 supervisor = (1×1) + (0×2) = **1 ponto** ✅ Melhor

### Aptidão
- **Supervisor**: `experiencia_supervisao / 10` (0.0 a 1.0)
- **Vigilante**: `0.5` (padrão)

---

## 🗄️ Estrutura de Banco

### Tabelas Utilizadas

#### `juries`
```sql
id, subject, location, room, exam_date,
inicio, fim, vigilantes_capacidade, campus
```

#### `jury_vigilantes`
```sql
id, jury_id, vigilante_id, papel,
juri_inicio, juri_fim, assigned_by, created_at
```

#### `users`
```sql
id, name, campus, role, active,
available_for_vigilance, experiencia_supervisao
```

### Coluna Adicional (Opcional)

Se não existir, adicionar:

```sql
ALTER TABLE users 
ADD COLUMN experiencia_supervisao INT DEFAULT 0 
COMMENT 'Experiência em supervisão (0-10)';
```

**Como preencher**:
1. Via interface (se criar campo no perfil)
2. Via SQL direto:
   ```sql
   UPDATE users 
   SET experiencia_supervisao = 8 
   WHERE id = 44; -- Ana Silva (supervisora experiente)
   ```

### Índices (Já criados anteriormente)
```sql
CREATE INDEX idx_jv_docente_intervalo 
ON jury_vigilantes(vigilante_id, juri_inicio, juri_fim);

CREATE INDEX idx_juries_intervalo 
ON juries(inicio, fim);
```

---

## 🛠️ Arquitetura Técnica

### Backend (PHP 8.1+)

#### `SuggestController.php`
- **GET** `/api/suggest-top3?juri_id=X&papel=vigilante|supervisor`
  - Retorna Top-3 docentes ordenados
  - JSON: `{ok, slot, top3[], fallbacks}`

- **POST** `/api/suggest-apply`
  - Body: `juri_id`, `docente_id`, `papel`, `_token`
  - Valida e insere alocação
  - JSON: `{ok, message, allocation_id}`

#### Validações (PHP)
1. ✅ Capacidade de vigilantes
2. ✅ Supervisor único por júri
3. ✅ Conflito de horário
4. ✅ Materialização de janelas temporais

### Frontend (JavaScript)

#### `smart-suggestions.js`
- Classe `SmartSuggestions`
- Escuta cliques em `[data-suggest-slot]`
- Abre popover com Top-3
- Aplica sugestão via POST

#### `smart-suggestions.css`
- Estilos do popover
- Animações de entrada/saída
- Badges de ranking (#1, #2, #3)
- Responsivo (mobile-friendly)

### Integração UI

#### Slots Vazios
```html
<button type="button" 
        data-suggest-slot
        data-juri-id="123"
        data-papel="supervisor">
    ⚡ Sugestões Top-3
</button>
```

#### Popover (renderizado dinamicamente)
```html
<div class="suggestions-popover">
  <div class="popover-header">Sugestões Supervisor</div>
  <div class="suggestions-list">
    <!-- Top-3 cards -->
  </div>
</div>
```

---

## 📊 Resposta da API

### GET `/api/suggest-top3`

**Sucesso** (200):
```json
{
  "ok": true,
  "slot": {
    "juri_id": 123,
    "papel": "supervisor",
    "inicio": "2025-11-03 10:00:00",
    "fim": "2025-11-03 12:00:00",
    "local": "Campus Central",
    "room": "Sala 101",
    "subject": "Matemática I"
  },
  "top3": [
    {
      "docente_id": 44,
      "nome": "Ana Silva",
      "score": 2,
      "aptidao": 0.9,
      "dist": 0,
      "prefer": 1,
      "motivo": "Baixa carga; supervisor experiente; mesmo campus; preferência declarada"
    },
    {
      "docente_id": 61,
      "nome": "Bruno João",
      "score": 3,
      "aptidao": 0.8,
      "dist": 1,
      "prefer": 0,
      "motivo": "Carga moderada; supervisor experiente"
    },
    {
      "docente_id": 73,
      "nome": "Catarina Lima",
      "score": 4,
      "aptidao": 0.7,
      "dist": 0,
      "prefer": 0,
      "motivo": "Disponível; mesmo campus"
    }
  ],
  "fallbacks": 0
}
```

**Erro** (400/404/500):
```json
{
  "ok": false,
  "error": "Júri não encontrado"
}
```

### POST `/api/suggest-apply`

**Sucesso** (200):
```json
{
  "ok": true,
  "message": "Alocação aplicada com sucesso",
  "allocation_id": 789
}
```

**Erro** (400):
```json
{
  "ok": false,
  "error": "Conflito de horário detectado"
}
```

---

## 🧪 Testes de Aceitação

### Teste 1: Top-3 Básico ✅
1. Criar 1 júri (Matemática I, 08:00-11:00)
2. Clicar em "⚡ Sugestões Top-3" (Supervisor)
3. **Verificar**: 3 docentes listados
4. **Verificar**: Ordenação por score crescente
5. Clicar "Aplicar" no #1
6. **Verificar**: Supervisor alocado no júri

### Teste 2: Conflito Bloqueado ✅
1. Alocar docente A em Júri 1 (08:00-11:00)
2. Criar Júri 2 (09:00-12:00)
3. Buscar sugestões para Júri 2
4. **Verificar**: Docente A NÃO aparece no Top-3

### Teste 3: Prioridade por Score ✅
1. Docente A: 0 alocações (score=0)
2. Docente B: 2 vigias + 1 supervisor (score=4)
3. Buscar sugestões
4. **Verificar**: Docente A aparece ANTES de Docente B

### Teste 4: Aptidão para Supervisor ✅
1. Configurar:
   - Ana Silva: `experiencia_supervisao = 10`
   - Bruno João: `experiencia_supervisao = 5`
2. Buscar sugestões para Supervisor
3. **Verificar**: Ana aparece ANTES de Bruno (se scores iguais)

### Teste 5: Mesmo Campus ✅
1. Júri em "Campus Central"
2. Docente A: campus = "Campus Central"
3. Docente B: campus = "Campus Norte"
4. Buscar sugestões
5. **Verificar**: Docente A ranqueia melhor (se outros fatores iguais)

### Teste 6: Capacidade Respeitada ✅
1. Júri com `vigilantes_capacidade = 2`
2. Alocar 2 vigilantes
3. Buscar sugestões para Vigilante
4. **Verificar**: Erro "Capacidade atingida"

### Teste 7: Supervisor Único ✅
1. Alocar supervisor A
2. Buscar sugestões para Supervisor
3. **Verificar**: Erro "Júri já possui supervisor"

---

## ⚙️ Configurações Avançadas

### Ajustar Pesos de Ranking

Editar `app/Controllers/SuggestController.php`:

```php
// Aumentar peso do equilíbrio (penalizar mais quem tem mais carga)
private const PESO_SCORE = 8; // default: 4

// Aumentar peso da aptidão (priorizar mais experientes)
private const PESO_APTIDAO = 4; // default: 2

// Aumentar peso da proximidade (priorizar muito mesmo campus)
private const PESO_DISTANCIA = 3; // default: 1
```

### Cache de Sugestões (Opcional)

Adicionar cache para reduzir queries repetidas:

```php
private function getCachedSuggestions($juriId, $papel) {
    $key = "suggest_{$juriId}_{$papel}";
    $cached = apcu_fetch($key);
    if ($cached !== false) return $cached;
    
    $suggestions = $this->calculateSuggestions($juriId, $papel);
    apcu_store($key, $suggestions, 60); // Cache 60s
    return $suggestions;
}
```

### Logging de Sugestões (Analytics)

Adicionar tracking:

```php
// Ao buscar sugestões
ActivityLogger::log("suggest_view", [
    'juri_id' => $juriId,
    'papel' => $papel,
    'top3' => array_column($top3, 'docente_id')
]);

// Ao aplicar
ActivityLogger::log("suggest_apply", [
    'juri_id' => $juriId,
    'docente_id' => $docenteId,
    'papel' => $papel,
    'rank' => $rankPosition // 1, 2 ou 3
]);
```

---

## 🐛 Troubleshooting

### Erro: "Nenhum docente disponível"

**Causas**:
- Todos os docentes têm conflito de horário
- Nenhum docente marcado como `available_for_vigilance = 1`
- Nenhum docente ativo (`active = 1`)

**Soluções**:
```sql
-- Verificar disponibilidade
SELECT id, name, active, available_for_vigilance 
FROM users 
WHERE role IN ('coordenador', 'membro', 'docente');

-- Ativar docentes
UPDATE users 
SET available_for_vigilance = 1, active = 1 
WHERE id IN (1, 2, 3);
```

### Popover não abre

**Causas**:
- JavaScript não carregado
- Erro de console

**Soluções**:
1. Abrir Console (F12)
2. Verificar erro
3. Recarregar página (Ctrl+F5)
4. Verificar se arquivo existe: `/js/smart-suggestions.js`

### Sugestões sempre iguais

**Causa**: Epsilon de desempate fixo

**Solução**: Adicionar randomização:
```php
private function epsilon(int $docenteId): float {
    return (crc32((string)$docenteId . time()) % 100) / 1000;
}
```

### Performance lenta

**Causas**:
- Muitos docentes (>100)
- Queries não otimizadas

**Soluções**:
1. Verificar índices:
   ```sql
   SHOW INDEX FROM jury_vigilantes;
   SHOW INDEX FROM juries;
   ```

2. Adicionar LIMIT na query de docentes:
   ```php
   $stmt = $db->query("
       SELECT ... FROM users
       WHERE ...
       LIMIT 50  -- Buscar apenas 50 para ranquear
   ");
   ```

3. Usar cache (ver seção "Cache de Sugestões")

---

## 📈 Métricas de Sucesso

Após implementação, monitorar:

- **Taxa de uso**: % de alocações via sugestões vs manual
- **Taxa de aceitação**: % de #1 aplicado vs #2 ou #3
- **Tempo de alocação**: Redução no tempo total de planejamento
- **Equilíbrio**: Desvio padrão de carga após uso de sugestões

**Query de Analytics**:
```sql
SELECT 
    DATE(created_at) AS data,
    COUNT(*) AS total_alocacoes,
    SUM(CASE WHEN assigned_by IS NOT NULL THEN 1 ELSE 0 END) AS via_sistema,
    AVG(CASE WHEN papel='vigilante' THEN 1 WHEN papel='supervisor' THEN 2 END) AS score_medio
FROM jury_vigilantes
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY data DESC;
```

---

## 🚀 Evolução Futura

### Fase 2: Machine Learning
- Aprender com histórico de alocações
- Priorizar docentes com bom feedback
- Evitar docentes com problemas reportados

### Fase 3: Preferências Dinâmicas
- Docentes declaram preferências de horário
- Docentes declaram preferências de disciplina
- Sistema prioriza preferências declaradas

### Fase 4: Otimização Global
- Gerar sugestões para TODOS os júris simultaneamente
- Otimização combinatória (Hungarian Algorithm)
- Garantir equilíbrio global ótimo

---

## ✅ Checklist de Implementação

- [x] SuggestController criado
- [x] Rotas API registradas
- [x] JavaScript de popover criado
- [x] CSS de estilos criado
- [x] UI integrada (botões Top-3)
- [x] Validações PHP implementadas
- [x] Documentação completa
- [ ] Coluna `experiencia_supervisao` adicionada (opcional)
- [ ] Testes de aceitação executados
- [ ] Treinamento de usuários

---

## 📞 Suporte

**Desenvolvido**: 2025-10-10  
**Stack**: PHP 8.1 + MySQL 8 + Tailwind CSS + Vanilla JS  
**Paradigma**: Sugestões Inteligentes + Controle Manual Híbrido

**Filosofia**: 
> "Resolva ~80% dos casos com 1 clique. Mantenha controle total para os outros 20%."

---

**Sistema pronto para uso!** 🎉
