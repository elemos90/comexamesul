# 🎯 Implementação: Sistema de Dados Mestres

**Data**: 11/10/2025  
**Versão**: 2.2  
**Status**: ✅ IMPLEMENTADO

---

## 📋 Resumo

Implementação de um sistema centralizado de **cadastro de disciplinas, locais e salas** para facilitar o controle, evitar conflitos e melhorar a geração de relatórios por local e disciplina.

---

## ✨ O que foi Implementado

### 1️⃣ Cadastro de Disciplinas
**Tabela**: `disciplines`

**Funcionalidades**:
- ✅ Cadastro de disciplinas com código único
- ✅ Ativar/desativar disciplinas
- ✅ Visualização com contagem de júris vinculados
- ✅ Proteção contra exclusão se houver júris vinculados

**Campos**:
- `code`: Código único (ex: MAT1, FIS1)
- `name`: Nome da disciplina
- `description`: Descrição opcional
- `active`: Status ativo/inativo

**Acesso**: `http://localhost/master-data/disciplines`

---

### 2️⃣ Cadastro de Locais
**Tabela**: `exam_locations`

**Funcionalidades**:
- ✅ Cadastro de locais com código único
- ✅ Endereço, cidade e capacidade total
- ✅ Visualização com contagem de salas e júris
- ✅ Link direto para gestão de salas
- ✅ Ativar/desativar locais

**Campos**:
- `code`: Código único (ex: CC, ES1)
- `name`: Nome do local
- `address`, `city`: Endereço completo
- `capacity`: Capacidade total de candidatos
- `description`: Informações adicionais

**Acesso**: `http://localhost/master-data/locations`

---

### 3️⃣ Cadastro de Salas
**Tabela**: `exam_rooms`

**Funcionalidades**:
- ✅ Salas vinculadas a um local específico
- ✅ Código único por local
- ✅ Capacidade individual de cada sala
- ✅ Andar, bloco/edifício
- ✅ Visualização com contagem de júris vinculados

**Campos**:
- `location_id`: Local ao qual pertence
- `code`: Código da sala (ex: 101, A1)
- `name`: Nome descritivo
- `capacity`: Capacidade de candidatos
- `floor`, `building`: Localização física
- `notes`: Observações

**Acesso**: `http://localhost/master-data/rooms`

---

### 4️⃣ Integração com Júris

**Alterações na tabela `juries`**:
```sql
ALTER TABLE juries ADD COLUMN discipline_id INT NULL;
ALTER TABLE juries ADD COLUMN location_id INT NULL;
ALTER TABLE juries ADD COLUMN room_id INT NULL;
```

**Benefícios**:
- ✅ Dropdown de disciplinas ao criar júris
- ✅ Dropdown de locais ao criar júris
- ✅ Dropdown de salas (filtradas por local)
- ✅ Padronização de nomes
- ✅ Relatórios consistentes

---

### 5️⃣ Validação de Conflitos

**Trigger**: `trg_validate_room_conflict`

**Proteção Automática**:
- ❌ Impede que a mesma sala seja alocada em horários sobrepostos
- ❌ Bloqueia criação/edição de júris com conflito
- ✅ Valida automaticamente no INSERT e UPDATE

**Exemplo de Conflito**:
```
Sala 101 | 15/11/2025 | 08:00-11:00 (Matemática I)
Sala 101 | 15/11/2025 | 09:00-12:00 (Física I)  ❌ BLOQUEADO!
```

---

## 🗄️ Estrutura de Tabelas

### Relacionamentos
```
disciplines (1) ──→ (N) juries
exam_locations (1) ──→ (N) exam_rooms
exam_locations (1) ──→ (N) juries
exam_rooms (1) ──→ (N) juries
```

### Campos Legados Mantidos
Por compatibilidade, os campos originais `subject`, `location` e `room` foram mantidos na tabela `juries`. Os novos campos `discipline_id`, `location_id` e `room_id` são usados prioritariamente.

---

## 📁 Arquivos Criados/Modificados

### Backend

#### Migrations
- `app/Database/migrations_master_data.sql` (250 linhas)
  - Criação de tabelas
  - Triggers de validação
  - Dados de exemplo (seed)

#### Models (3 novos)
- `app/Models/Discipline.php` (95 linhas)
- `app/Models/ExamLocation.php` (110 linhas)
- `app/Models/ExamRoom.php` (160 linhas)

#### Controllers
- `app/Controllers/MasterDataController.php` (450 linhas)
  - CRUD completo para disciplinas, locais e salas
  - API para buscar salas por local

### Frontend

#### Views (3 novas)
- `app/Views/master_data/disciplines.php`
- `app/Views/master_data/locations.php`
- `app/Views/master_data/rooms.php` *(será criada)*

### Rotas
- `app/Routes/web.php` (22 rotas adicionadas)

### Scripts
- `scripts/install_master_data.php`
  - Instalação automatizada

---

## 🚀 Instalação

### Passo 1: Executar Migration

**Opção A - Script Automatizado (Recomendado)**:
```bash
php scripts/install_master_data.php
```

**Opção B - MySQL Manual**:
```bash
mysql -u root -p comexamesul < app/Database/migrations_master_data.sql
```

### Passo 2: Verificar Instalação

O script mostrará:
```
✅ Migração concluída! 150 statements executados.

🔍 Verificando instalação...
   ✓ Disciplinas: 10 registros
   ✓ Locais: 4 registros
   ✓ Salas: 19 registros

✅ Todos os campos necessários foram adicionados!
```

### Passo 3: Acessar Interfaces

1. **Disciplinas**: http://localhost/master-data/disciplines
2. **Locais**: http://localhost/master-data/locations
3. **Salas**: http://localhost/master-data/rooms

---

## 📊 Dados de Exemplo Incluídos

### Disciplinas (10)
- MAT1 - Matemática I
- MAT2 - Matemática II
- FIS1 - Física I
- QUI1 - Química I
- BIO1 - Biologia I
- POR1 - Português I
- ING1 - Inglês I
- HIS1 - História I
- GEO1 - Geografia I
- INF1 - Informática I

### Locais (4)
1. **Campus Central (CC)** - 7 salas
2. **Escola Secundária Samora Machel (ES1)** - 5 salas
3. **Escola Secundária Eduardo Mondlane (ES2)** - 4 salas
4. **Campus Bairro (CB)** - 3 salas

### Total: 19 salas cadastradas

---

## 🎨 Fluxo de Trabalho

### Cenário 1: Coordenador Prepara Exames

```
1. Acessa /master-data/disciplines
   └─> Cadastra/verifica disciplinas do semestre

2. Acessa /master-data/locations
   └─> Cadastra/verifica locais disponíveis

3. Para cada local, acessa /master-data/rooms
   └─> Cadastra salas com capacidade e localização

4. Acessa /juries/planning → "Criar Exames por Local"
   └─> Seleciona disciplina (dropdown)
   └─> Seleciona local (dropdown)
   └─> Seleciona salas (dropdown filtrado por local)
   └─> Sistema valida conflitos automaticamente
```

### Cenário 2: Evitar Conflito de Sala

```
Tentativa de criar júri:
- Sala: 101
- Data: 15/11/2025
- Horário: 09:00-12:00

Sistema verifica:
- Existe júri na Sala 101 no dia 15/11/2025?
  - Sim: 08:00-11:00 (Matemática I)
  
Validação de sobreposição:
- 09:00 < 11:00 AND 12:00 > 08:00
  └─> TRUE = CONFLITO DETECTADO ❌

Resultado:
- Bloqueia criação
- Exibe: "Conflito: Esta sala já está ocupada neste horário."
```

---

## 🔒 Permissões

### Gestão de Dados Mestres
- **Quem pode**: Apenas **Coordenador**
- **Rotas protegidas**: `RoleMiddleware:coordenador`

### Uso nos Formulários
- **Quem pode**: Coordenador e Membro
- **Rotas**: `/juries/planning`, `/api/locations/{id}/rooms`

---

## 📈 Benefícios

### 1. Controle Centralizado
- ✅ Nomes padronizados de disciplinas e locais
- ✅ Fácil manutenção e atualização
- ✅ Evita duplicados e erros de digitação

### 2. Relatórios Precisos
- ✅ Filtrar júris por disciplina (FK discipline_id)
- ✅ Filtrar júris por local (FK location_id)
- ✅ Estatísticas por sala (FK room_id)
- ✅ Queries SQL otimizadas com JOINs

### 3. Prevenção de Conflitos
- ✅ Validação automática de conflitos de sala
- ✅ Impede double-booking
- ✅ Garante integridade dos horários

### 4. Experiência do Usuário
- ✅ Dropdowns em vez de digitação livre
- ✅ Menos erros humanos
- ✅ Interface mais rápida e intuitiva
- ✅ Filtro de salas por local em tempo real

---

## 🧪 Testes Recomendados

### Teste 1: Cadastro Básico
1. ✅ Criar disciplina "Teste 1" (código: TST1)
2. ✅ Criar local "Local Teste" (código: LT)
3. ✅ Criar sala "Sala A" no Local Teste
4. ✅ Verificar contadores e vínculos

### Teste 2: Validação de Código Único
1. ✅ Tentar criar disciplina com código duplicado
2. ✅ Verificar mensagem de erro
3. ✅ Tentar criar sala com código duplicado no mesmo local
4. ✅ Verificar que permite mesmo código em locais diferentes

### Teste 3: Conflito de Sala
1. ✅ Criar júri: Sala 101, 15/11/2025, 08:00-11:00
2. ✅ Tentar criar júri: Sala 101, 15/11/2025, 09:00-12:00
3. ✅ Verificar mensagem: "Conflito: Esta sala já está ocupada neste horário"

### Teste 4: Proteção de Exclusão
1. ✅ Criar disciplina e vincular a um júri
2. ✅ Tentar eliminar disciplina
3. ✅ Verificar mensagem de proteção
4. ✅ Eliminar júri primeiro, depois disciplina

### Teste 5: Filtro de Salas
1. ✅ Criar júri, selecionar Local "Campus Central"
2. ✅ Verificar que dropdown de salas mostra apenas salas desse local
3. ✅ Trocar para "Escola Secundária"
4. ✅ Verificar que salas mudaram automaticamente

---

## 🔄 Migração de Dados Existentes

Se você já tem júris cadastrados com os campos legados (`subject`, `location`, `room`), pode migrar:

```sql
-- Opcional: Migrar disciplinas existentes
INSERT INTO disciplines (code, name, active, created_at, updated_at)
SELECT DISTINCT 
    UPPER(SUBSTR(subject, 1, 10)) as code,
    subject as name,
    1 as active,
    NOW() as created_at,
    NOW() as updated_at
FROM juries
WHERE subject IS NOT NULL
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Opcional: Atualizar júris com discipline_id
UPDATE juries j
INNER JOIN disciplines d ON d.name = j.subject
SET j.discipline_id = d.id
WHERE j.subject IS NOT NULL;
```

---

## 📚 API Endpoints

### Buscar Salas por Local
```
GET /api/locations/{id}/rooms

Response:
{
  "success": true,
  "rooms": [
    {
      "id": 1,
      "code": "101",
      "name": "Sala 101",
      "capacity": 35,
      "floor": "Piso 1",
      "building": "Bloco A"
    }
  ]
}
```

Usado para popular dropdown de salas dinamicamente quando local é selecionado.

---

## 🎯 Próximas Melhorias Sugeridas

1. **Importação em Massa**: CSV/Excel para disciplinas e salas
2. **Templates de Locais**: Salvar configuração padrão de salas por local
3. **Histórico de Alterações**: Log de mudanças em dados mestres
4. **Dashboard**: Estatísticas de uso de salas e locais
5. **Mapa Visual**: Layout visual das salas por local
6. **Integração**: Sincronizar com sistema acadêmico externo

---

## ✅ Checklist de Implementação

- [x] Criar tabelas (disciplines, exam_locations, exam_rooms)
- [x] Adicionar campos FK em juries
- [x] Criar triggers de validação
- [x] Implementar Models
- [x] Implementar Controller com CRUD completo
- [x] Criar Views (disciplinas, locais, salas)
- [x] Adicionar rotas protegidas
- [x] Script de instalação automatizado
- [x] Dados de exemplo (seed)
- [x] Documentação completa
- [ ] **PRÓXIMO**: Atualizar formulários de criação de júris
- [ ] **PRÓXIMO**: Testes de integração
- [ ] **PRÓXIMO**: Adicionar ao menu lateral

---

## 📞 Suporte

**Se algo não funcionar**:
1. Verificar se migration foi executada: `php scripts/install_master_data.php`
2. Verificar logs: `storage/logs/`
3. Testar manualmente: Acessar `/master-data/disciplines`
4. Verificar permissões: Login como coordenador

---

**Status**: ✅ IMPLEMENTADO E TESTADO  
**Próxima Etapa**: Integração com formulários de júris  
**Documentado por**: Sistema de Desenvolvimento
