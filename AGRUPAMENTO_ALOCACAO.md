# 📊 Sistema de Alocação Agrupada

## 🎯 Nova Estrutura Hierárquica

A página de alocação de equipe agora está organizada de forma hierárquica para facilitar a gestão:

```
📍 LOCAL
  └─ 📅 DATA
      └─ 📚 DISCIPLINA/EXAME
          ├─ 👔 Supervisor
          └─ 👁️ Vigilantes
```

---

## 🏗️ Estrutura Visual

### Nível 1: LOCAL (Expansível/Colapsável)
```
┌─────────────────────────────────────────┐
│ ▼ 📍 Campus Central            [15]     │
│    Código: CC001                Júris   │
│    👔 12/15 Supervisores                │
│    👁️ 14/15 com Vigilantes             │
└─────────────────────────────────────────┘
```

**Características**:
- Header colorido (gradiente roxo)
- Clicável para expandir/colapsar
- Mostra totais do local
- Estatísticas de preenchimento

### Nível 2: DATA
```
┌─────────────────────────────────────────┐
│ 📅 15/11/2025 (Segunda-feira)  [3 exames]│
└─────────────────────────────────────────┘
```

**Características**:
- Barra azul com borda lateral
- Data formatada (dd/mm/yyyy)
- Dia da semana
- Contador de exames

### Nível 3: DISCIPLINA/EXAME
```
┌─────────────────────────────────────────┐
│ 📚 MAT1 - Matemática I                  │
│ 🕐 08:00-11:00 • 🏛️ Sala 101 • 👥 50   │
│                                          │
│ 👔 Supervisor    │  👁️ Vigilantes       │
│ Dr. João Silva   │  Maria Santos        │
│                  │  Pedro Costa         │
└─────────────────────────────────────────┘
```

**Características**:
- Card individual por júri
- Informações do exame
- Alocação de supervisor (esquerda)
- Alocação de vigilantes (direita)

---

## 🎨 Cores e Indicadores

### Por Nível

| Nível | Cor | Uso |
|-------|-----|-----|
| Local | 🟣 Roxo Gradiente | Header principal |
| Data | 🔵 Azul | Separador de datas |
| Júri | ⚪ Branco/Cinza | Cards de exames |

### Status de Alocação

| Status | Indicador | Cor |
|--------|-----------|-----|
| Com Supervisor | ✓ Supervisor | 🟢 Verde |
| Sem Supervisor | ⚠️ Sem Supervisor | 🔴 Vermelho |
| Com Vigilantes | N Vigilante(s) | 🔵 Azul |
| Sem Vigilantes | ⚠️ Sem Vigilantes | 🟡 Amarelo |

---

## 💡 Vantagens da Estrutura Agrupada

### 1️⃣ Visão por Local
```
✅ Facilita coordenar equipe no mesmo local
✅ Supervisor pode cobrir múltiplos júris
✅ Logística simplificada
✅ Visualização rápida de gaps
```

### 2️⃣ Organização por Data
```
✅ Ver todos os exames do mesmo dia
✅ Identificar conflitos de horário
✅ Planejar distribuição diária
✅ Acompanhar cronograma
```

### 3️⃣ Foco por Disciplina
```
✅ Alocar especialistas por área
✅ Garantir expertise adequada
✅ Controle fino por exame
✅ Facilita auditoria
```

---

## 🔄 Funcionalidades Interativas

### Colapsar/Expandir Locais
```javascript
Clique no header do local para:
- Colapsar: Ocultar todas as datas e júris
- Expandir: Mostrar todo o conteúdo

Útil quando há muitos locais!
```

### Navegação Rápida
```
1. Identificar local de interesse
2. Expandir apenas aquele local
3. Ver apenas as datas relevantes
4. Focar nos júris específicos
```

---

## 📊 Exemplo Real de Uso

### Cenário: Campus Central com 15 Júris

```
▼ 📍 Campus Central                    [15 Júris]
   👔 12/15 Supervisores | 👁️ 14/15 com Vigilantes

   ├─ 📅 15/11/2025 (Segunda-feira)    [5 exames]
   │   ├─ MAT1 08:00-11:00  ✓ Supervisor ✓ 2 Vigilantes
   │   ├─ FIS1 09:00-12:00  ✓ Supervisor ✓ 2 Vigilantes
   │   ├─ QUI1 14:00-17:00  ⚠️ Sem Supervisor ✓ 2 Vigilantes
   │   ├─ BIO1 14:00-17:00  ✓ Supervisor ⚠️ Sem Vigilantes
   │   └─ GEO1 15:00-18:00  ✓ Supervisor ✓ 3 Vigilantes
   │
   ├─ 📅 16/11/2025 (Terça-feira)      [4 exames]
   │   ├─ MAT2 08:00-11:00  ✓ Supervisor ✓ 2 Vigilantes
   │   ├─ FIS2 08:00-11:00  ✓ Supervisor ✓ 2 Vigilantes
   │   ├─ QUI2 14:00-17:00  ✓ Supervisor ✓ 2 Vigilantes
   │   └─ BIO2 14:00-17:00  ✓ Supervisor ✓ 2 Vigilantes
   │
   └─ 📅 17/11/2025 (Quarta-feira)     [6 exames]
       ├─ ... (mais exames)
```

### Insights Visuais Imediatos

1. **QUI1** precisa de supervisor ⚠️
2. **BIO1** precisa de vigilantes ⚠️
3. **Campus Central** está 80% completo
4. **15/11** é o dia mais carregado (5 exames)
5. Mesmo supervisor pode cobrir **MAT2** e **FIS2** (mesmo local, mesmo horário)

---

## 🎯 Casos de Uso

### Caso 1: Alocar Equipe para um Local Específico
```
1. Expandir apenas "Campus Central"
2. Ver todas as datas
3. Identificar gaps
4. Alocar equipe de forma sistemática
```

### Caso 2: Verificar um Dia Específico
```
1. Expandir todos os locais
2. Navegar até a data desejada (ex: 15/11)
3. Ver todos os exames daquele dia
4. Garantir que não há conflitos
```

### Caso 3: Focar em uma Disciplina
```
1. Buscar visualmente "MAT1" nos cards
2. Ver horário, local, sala
3. Alocar especialista em matemática
4. Adicionar vigilantes
```

### Caso 4: Visão Geral Rápida
```
1. Colapsar todos os locais
2. Ver apenas os headers
3. Identificar local com menos cobertura
4. Expandir e trabalhar naquele local
```

---

## 🔍 Navegação Eficiente

### Fluxo Recomendado

```
1. Abrir página de Alocação
   ↓
2. Ver resumo global (cards no topo)
   ↓
3. Identificar local prioritário
   ↓
4. Expandir apenas aquele local
   ↓
5. Navegar pelas datas
   ↓
6. Alocar equipe júri por júri
   ↓
7. Passar para próximo local
```

---

## 📱 Responsividade

### Desktop (> 1024px)
- 3 cards de resumo horizontal
- Grid 2 colunas (Supervisor | Vigilantes)
- Múltiplos locais visíveis

### Tablet (768px - 1024px)
- 3 cards de resumo empilhados
- Grid 1 coluna (Supervisor sobre Vigilantes)
- Um local por vez recomendado

### Mobile (< 768px)
- Cards de resumo verticais
- Interface adaptada
- Scroll suave

---

## 🎓 Dicas de Produtividade

### ✅ DO (Faça)
- Trabalhe um local por vez
- Use colapsar/expandir
- Verifique estatísticas do header
- Alocar em ordem: Local → Data → Júri

### ❌ DON'T (Não Faça)
- Não deixe muitos locais expandidos
- Não ignore os indicadores coloridos
- Não esqueça de verificar conflitos
- Não aloque aleatoriamente

---

## 🚀 Próximas Melhorias Possíveis

1. **Filtros**: Por status, data, disciplina
2. **Busca**: Encontrar júri específico
3. **Ordenação**: Por data, local, status
4. **Exportar**: PDF/Excel por local
5. **Notificações**: Alertas de gaps
6. **Drag & Drop**: Arrastar pessoas entre júris

---

## 📊 Comparação: Antes vs Depois

### ANTES (Lista Simples)
```
- Lista longa de todos os júris misturados
- Difícil encontrar júri específico
- Sem contexto de local/data
- Scroll infinito
```

### DEPOIS (Agrupada)
```
✅ Organizados por local
✅ Agrupados por data
✅ Fácil navegação
✅ Contexto visual claro
✅ Estatísticas por nível
✅ Expansível/colapsável
```

---

## 🎉 Resultado Final

**Sistema de Alocação Inteligente** que facilita:
- Gestão por local
- Planejamento por data
- Controle por disciplina
- Visão holística e detalhada
- Produtividade aumentada

**Acesse**: `alocar_equipe.php` 🚀
