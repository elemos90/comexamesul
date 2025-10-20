# 📍 Guia: Criação de Júris por Local

## Visão Geral

O sistema agora suporta a criação de júris considerando que um **Local** (campus, escola, etc.) pode albergar **várias disciplinas** no mesmo dia, cada uma com seus próprios júris e salas.

---

## 🎯 Conceito

### Antes
- Criava-se júris por disciplina individual
- Não havia agrupamento claro por local de realização

### Agora
- **Local** é o primeiro nível de organização
- Um local pode ter múltiplas disciplinas no mesmo dia
- Cada disciplina pode ter horários diferentes
- Cada disciplina pode ter múltiplas salas

---

## 📋 Estrutura Hierárquica

```
LOCAL (Campus Central, Escola XYZ)
  └── DATA (15/11/2025)
       ├── DISCIPLINA 1 (Matemática I)
       │    ├── Horário: 08:00 - 11:00
       │    ├── Sala 101 (30 candidatos)
       │    ├── Sala 102 (30 candidatos)
       │    └── Sala 103 (25 candidatos)
       │
       ├── DISCIPLINA 2 (Física I)
       │    ├── Horário: 14:00 - 17:00
       │    ├── Sala 201 (35 candidatos)
       │    └── Sala 202 (35 candidatos)
       │
       └── DISCIPLINA 3 (Química I)
            ├── Horário: 08:00 - 11:00
            ├── Sala 301 (40 candidatos)
            └── Sala 302 (40 candidatos)
```

---

## 🚀 Como Usar

### Passo 1: Acessar a Criação de Júris
1. Faça login como **Coordenador** ou **Membro da Comissão**
2. Navegue para **Júris** no menu
3. Clique no botão **"Criar Exames por Local"** (botão azul principal)

### Passo 2: Definir o Local e Data
Preencha os campos obrigatórios:
- **Nome do Local**: Ex: "Campus Central", "Escola Secundária ABC"
- **Data dos Exames**: Selecione a data comum para todas as disciplinas

### Passo 3: Adicionar Disciplinas
Para cada disciplina que será realizada no local:

1. **Clique em "Adicionar Disciplina"** (botão verde)
2. Preencha:
   - **Nome da Disciplina**: Ex: "Matemática I"
   - **Horário de Início**: Ex: 08:00
   - **Horário de Fim**: Ex: 11:00

### Passo 4: Adicionar Salas para Cada Disciplina
Dentro de cada disciplina:

1. **Clique no botão "Sala"** (botão azul pequeno)
2. Preencha:
   - **Nº da Sala**: Ex: "101", "A1", "Lab 3"
   - **Candidatos**: Número de candidatos nessa sala

3. Repita para todas as salas da disciplina

### Passo 5: Criar Todos os Júris
- Clique em **"Criar Todos os Júris"**
- O sistema criará automaticamente todos os júris
- Você verá uma mensagem de confirmação com o total criado

---

## 💡 Exemplo Prático

### Cenário: Exames no Campus Central (15/11/2025)

**Disciplina 1: Matemática I**
- Horário: 08:00 - 11:00
- Salas: 101 (30), 102 (28), 103 (32)

**Disciplina 2: Física I**  
- Horário: 14:00 - 17:00
- Salas: 201 (35), 202 (35)

**Disciplina 3: Biologia I**
- Horário: 08:00 - 11:00
- Salas: B01 (40), B02 (40)

### Resultado
O sistema criará **7 júris** automaticamente:
- 3 júris para Matemática I (salas 101, 102, 103)
- 2 júris para Física I (salas 201, 202)
- 2 júris para Biologia I (salas B01, B02)

---

## ✅ Vantagens

1. **Organização por Local**: Fácil visualização de todos os exames num campus/escola
2. **Horários Flexíveis**: Disciplinas diferentes podem ter horários diferentes no mesmo local
3. **Criação em Massa**: Crie dezenas de júris de uma só vez
4. **Sem Conflitos**: O sistema continua validando conflitos de horário para vigilantes

---

## 🎨 Interface Dinâmica

### Adicionar/Remover Disciplinas
- **Adicionar**: Botão verde "Adicionar Disciplina"
- **Remover**: Botão vermelho ❌ no canto superior direito de cada disciplina
- **Mínimo**: Deve ter pelo menos 1 disciplina

### Adicionar/Remover Salas
- **Adicionar**: Botão azul "Sala" dentro de cada disciplina
- **Remover**: Botão vermelho ❌ ao lado de cada sala
- **Mínimo**: Cada disciplina deve ter pelo menos 1 sala

---

## 🔍 Visualização dos Júris

Após a criação, os júris são agrupados visualmente por:
1. **Disciplina** (cor azul primária)
   - Mostra: Nome, Data, Horário, Local
   - Lista todas as salas dessa disciplina

2. **Sala** (dentro de cada disciplina)
   - Mostra: Número da sala, quota de candidatos
   - Áreas de drag-and-drop para vigilantes e supervisores

---

## 🛠️ Gestão Após Criação

### Alocar Vigilantes
- Arraste vigilantes do painel lateral para as salas
- O sistema valida conflitos de horário automaticamente

### Atribuir Supervisores
- Arraste supervisores elegíveis para a área amarela de cada sala

### Editar Júri Individual
- Clique no botão "Editar" em qualquer sala
- Modifique campos como quota de candidatos, observações, etc.

### Eliminar Júri
- Clique no botão "Eliminar" (vermelho) em qualquer sala
- Confirme a ação

---

## ⚠️ Notas Importantes

1. **Locais Diferentes, Mesma Disciplina**: Se a mesma disciplina for realizada em locais diferentes, crie separadamente para cada local

2. **Horários Simultâneos**: Disciplinas diferentes podem ter o mesmo horário no mesmo local (ex: Matemática e Biologia às 08:00)

3. **Validação de Vigilantes**: O sistema ainda valida que um vigilante não pode estar em dois júris ao mesmo tempo

4. **Júri Individual**: O botão "Júri Individual" (cinza) continua disponível para casos especiais

---

## 🆚 Comparação com Método Anterior

| Aspecto | Método Anterior | Método Novo (Por Local) |
|---------|----------------|------------------------|
| **Foco** | Uma disciplina | Um local completo |
| **Salas** | Múltiplas | Múltiplas por disciplina |
| **Disciplinas** | Uma por vez | Várias de uma vez |
| **Horários** | Um só | Diferentes por disciplina |
| **Casos de Uso** | Disciplina isolada | Dia completo de exames num local |

---

## 📞 Suporte

Para dúvidas ou problemas:
- Verifique o `TESTE_DRAG_DROP.md` para testar a alocação
- Consulte os logs em `storage/logs/`
- Entre em contato com o administrador do sistema

---

**Última atualização**: 09/10/2025
