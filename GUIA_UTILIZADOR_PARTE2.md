# 📗 Guia do Utilizador - Parte 2: Membro da Comissão

**Versão**: 2.1 | **Data**: 15/10/2025

---

## 🟢 MEMBRO DA COMISSÃO

### 1. Gestão de Vagas

#### Criar Vaga
1. Menu → Vagas → "+ Nova Vaga"
2. Preencha: Título, Descrição, Data Limite
3. Criar → Status "Aberta"

**Estados**: 🟢 Aberta → 🔴 Fechada → ⚫ Encerrada

#### Operações
- **Fechar**: Para candidaturas (reversível)
- **Encerrar**: Arquivar permanentemente (irreversível)
- **Eliminar**: Apenas se sem júris/candidaturas

---

### 2. Gestão de Candidaturas

#### Dashboard
Menu → Candidaturas → Dashboard
- Estatísticas gerais
- Alertas urgentes (>48h)
- Gráficos de tendências

#### Aprovar/Rejeitar
1. Menu → Candidaturas → Lista
2. Filtre por vaga/status
3. **Aprovar**: ✅ → Vigilante disponível
4. **Rejeitar**: ❌ → Preencha motivo obrigatório

---

### 3. Gestão de Júris

#### 3 Interfaces Disponíveis

**A) Lista de Júris**: Visão completa
- Menu → Júris → Lista de Júris
- Criar, editar, eliminar individual

**B) Planeamento por Vaga**: Focado
- Menu → Júris → Planeamento por Vaga
- Gerir júris de vaga específica

**C) Planeamento Avançado**: Visual ⭐
- Menu → Júris → Planeamento Avançado
- Drag-and-drop de vigilantes
- Auto-alocação inteligente

---

#### Criar Júri Individual
1. Lista de Júris → "+ Novo Júri"
2. Preencha: Vaga, Local, Sala, Disciplina, Data, Horário, Candidatos, Vigilantes
3. Criar

#### Criar Múltiplos Júris (Excel)
1. Menu → Júris por Local → Importar
2. Baixe template Excel
3. Preencha com dados de múltiplos júris
4. Upload → Sistema cria automaticamente

---

#### Alocar Vigilantes

**Método 1: Drag-and-Drop**
1. Planeamento Avançado
2. Arraste vigilante para júri
3. Feedback: 🟢 OK | 🟡 Aviso | 🔴 Bloqueado
4. Solte → Alocado

**Método 2: Auto-Alocação Individual**
- No júri → "🤖 Auto"
- Sistema aloca baseado em menor carga

**Método 3: Auto-Alocação Completa** 🚀
1. Planeamento Avançado
2. "⚡ Auto-Alocar Completo" na disciplina
3. Preenche TODOS os júris automaticamente
4. Algoritmo Greedy: equilibra carga, evita conflitos

**Validações automáticas**:
✅ Sem conflito de horário
✅ Capacidade não excedida  
✅ Vigilante disponível
✅ Perfil completo

---

### 4. Júris por Local

#### Templates (Reutilizar Configurações)
1. Menu → Júris por Local → Templates
2. Criar template: Nome, Local, Disciplinas + Salas
3. Usar template: Selecione vaga + data → Aplica
4. ✅ Cria todos os júris automaticamente

#### Importar Excel
1. Menu → Júris por Local → Importar
2. Baixe template
3. Preencha: Local, Data, Disciplina, Sala, Horários
4. Upload → Cria em massa

#### Dashboard de Locais
- Top locais por candidatos
- Breakdown por data
- Estatísticas agregadas

---

### 5. Exportações

**Formatos**: Excel (.xlsx) e PDF

**Disponíveis**:
- Lista de júris completa
- Lista de vigilantes
- Relatórios por local
- Estatísticas

**Como exportar**:
- Botão "📊 Exportar Excel" ou "📄 PDF"
- Arquivo baixado automaticamente

---

### 6. Relatórios e Dashboards

#### Dashboard Principal
- Vagas abertas
- Vigilantes disponíveis
- Próximos júris

#### Dashboard de Candidaturas
- Taxa de aprovação
- Tempo médio de revisão
- Candidaturas urgentes

#### Dashboard de Locais
- Distribuição geográfica
- Capacidades utilizadas
- KPIs por local

---

## 💡 Fluxo de Trabalho Recomendado

### Antes da Sessão de Exames (2-4 semanas)

1. **Criar Vaga**
   - Título claro + descrição completa
   - Prazo adequado (7-14 dias)

2. **Aguardar Candidaturas**
   - Monitore dashboard
   - Responda dúvidas

3. **Rever Candidaturas**
   - Priorize urgentes (>48h)
   - Aprove/rejeite com critério
   - Forneça feedback construtivo

### Durante Preparação (1-2 semanas antes)

4. **Criar Júris**
   - Use importação Excel (se muitos)
   - Ou templates salvos
   - Ou crie individualmente

5. **Alocar Vigilantes**
   - Use auto-alocação completa (mais rápido)
   - Ou drag-and-drop (mais controlo)
   - Revise alocações
   - Ajuste manualmente se necessário

6. **Verificar**
   - Todos os júris preenchidos?
   - Todos têm supervisor?
   - Sem conflitos de horário?

### Durante Exames

7. **Monitorar**
   - Vigilantes presentes?
   - Problemas de última hora?
   - Substituições necessárias?

### Após Exames

8. **Fechar Vaga**
   - Após conclusão dos exames

9. **Encerrar Vaga**
   - Após pagamentos e conclusão total
   - Arquiva permanentemente

---

## 🎯 Dicas e Boas Práticas

### Vagas
✅ Título descritivo com período  
✅ Descrição completa (datas, locais, requisitos)  
✅ Prazo adequado para candidaturas  
❌ Não encerre antes de total conclusão

### Candidaturas
✅ Revise dentro de 48h  
✅ Motivo claro ao rejeitar  
✅ Seja educado e construtivo  
❌ Não aprove perfis incompletos

### Júris
✅ Use auto-alocação para economizar tempo  
✅ Revise alocações após auto-alocação  
✅ Priorize vigilantes com menor carga  
❌ Evite conflitos de horário

### Templates
✅ Crie para locais recorrentes  
✅ Mantenha atualizados  
✅ Use para economizar horas  
❌ Não delete templates em uso

---

**Próximo**: 📕 [Parte 3 - Coordenador + FAQ](GUIA_UTILIZADOR_PARTE3.md)
