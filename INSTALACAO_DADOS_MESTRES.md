# 🚀 Instalação: Sistema de Dados Mestres

**Data**: 11/10/2025  
**Versão**: 2.2  
**Tempo estimado**: 5 minutos

---

## 📦 O que será instalado

Este sistema adiciona gestão centralizada de:
- ✅ **Disciplinas** (ex: Matemática I, Física I)
- ✅ **Locais** (ex: Campus Central, Escolas)
- ✅ **Salas** por local (ex: Sala 101, Lab1)
- ✅ **Dropdowns inteligentes** no formulário de júris
- ✅ **Validação automática** de conflitos de sala
- ✅ **Dados de exemplo** (10 disciplinas, 4 locais, 19 salas)

---

## 🔧 Instalação

### Opção 1: Via phpMyAdmin (RECOMENDADO)

1. **Abra o phpMyAdmin**:
   ```
   http://localhost/phpmyadmin
   ```

2. **Selecione o banco** `comexamesul` (coluna esquerda)

3. **Clique na aba "SQL"** (topo)

4. **Copie e cole TODO o conteúdo** de:
   ```
   app/Database/migrations_master_data_simple.sql
   ```

5. **Clique em "Executar"** (canto inferior direito)

6. **Verifique o resultado**:
   - Deve aparecer: "✅ Tabelas criadas!"
   - Disciplinas: 10
   - Locais: 4
   - Salas: 19

### Opção 2: Via Linha de Comando

Se o MySQL estiver no PATH:

```bash
# Windows (CMD, não PowerShell)
cd c:\xampp\htdocs\comexamesul
c:\xampp\mysql\bin\mysql.exe -u root comexamesul < app/Database/migrations_master_data_simple.sql
```

---

## ✅ Verificação

Após executar a migration, verifique:

### 1. Tabelas Criadas
No phpMyAdmin, verifique se existem:
- [x] `disciplines`
- [x] `exam_locations`
- [x] `exam_rooms`

### 2. Dados de Exemplo
Execute no phpMyAdmin (aba SQL):
```sql
SELECT COUNT(*) as total, 'Disciplinas' as tabela FROM disciplines
UNION ALL
SELECT COUNT(*), 'Locais' FROM exam_locations
UNION ALL
SELECT COUNT(*), 'Salas' FROM exam_rooms;
```

**Resultado esperado**:
```
total | tabela
------+------------
10    | Disciplinas
4     | Locais
19    | Salas
```

### 3. Acessar Interfaces Web

1. **Disciplinas**:
   ```
   http://localhost/master-data/disciplines
   ```
   - Login: coordenador@unilicungo.ac.mz / password
   - Deve mostrar 10 disciplinas (MAT1, FIS1, etc.)

2. **Locais**:
   ```
   http://localhost/master-data/locations
   ```
   - Deve mostrar 4 cards de locais

3. **Salas**:
   ```
   http://localhost/master-data/rooms
   ```
   - Selecione um local no dropdown
   - Deve mostrar as salas desse local

---

## 📊 Dados de Exemplo Instalados

### Disciplinas (10)
| Código | Nome |
|--------|------|
| MAT1 | Matemática I |
| MAT2 | Matemática II |
| FIS1 | Física I |
| QUI1 | Química I |
| BIO1 | Biologia I |
| POR1 | Português I |
| ING1 | Inglês I |
| HIS1 | História I |
| GEO1 | Geografia I |
| INF1 | Informática I |

### Locais (4)
| Código | Nome | Salas |
|--------|------|-------|
| CC | Campus Central | 7 |
| ES1 | Escola Secundária Samora Machel | 5 |
| ES2 | Escola Secundária Eduardo Mondlane | 4 |
| CB | Campus Bairro | 3 |

### Salas (19 distribuídas)

**Campus Central (7)**:
- 101, 102, 103 (Piso 1, Bloco A)
- 201, 202 (Piso 2, Bloco A)
- AUD1 (Auditório)
- LAB1 (Laboratório)

**Escola Samora Machel (5)**:
- A1, A2, A3 (Piso 1)
- B1, B2 (Piso 2)

**Escola Eduardo Mondlane (4)**:
- 1A, 1B (Piso 1)
- 2A, 2B (Piso 2)

**Campus Bairro (3)**:
- S1, S2, S3 (Térreo)

---

## 🎯 Como Usar

### 1. Cadastrar Nova Disciplina

```
Acesse: /master-data/disciplines
Clique: "Nova Disciplina"
Preencha:
  - Código: TST1
  - Nome: Teste de Disciplina
  - Descrição: (opcional)
Salvar
```

### 2. Cadastrar Novo Local

```
Acesse: /master-data/locations
Clique: "Novo Local"
Preencha:
  - Código: TL1
  - Nome: Local de Teste
  - Endereço, Cidade: (opcional)
  - Capacidade Total: 200
Salvar
```

### 3. Cadastrar Salas de um Local

```
Acesse: /master-data/rooms
Selecione local no dropdown
Clique: "Nova Sala"
Preencha:
  - Código: T1
  - Nome: Sala Teste 1
  - Capacidade: 30
  - Andar, Bloco: (opcional)
Salvar
```

### 4. Criar Júri Usando Dropdowns

```
Acesse: /juries/planning
Clique: "Criar Exames por Local"

ANTES (digitação livre):
❌ Disciplina: [digite aqui]
❌ Local: [digite aqui]
❌ Sala: [digite aqui]

AGORA (dropdowns):
✅ Disciplina: [MAT1 - Matemática I ▼]
✅ Local: [Campus Central ▼]
✅ Sala: [101 - Sala 101 (35 vagas) ▼]

Benefícios:
- Nomes padronizados
- Sem erros de digitação
- Validação automática de conflitos
- Relatórios consistentes
```

---

## 🔒 Permissões

### Gestão de Dados Mestres
- **Quem**: Apenas Coordenador
- **Acesso**: `/master-data/*`

### Uso em Formulários
- **Quem**: Coordenador e Membro
- **Acesso**: `/juries/planning`

---

## ⚠️ Troubleshooting

### Erro: "Table 'disciplines' doesn't exist"
**Causa**: Migration não foi executada  
**Solução**: Repetir instalação via phpMyAdmin

### Erro: "Access denied for user 'root'"
**Causa**: Senha do MySQL  
**Solução**: 
```bash
# Windows CMD
c:\xampp\mysql\bin\mysql.exe -u root -p comexamesul
# Digitar senha quando solicitado
# Depois colar o conteúdo do SQL
```

### Menu "Dados Mestres" não aparece
**Causa**: Não está logado como coordenador  
**Solução**: Fazer login com: coordenador@unilicungo.ac.mz / password

### Dropdowns vazios ao criar júri
**Causa**: Sem dados cadastrados  
**Solução**: 
1. Verificar se migration rodou com sucesso
2. Acessar `/master-data/disciplines` e verificar se há disciplinas

---

## 🧪 Teste Rápido (2 minutos)

```bash
# 1. Executar migration via phpMyAdmin
# 2. Acessar:
http://localhost/master-data/disciplines
# 3. Verificar se aparecem 10 disciplinas
# 4. Acessar:
http://localhost/master-data/locations
# 5. Verificar se aparecem 4 cards de locais
# 6. Clicar em "Gerir Salas" do Campus Central
# 7. Verificar se aparecem 7 salas
```

**Se tudo apareceu**: ✅ Instalação OK!

---

## 📚 Próximos Passos

Após instalação bem-sucedida:

1. **Revisar Disciplinas**: Adicionar/remover conforme necessário
2. **Revisar Locais**: Atualizar com endereços reais
3. **Revisar Salas**: Ajustar capacidades conforme realidade
4. **Criar Júris**: Usar novos dropdowns em `/juries/planning`
5. **Monitorar**: Verificar se conflitos de sala são bloqueados

---

## 📖 Documentação Completa

Para mais detalhes, consulte:
- `IMPLEMENTACAO_DADOS_MESTRES.md` - Documentação técnica completa
- `README.md` - Visão geral do sistema

---

**Status**: ✅ PRONTO PARA INSTALAÇÃO  
**Suporte**: Em caso de dúvidas, consulte a documentação ou logs em `storage/logs/`
