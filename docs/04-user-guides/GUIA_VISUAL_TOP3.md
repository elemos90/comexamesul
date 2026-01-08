# 🎨 Guia Visual - Sistema Top-3

## 📸 Como Vai Aparecer na Interface

### 1. Botão "Sugestões Top-3" nos Slots Vazios

**Antes** (slot vazio de Supervisor):
```
┌─────────────────────────────────┐
│ Supervisor                      │
│ ┌─────────────────────────────┐ │
│ │                             │ │
│ │  Arraste supervisor aqui    │ │
│ │                             │ │
│ └─────────────────────────────┘ │
└─────────────────────────────────┘
```

**Agora** (com botão Top-3):
```
┌─────────────────────────────────┐
│ Supervisor                      │
│ ┌─────────────────────────────┐ │
│ │  ⚡ Sugestões Top-3          │ │ ← NOVO!
│ └─────────────────────────────┘ │
│ ou arraste supervisor aqui      │
└─────────────────────────────────┘
```

**Idem para Vigilantes**:
```
┌─────────────────────────────────┐
│ Vigilantes (0/2)                │
│ ┌─────────────────────────────┐ │
│ │  ⚡ Sugestões Top-3          │ │ ← NOVO!
│ └─────────────────────────────┘ │
│ ou arraste vigilantes aqui      │
└─────────────────────────────────┘
```

---

### 2. Popover de Sugestões (ao clicar no botão)

```
┌───────────────────────────────────────────────┐
│ ⚡ Sugestões Supervisor                    ✕  │ ← Header roxo
├───────────────────────────────────────────────┤
│ Matemática I │ Sala 101 │ 08:00 - 11:00     │ ← Info do júri
├───────────────────────────────────────────────┤
│                                               │
│ ┌───────────────────────────────────────────┐ │
│ │ #1  Ana Silva                            │ │ ← Card dourado (#1)
│ │                                           │ │
│ │ 📊 Score: 2  ⭐ Aptidão: 9/10             │ │
│ │ 📍 Mesmo campus  ✓ Preferência           │ │
│ │                                           │ │
│ │ Baixa carga; supervisor experiente;      │ │
│ │ mesmo campus; preferência declarada      │ │
│ │                                           │ │
│ │ [ ✓ Aplicar ]                            │ │ ← Botão verde
│ └───────────────────────────────────────────┘ │
│                                               │
│ ┌───────────────────────────────────────────┐ │
│ │ #2  Bruno João                           │ │ ← Card prata (#2)
│ │                                           │ │
│ │ 📊 Score: 3  ⭐ Aptidão: 8/10             │ │
│ │                                           │ │
│ │ Carga moderada; supervisor experiente    │ │
│ │                                           │ │
│ │ [ ✓ Aplicar ]                            │ │
│ └───────────────────────────────────────────┘ │
│                                               │
│ ┌───────────────────────────────────────────┐ │
│ │ #3  Catarina Lima                        │ │ ← Card bronze (#3)
│ │                                           │ │
│ │ 📊 Score: 4  ⭐ Aptidão: 7/10             │ │
│ │ 📍 Mesmo campus                          │ │
│ │                                           │ │
│ │ Disponível; mesmo campus                 │ │
│ │                                           │ │
│ │ [ ✓ Aplicar ]                            │ │
│ └───────────────────────────────────────────┘ │
│                                               │
└───────────────────────────────────────────────┘
```

---

### 3. Cores e Estilos

#### Ranking Visual
- **#1 (Dourado)**: Fundo amarelo claro, borda dourada
- **#2 (Prata)**: Fundo cinza claro, borda prata
- **#3 (Bronze)**: Fundo branco, borda cinza

#### Métricas (Badges)
- **Score**: Fundo cinza, ícone de gráfico
- **Aptidão**: Fundo cinza, ícone de estrela
- **Mesmo campus**: Fundo verde claro, ícone de localização
- **Preferência**: Fundo verde claro, ícone de check

#### Botões
- **"Aplicar"**: Verde, ícone de check
- **Fechar (✕)**: Cinza, no canto superior direito

---

### 4. Animações

#### Ao Clicar no Botão
```
1. Botão pulsa (loading)
2. Popover aparece com fade-in + scale
3. Cards entram um por um (stagger)
```

#### Ao Aplicar
```
1. Botão "Aplicar" vira "Aplicando..." (disabled)
2. Toast verde aparece: "✓ Alocação aplicada!"
3. Página recarrega em 1 segundo
4. Docente aparece alocado no júri
```

---

### 5. Estados Visuais

#### Loading (Buscando Sugestões)
```
┌─────────────────────────────────┐
│  ⚡ Sugestões Top-3              │
│  [Spinner animado]              │
└─────────────────────────────────┘
```

#### Sem Sugestões
```
┌───────────────────────────────────────────────┐
│ ⚡ Sugestões Supervisor                    ✕  │
├───────────────────────────────────────────────┤
│                                               │
│     ⚠️                                        │
│                                               │
│     Nenhum docente disponível                │
│                                               │
│     Todos os docentes têm conflitos de       │
│     horário ou já atingiram sua capacidade   │
│                                               │
└───────────────────────────────────────────────┘
```

#### Poucas Sugestões (< 3)
```
┌───────────────────────────────────────────────┐
│ #1  Ana Silva                     [ Aplicar ] │
│ #2  Bruno João                    [ Aplicar ] │
│                                               │
│ ⚠️ Apenas 2 docente(s) disponível(is)        │
└───────────────────────────────────────────────┘
```

---

### 6. Responsivo (Mobile)

**Desktop** (> 640px):
- Popover ao lado do slot (direita ou esquerda)
- Largura: 420px
- Altura: até 600px

**Mobile** (≤ 640px):
- Popover centralizado na tela
- Largura: 90% da tela (com margens)
- Altura: adaptativa
- Overlay escuro no fundo

---

### 7. Interação com DnD (Drag-and-Drop)

#### Cenário 1: Usar Sugestão
```
1. Clicar "⚡ Sugestões Top-3"
2. Ver Top-3
3. Clicar "Aplicar" em #1
4. Pronto! ✓
```

#### Cenário 2: Usar DnD (Manual)
```
1. Ignorar botão Top-3
2. Arrastar docente da lista lateral
3. Soltar no slot
4. Pronto! ✓
```

#### Cenário 3: Híbrido (Recomendado!)
```
1. Usar Top-3 para Supervisores (rápido)
2. Usar DnD para Vigilantes (controle fino)
3. Ou vice-versa
4. Flexibilidade total! 🎯
```

---

### 8. Feedback Visual

#### Sucesso
```
┌──────────────────────────────┐
│ ✓ Alocação aplicada com     │  ← Toast verde (canto superior direito)
│   sucesso!                   │     Aparece por 3 segundos
└──────────────────────────────┘
```

#### Erro
```
┌──────────────────────────────┐
│ ✕ Conflito de horário       │  ← Toast vermelho
│   detectado                  │     Aparece por 5 segundos
└──────────────────────────────┘
```

---

### 9. Acessibilidade

#### Teclado
- **Tab**: Navegar entre botões "Aplicar"
- **Enter**: Aplicar sugestão focada
- **Esc**: Fechar popover
- **Shift+Tab**: Navegar para trás

#### Screen Readers
- Botões com `aria-label`
- Popover com `role="dialog"`
- Métricas com `title` descritivo

---

### 10. Performance

#### Otimizações
- **Lazy load**: Sugestões só buscadas ao clicar
- **Cache**: Sugestões cacheadas por 60s (opcional)
- **Debounce**: Cliques duplos prevenidos
- **Optimistic UI**: Feedback imediato antes de recarregar

#### Velocidade Esperada
- **Buscar Top-3**: < 300ms
- **Aplicar sugestão**: < 500ms
- **Total (clique → alocado)**: ~2 segundos

---

### 11. Comparação Visual

#### Antes (Só DnD)
```
Tempo para alocar 10 júris:
1. Arrastar 10 supervisores (manual)     → 5 min
2. Arrastar 20 vigilantes (manual)       → 8 min
Total: ~13 minutos ⏱️
```

#### Agora (Top-3 + DnD)
```
Tempo para alocar 10 júris:
1. 8 supervisores via Top-3 (1 clique)   → 1 min
2. 2 supervisores via DnD (casos esp.)   → 1 min
3. 16 vigilantes via Top-3               → 2 min
4. 4 vigilantes via DnD (ajustes)        → 1 min
Total: ~5 minutos ⏱️ (62% mais rápido!) 🚀
```

---

### 12. Casos de Uso Visuais

#### Caso 1: Alocação Rápida (Supervisor)
```
👤 Gestor quer alocar supervisor em 5 júris

COM TOP-3:
1. Clicar "⚡ Sugestões" → 3 seg
2. Ver #1 (Ana - Score 2) → 1 seg
3. Clicar "Aplicar" → 2 seg
4. Repetir 4× → 25 seg total
Total: ~25 segundos ✅

SEM TOP-3 (Manual):
1. Buscar na lista → 5 seg
2. Ver disponibilidade → 3 seg
3. Calcular score mental → 5 seg
4. Arrastar → 2 seg
5. Verificar conflitos → 5 seg
6. Repetir 4× → 100 seg total
Total: ~1min 40seg ❌
```

#### Caso 2: Caso Especial (Vigilante)
```
👤 Gestor precisa alocar vigilante específico (preferência pessoal)

1. Ignorar "⚡ Sugestões"
2. Buscar docente na lista
3. Arrastar para slot
4. Pronto!

Sistema NÃO força sugestões! 🎯
Controle manual sempre disponível! ✓
```

---

### 13. Indicadores de Qualidade

#### No Card de Sugestão

**Score Baixo** (0-2):
```
📊 Score: 2  ← Verde
"Baixa carga"
```

**Score Médio** (3-5):
```
📊 Score: 4  ← Amarelo
"Carga moderada"
```

**Score Alto** (6+):
```
📊 Score: 7  ← Vermelho
"Alta carga"
```

**Aptidão Alta** (Supervisor):
```
⭐ Aptidão: 9/10  ← Dourado
"Supervisor experiente"
```

**Mesmo Campus**:
```
📍 Mesmo campus  ← Verde
```

**Preferência Declarada**:
```
✓ Preferência  ← Verde
```

---

### 14. Fluxo Visual Completo

```
INÍCIO
  │
  ├─→ Criar Júris
  │   (Modal "Criar Exames por Local")
  │
  ├─→ Ver Júris Criados
  │   (Cards com slots vazios)
  │
  ├─→ Slot Vazio?
  │   │
  │   ├─ SIM → Aparece botão "⚡ Sugestões Top-3"
  │   │         │
  │   │         ├─→ Clicar Botão
  │   │         │   │
  │   │         │   ├─→ Loading (spinner)
  │   │         │   │
  │   │         │   ├─→ Popover abre
  │   │         │   │   │
  │   │         │   │   ├─→ Ver Top-3
  │   │         │   │   │
  │   │         │   │   ├─→ Clicar "Aplicar" em #1
  │   │         │   │   │   │
  │   │         │   │   │   ├─→ Validação
  │   │         │   │   │   │   │
  │   │         │   │   │   │   ├─ OK → Toast verde → Reload → FIM ✓
  │   │         │   │   │   │   │
  │   │         │   │   │   │   └─ ERRO → Toast vermelho → Permanece
  │   │         │   │   │   │
  │   │         │   │   │   └─→ Ou tentar #2 ou #3
  │   │         │   │   │
  │   │         │   │   └─→ Ou fechar (Esc/✕)
  │   │         │
  │   │         └─→ OU arrastar docente (DnD)
  │   │             │
  │   │             └─→ Soltar → FIM ✓
  │   │
  │   └─ NÃO → Mostrar alocação existente
  │
  └─→ FIM (Júri completo)
```

---

## 🎨 Paleta de Cores

| Elemento | Cor | Código |
|----------|-----|--------|
| Header popover | Roxo gradiente | `#667eea → #764ba2` |
| Card #1 | Dourado | `#fbbf24` / `#fffbeb` |
| Card #2 | Prata | `#9ca3af` / `#f9fafb` |
| Card #3 | Branco | `#ffffff` / `#e5e7eb` |
| Botão Aplicar | Verde | `#10b981` |
| Botão Top-3 (Supervisor) | Azul | `#3b82f6` |
| Botão Top-3 (Vigilante) | Verde | `#10b981` |
| Toast Sucesso | Verde | `#d1fae5` / `#065f46` |
| Toast Erro | Vermelho | `#fee2e2` / `#991b1b` |
| Badge "Mesmo campus" | Verde claro | `#d1fae5` / `#059669` |
| Badge "Score" | Cinza | `rgba(0,0,0,0.05)` |

---

**Sistema pronto para uso visual! 🎨**
