# 🎯 Guia: Sistema de Alocação de Equipe

## 📋 Visão Geral

Sistema inteligente para distribuir **Vigilantes** e **Supervisores** aos júris de exames, com:
- ✅ Validação automática de conflitos de horário
- ✅ **Agrupamento por Local → Data → Disciplina** (NOVO!)
- ✅ Interface expansível/colapsável
- ✅ Estatísticas em tempo real

---

## 🚀 Funcionalidades Principais

### 1️⃣ Alocação Manual (Agrupada)
**Página**: `alocar_equipe.php`

- ✅ **Agrupamento hierárquico**: Local → Data → Júri
- ✅ Interface intuitiva por júri
- ✅ Adicionar/remover vigilantes individualmente
- ✅ Alocar/remover supervisor
- ✅ **Validação em tempo real** de conflitos
- ✅ **Cards expansíveis** por local
- ✅ Estatísticas visuais por local
- ✅ Permite correções a qualquer momento

### 2️⃣ Distribuição Automática
**Página**: `distribuicao_automatica.php`

- 🤖 **Sugestões inteligentes** baseadas em algoritmo
- ✅ Calcula quantidade ideal de vigilantes (1 por 30 candidatos)
- ✅ Respeita disponibilidade e conflitos
- ✅ Permite revisão antes de aplicar
- ✅ Aplica todas as sugestões com 1 clique

### 3️⃣ Mapa de Alocações
**Página**: `mapa_alocacoes.php`

- 🗺️ Visualização em **timeline por data**
- ✅ Status de cada júri (completo/incompleto)
- ✅ Lista de equipe alocada
- ✅ Estatísticas de cobertura

---

## 📏 Regras de Alocação

### Vigilantes
```
❌ NÃO podem estar em 2 júris ao mesmo tempo
❌ NÃO podem ser vigilantes se já são supervisores do mesmo júri
✅ Podem ser alocados em júris em horários diferentes
✅ Devem ter available_for_vigilance = 1
```

**Exemplo de CONFLITO**:
- Júri A: 15/11/2025, 08:00-11:00
- Júri B: 15/11/2025, 09:00-12:00
- ❌ Mesmo vigilante não pode estar nos dois

### Supervisores
```
✅ PODEM supervisionar múltiplos júris simultaneamente
❌ MAS apenas no MESMO local
❌ NÃO podem ser supervisores se já são vigilantes do mesmo júri
✅ Devem ter supervisor_eligible = 1
```

**Exemplo PERMITIDO**:
- Júri A: Campus Central, 08:00-11:00
- Júri B: Campus Central, 08:00-11:00
- ✅ Mesmo supervisor pode estar nos dois

**Exemplo de CONFLITO**:
- Júri A: Campus Central, 08:00-11:00
- Júri B: Escola Samora, 08:00-11:00
- ❌ Supervisor não pode estar em locais diferentes

### Regra de Exclusividade por Júri
```
❌ Uma pessoa NÃO pode ser supervisor E vigilante no MESMO júri
✅ Pode ser supervisor de um júri e vigilante de outro (se horários permitirem)
```

**Exemplo de CONFLITO**:
- Júri A: Tentar alocar "João Silva" como supervisor
- Júri A: "João Silva" já é vigilante
- ❌ ERRO: "Esta pessoa já é VIGILANTE deste júri"

---

## 🎮 Como Usar

### Opção A: Alocação Manual

1. **Dashboard** → Clicar em "👥 Alocar Equipe"

2. **Ver lista de júris futuros**

3. **Para cada júri**:
   - Selecionar supervisor no dropdown
   - Clicar "Alocar Supervisor"
   - Selecionar vigilante no dropdown
   - Clicar "+ Adicionar Vigilante"
   - Repetir para adicionar mais vigilantes

4. **Sistema valida automaticamente**:
   - Se houver conflito, mostra mensagem de erro
   - Não permite alocação inválida

5. **Correções**:
   - Clicar "Remover" ao lado de qualquer pessoa
   - Alocar outra pessoa no lugar

---

### Opção B: Distribuição Automática (RECOMENDADO)

1. **Alocar Equipe** → Clicar em "🤖 Distribuição Automática"

2. **Sistema gera sugestões**:
   - Analisa todos os júris futuros
   - Verifica disponibilidade
   - Evita conflitos automaticamente
   - Calcula quantidade ideal de vigilantes

3. **Revisar sugestões**:
   - Ver cada júri e equipe sugerida
   - Verificar se está correto

4. **Aplicar**:
   - Clicar "✓ Aplicar Todas as Sugestões"
   - Sistema aloca todos de uma vez

5. **Ajustar se necessário**:
   - Voltar para Alocação Manual
   - Fazer correções específicas

---

### Opção C: Apenas Visualizar

1. **Dashboard** → "🗺️ Mapa de Alocações"

2. **Ver timeline por data**:
   - Todos os júris organizados por dia
   - Status de cada júri
   - Equipe completa de cada um

3. **Estatísticas**:
   - Total de júris
   - Cobertura de supervisores
   - Total de vigilantes alocados

---

## ⚙️ Algoritmo de Distribuição Automática

```
Para cada júri futuro:
  
  1. Verificar se precisa de supervisor:
     - Se não tem: buscar supervisor disponível
     - Verificar se supervisor está livre neste horário/local
     - Alocar primeiro disponível
  
  2. Calcular vigilantes necessários:
     - Fórmula: MAX(2, CEIL(vagas_candidatos / 30))
     - Exemplo: 75 vagas → MAX(2, CEIL(75/30)) = 3 vigilantes
  
  3. Buscar vigilantes disponíveis:
     - Para cada vigilante:
       - Verificar se está livre neste horário
       - Se sim: adicionar à sugestão
       - Parar quando atingir quantidade necessária
  
  4. Gerar sugestão final:
     - Supervisor + Lista de vigilantes
     - Usuário revisa e aplica
```

---

## 📊 Exemplos Práticos

### Exemplo 1: Alocação Simples
```
Júri: MAT1, 15/11/2025, 08:00-11:00, Campus Central, 50 vagas

Necessário:
- 1 Supervisor
- 2 Vigilantes (50/30 = 1.66, arredonda para 2)

Resultado:
✅ Supervisor: Dr. João Silva
✅ Vigilante 1: Maria Santos
✅ Vigilante 2: Pedro Costa
```

### Exemplo 2: Múltiplos Júris no Mesmo Local
```
Júri A: FIS1, 15/11/2025, 08:00-11:00, Campus Central
Júri B: QUI1, 15/11/2025, 09:00-12:00, Campus Central

✅ Mesmo supervisor pode estar nos dois júris
❌ Vigilantes diferentes para cada júri (horários se sobrepõem)
```

### Exemplo 3: Conflito de Vigilante
```
Tentativa:
- Júri A: 08:00-11:00 → Vigilante: Ana
- Júri B: 09:00-12:00 → Vigilante: Ana

Resultado:
❌ ERRO: "Vigilante já alocado em FIS1 às 08:00-11:00 no Campus Central"
```

---

## 🔧 Solução de Problemas

### "Vigilante já alocado em outro júri"
**Causa**: Conflito de horário  
**Solução**: Escolher outro vigilante ou ajustar horários

### "Supervisor já alocado em outro local"
**Causa**: Supervisor em local diferente no mesmo horário  
**Solução**: 
- Escolher outro supervisor, OU
- Mover júris para o mesmo local

### "Nenhuma sugestão disponível"
**Causa**: Todos os júris já têm equipe completa  
**Solução**: ✅ Tudo ok! Nada a fazer

### "Erro ao aplicar sugestões"
**Causa**: Alguém já alocou entre você gerar e aplicar  
**Solução**: Atualizar página e gerar novas sugestões

---

## 📈 Boas Práticas

1. **Use Distribuição Automática primeiro**
   - Economiza tempo
   - Evita erros manuais
   - Garante distribuição uniforme

2. **Revise as sugestões**
   - Sempre conferir antes de aplicar
   - Verificar se conhece as pessoas
   - Ajustar preferências específicas

3. **Use Mapa de Alocações para overview**
   - Visualização rápida de gaps
   - Identificar júris sem equipe
   - Ver carga de trabalho por pessoa

4. **Faça ajustes manuais quando necessário**
   - Preferências específicas
   - Substituições de última hora
   - Casos especiais

5. **Verifique regularmente**
   - Confirmar disponibilidade
   - Atualizar mudanças
   - Comunicar com equipe

---

## 🎯 Fluxo Recomendado

```
1. Criar todos os júris
   ↓
2. Distribuição Automática
   ↓
3. Revisar sugestões
   ↓
4. Aplicar sugestões
   ↓
5. Ver Mapa de Alocações
   ↓
6. Ajustes manuais se necessário
   ↓
7. Comunicar equipe alocada
```

---

## 📞 Acesso às Páginas

| Página | URL | Função |
|--------|-----|--------|
| Dashboard | `dashboard_direto.php` | Menu principal |
| Alocar Equipe | `alocar_equipe.php` | Alocação manual |
| Distribuição Automática | `distribuicao_automatica.php` | Sugestões inteligentes |
| Mapa de Alocações | `mapa_alocacoes.php` | Visualização timeline |

---

## ✅ Checklist Pré-Exames

- [ ] Todos os júris têm supervisor
- [ ] Todos os júris têm vigilantes (mínimo 2)
- [ ] Não há conflitos de horário
- [ ] Equipe foi comunicada
- [ ] Números de contato confirmados
- [ ] Locais e salas verificados

---

**Dúvidas?** Acesse o Mapa de Alocações para visualização completa! 🗺️
