# 🚀 Guia de Implementação v2.2 - Sistema de Vagas Vinculadas

**Data**: 11/10/2025  
**Versão**: 2.2  
**Tipo**: Atualização Estrutural

---

## 📋 Resumo das Mudanças

Esta atualização reestrutura completamente o sistema de **candidaturas a vagas** e **relacionamento vagas ↔ júris**:

### **Mudanças Principais:**
1. ✅ **Júris vinculados a vagas** específicas
2. ✅ **Candidaturas por vaga** (substitui disponibilidade genérica)
3. ✅ **Validação de perfil completo** antes de candidatura
4. ✅ **Gestão de candidaturas** (aprovar, rejeitar, cancelar)

---

## 🗄️ PASSO 1: Executar Migrations do Banco de Dados

### **1.1. Fazer Backup**
```bash
# IMPORTANTE: Sempre faça backup antes de migrations!
mysqldump -u root -p comexamesul > comexamesul_backup_$(date +%Y%m%d).sql
```

### **1.2. Executar Migration**
```bash
mysql -u root -p comexamesul < app/Database/migrations_v2.2.sql
```

### **1.3. Verificar Tabelas Criadas**
```sql
SHOW TABLES LIKE 'vacancy_applications';
DESCRIBE juries; -- Deve ter campo vacancy_id
DESCRIBE users;  -- Deve ter campos profile_completed e profile_completed_at
```

---

## 📊 Mudanças no Banco de Dados

### **Tabela: `juries`**
```sql
-- Novo campo adicionado:
vacancy_id INT NULL -- Vínculo do júri com uma vaga
```

### **Nova Tabela: `vacancy_applications`**
```sql
CREATE TABLE vacancy_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vacancy_id INT NOT NULL,           -- Vaga
    vigilante_id INT NOT NULL,         -- Vigilante
    status ENUM(...) DEFAULT 'pendente', -- pendente/aprovada/rejeitada/cancelada
    notes TEXT NULL,                   -- Observações do vigilante
    applied_at DATETIME NOT NULL,      -- Data da candidatura
    reviewed_at DATETIME NULL,         -- Data da revisão
    reviewed_by INT NULL,              -- Quem revisou
    ...
);
```

### **Tabela: `users`**
```sql
-- Novos campos adicionados:
profile_completed TINYINT(1) DEFAULT 0,       -- Perfil completo?
profile_completed_at DATETIME NULL            -- Quando completou
```

---

## 🔄 Fluxo Anterior vs Novo Fluxo

### **❌ ANTES (Sistema Antigo)**
```
1. Vigilante ativa "disponibilidade genérica"
   ↓
2. Coordenador cria júris (sem vínculo com vaga)
   ↓
3. Coordenador aloca vigilantes "disponíveis"
```

**Problemas:**
- Disponibilidade não vinculada a concurso específico
- Júris não organizados por vaga/concurso
- Sem controle de candidaturas

---

### **✅ AGORA (Sistema Novo)**
```
1. Coordenador publica VAGA (Ex: "Exames Admissão 2025")
   ↓
2. Vigilante completa PERFIL (telefone, NIB, NUIT, banco)
   ↓
3. Vigilante CANDIDATA-SE à vaga específica
   ↓
4. Coordenador revisa candidaturas (aprovar/rejeitar)
   ↓
5. Coordenador cria JÚRIS vinculados à vaga
   ↓
6. Coordenador aloca vigilantes APROVADOS para aquela vaga
```

**Benefícios:**
- ✅ Candidaturas organizadas por concurso
- ✅ Júris agrupados por vaga
- ✅ Validação de perfil completo
- ✅ Rastreabilidade total

---

## 🛠️ Novos Componentes

### **1. Modelo: `VacancyApplication`**
**Localização**: `app/Models/VacancyApplication.php`

**Métodos principais:**
```php
apply($vacancyId, $vigilanteId, $notes)      // Candidatar-se
hasApplied($vacancyId, $vigilanteId)         // Já candidatou?
getByVigilante($vigilanteId)                 // Candidaturas do vigilante
getByVacancy($vacancyId, $status)            // Candidaturas da vaga
approve($applicationId, $reviewerId)         // Aprovar
reject($applicationId, $reviewerId)          // Rejeitar
cancelApplication($applicationId)            // Cancelar
getAvailableVigilantes($vacancyId)           // Vigilantes disponíveis
countByStatus($vacancyId)                    // Estatísticas
```

---

### **2. Modelo: `User` (Atualizado)**
**Novos métodos:**
```php
isProfileComplete($user)                     // Perfil completo?
getMissingProfileFields($user)               // Campos faltantes
markProfileComplete($id)                     // Marcar como completo
checkAndUpdateProfileStatus($id)             // Auto-verificação
```

**Campos obrigatórios para perfil completo:**
- `phone` (Telefone)
- `nuit` (NUIT)
- `nib` (NIB - 21 dígitos)
- `bank_name` (Nome do Banco)

---

### **3. Controller: `AvailabilityController` (Reescrito)**
**Localização**: `app/Controllers/AvailabilityController.php`

**Ações:**
| Método | Rota | Função |
|--------|------|--------|
| `show()` | GET `/availability` | Listar vagas e candidaturas |
| `apply()` | POST `/vacancies/{id}/apply` | Candidatar-se a vaga |
| `cancel()` | POST `/applications/{id}/cancel` | Cancelar candidatura |

---

### **4. View: `availability/index.php` (Nova)**
**Funcionalidades:**
- ✅ Alerta se perfil incompleto (lista campos faltantes)
- ✅ Lista de candidaturas do vigilante
- ✅ Vagas abertas com botão "Candidatar-me"
- ✅ Status visual das candidaturas (pendente/aprovada/rejeitada)
- ✅ Cancelamento de candidaturas pendentes

---

## 📝 Novas Rotas

```php
// Candidatura a vagas
POST /vacancies/{id}/apply        // Candidatar-se
POST /applications/{id}/cancel    // Cancelar candidatura
```

---

## 🎯 Como Usar o Novo Sistema

### **Para Vigilantes:**

1. **Completar Perfil** (obrigatório)
   - Ir em **Perfil** → preencher telefone, NUIT, NIB, banco
   - Sistema valida automaticamente

2. **Candidatar-se a Vaga**
   - Ir em **Disponibilidade**
   - Ver vagas abertas
   - Clicar **"Candidatar-me"**
   - Status: `pendente`

3. **Acompanhar Candidatura**
   - Ver status em **Disponibilidade**
   - Cancelar se necessário (antes de aprovação)

---

### **Para Coordenadores:**

1. **Publicar Vaga**
   - Ir em **Vagas** → Nova Vaga
   - Definir título, descrição, deadline

2. **Revisar Candidaturas** (NOVO RECURSO - em desenvolvimento)
   - Ver candidaturas pendentes
   - Aprovar ou rejeitar

3. **Criar Júris Vinculados**
   - Ao criar júri, vincular à vaga
   - Campo `vacancy_id` disponível

4. **Alocar Vigilantes**
   - Apenas vigilantes **aprovados** para aquela vaga
   - Drag-and-drop normal

---

## 🔄 Migração de Dados Existentes

A migration automática faz:

### **1. Candidaturas Automáticas**
Vigilantes que já tinham `available_for_vigilance = 1` são automaticamente candidatados às **vagas abertas** com status `aprovada`.

### **2. Perfis Completos**
Usuários com telefone, NUIT, NIB e banco preenchidos são marcados como `profile_completed = 1`.

### **3. Júris Existentes**
Júris criados antes da migration terão `vacancy_id = NULL` (compatibilidade).

---

## ✅ Checklist de Implementação

### **Backend:**
- [x] Migration SQL criada
- [x] Modelo `VacancyApplication`
- [x] Modelo `User` atualizado
- [x] `AvailabilityController` reescrito
- [x] Rotas atualizadas

### **Frontend:**
- [x] View `availability/index.php` nova
- [ ] View para coordenador revisar candidaturas (**PRÓXIMA VERSÃO**)
- [ ] Adicionar campo `vacancy_id` ao criar júris (**PRÓXIMA VERSÃO**)

### **Banco de Dados:**
- [ ] **Executar backup**
- [ ] **Executar migration**
- [ ] **Verificar dados migrados**

---

## 🧪 Como Testar

### **Teste 1: Perfil Incompleto**
1. Login como vigilante
2. Não preencha NIB
3. Vá em **Disponibilidade**
4. ✅ Deve ver alerta vermelho com campos faltantes
5. ✅ Botão "Candidatar-me" desabilitado

### **Teste 2: Completar Perfil**
1. Vá em **Perfil**
2. Preencha: telefone, NUIT, NIB (21 dígitos), banco
3. Salve
4. ✅ Campo `profile_completed` deve ser `1`

### **Teste 3: Candidatura**
1. Com perfil completo, vá em **Disponibilidade**
2. Veja vagas abertas
3. Clique **"Candidatar-me"**
4. ✅ Deve aparecer em "Minhas Candidaturas"
5. ✅ Status: **Pendente**

### **Teste 4: Cancelar Candidatura**
1. Em "Minhas Candidaturas"
2. Clique no ícone ❌
3. Confirme
4. ✅ Status muda para **Cancelada**

### **Teste 5: Vagas Já Candidatadas**
1. Candidatar-se a uma vaga
2. Recarregar página
3. ✅ Card deve mostrar "Já Candidatado" (verde)
4. ✅ Botão não aparece

---

## 🚨 Problemas Conhecidos / Limitações

### **1. Júris Antigos**
Júris criados antes da migration terão `vacancy_id = NULL`.
**Solução**: Editar manualmente se necessário.

### **2. Revisão de Candidaturas**
Interface para coordenador aprovar/rejeitar candidaturas está **em desenvolvimento**.
**Workaround**: Usar SQL diretamente:
```sql
UPDATE vacancy_applications 
SET status = 'aprovada', reviewed_at = NOW(), reviewed_by = 1
WHERE id = X;
```

### **3. Campo vacancy_id em Formulário de Júris**
Ainda não adicionado ao formulário de criação de júris.
**Próxima versão**: Dropdown para selecionar vaga.

---

## 📚 Próximas Implementações (v2.3)

- [ ] Interface de revisão de candidaturas (coordenador)
- [ ] Campo `vacancy_id` no formulário de júris
- [ ] Filtrar júris por vaga
- [ ] Dashboard de estatísticas de candidaturas
- [ ] Notificações por email (candidatura aprovada/rejeitada)
- [ ] Exportar lista de candidatos por vaga

---

## 🆘 Suporte e Rollback

### **Rollback (se necessário)**
```bash
# Restaurar backup
mysql -u root -p comexamesul < comexamesul_backup_YYYYMMDD.sql

# Reverter arquivos
git checkout HEAD~1 app/Controllers/AvailabilityController.php
git checkout HEAD~1 app/Models/
git checkout HEAD~1 app/Views/availability/
```

### **Logs**
Todas as ações são registradas em `activity_log`:
```sql
SELECT * FROM activity_log 
WHERE entity = 'vacancy_applications' 
ORDER BY created_at DESC;
```

---

## ✅ Status Final

**Implementação**: ✅ **Concluída (80%)**

**Funcionalidades Prontas:**
- ✅ Estrutura de banco
- ✅ Candidaturas por vaga
- ✅ Validação de perfil
- ✅ Interface vigilante
- ✅ Cancelamento de candidaturas

**Pendente (v2.3):**
- ⏳ Interface de revisão (coordenador)
- ⏳ Vínculo vaga-júri no formulário
- ⏳ Filtros e relatórios

---

**🎉 Sistema pronto para teste!**

Execute a migration e teste o fluxo completo de candidatura.
