# 📝 Sistema de Cancelamento com Justificativa - v2.3

**Data**: 11/10/2025  
**Versão**: 2.3  
**Status**: ✅ Implementado

---

## 🎯 Objetivo

Permitir que vigilantes alterem/cancelem sua disponibilidade, com **justificativa obrigatória** e **anexo opcional** quando já estiverem alocados a júris de exame.

---

## 🔄 Fluxo do Sistema

### **Cenário 1: Vigilante NÃO Alocado**
```
Vigilante clica "Cancelar" → Sistema verifica alocações
  ↓
Não tem alocação → Cancelamento IMEDIATO
  ↓
Candidatura cancelada ✅
```

### **Cenário 2: Vigilante JÁ ALOCADO**
```
Vigilante clica "Cancelar" → Sistema verifica alocações
  ↓
TEM alocação → Exige JUSTIFICATIVA
  ↓
Formulário com:
  - Lista de júris alocados
  - Campo de justificativa (mínimo 20 chars)
  - Upload de documento (opcional, até 5MB)
  ↓
Vigilante envia solicitação
  ↓
Status: PENDENTE (aguarda aprovação coordenador)
  ↓
Coordenador revisa:
  - APROVAR → Candidatura cancelada + vigilante desalocado
  - REJEITAR → Vigilante continua alocado
```

---

## 🗄️ Estrutura do Banco de Dados

### **Nova Tabela: `availability_change_requests`**
```sql
CREATE TABLE availability_change_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vigilante_id INT NOT NULL,              -- Vigilante solicitante
    application_id INT NOT NULL,            -- Candidatura afetada
    request_type ENUM(...) DEFAULT 'cancelamento',
    reason TEXT NOT NULL,                   -- Justificativa (obrigatório)
    attachment_path VARCHAR(255) NULL,      -- Caminho do arquivo
    attachment_original_name VARCHAR(255),  -- Nome original
    has_allocation TINYINT(1) DEFAULT 0,    -- Tem alocação?
    jury_details TEXT NULL,                 -- JSON dos júris
    status ENUM('pendente','aprovada','rejeitada') DEFAULT 'pendente',
    reviewed_at DATETIME NULL,
    reviewed_by INT NULL,                   -- Coordenador revisor
    reviewer_notes TEXT NULL,               -- Notas do coordenador
    ...
);
```

---

## 📂 Arquivos Criados/Modificados

### **Criados:**
1. ✅ `app/Database/migrations_v2.3.sql`
2. ✅ `app/Models/AvailabilityChangeRequest.php`
3. ✅ `app/Views/availability/request_cancel.php`
4. ✅ `storage/uploads/justifications/` (diretório)

### **Modificados:**
1. ✅ `app/Controllers/AvailabilityController.php`
   - Método `requestCancel()` - Verificar alocação
   - Método `submitCancelRequest()` - Processar justificativa
2. ✅ `app/Models/JuryVigilante.php`
   - Método `getByVigilante()` - Buscar alocações
3. ✅ `app/Routes/web.php`
   - `GET /availability/{id}/cancel`
   - `POST /availability/{id}/cancel/submit`
4. ✅ `app/Views/availability/index.php`
   - Botão "Cancelar" para candidaturas aprovadas

---

## 🛠️ Funcionalidades Implementadas

### **1. Verificação Automática de Alocação**
```php
// Controller verifica se vigilante está alocado
$juryVigilanteModel = new JuryVigilante();
$allocations = $juryVigilanteModel->getByVigilante($vigilanteId);

if (empty($allocations)) {
    // Cancelamento direto
} else {
    // Exige justificativa
}
```

### **2. Formulário de Justificativa**
**Campos:**
- ✅ **Lista de júris** onde está alocado (visual destacado)
- ✅ **Textarea** para justificativa (mínimo 20 caracteres)
- ✅ **Upload de documento** (PDF, JPG, PNG, DOC, DOCX - até 5MB)
- ✅ **Validações** de tamanho e tipo de arquivo

### **3. Upload de Documentos**
**Tipos permitidos:**
- PDF
- JPG/JPEG
- PNG
- DOC/DOCX

**Validações:**
- Tamanho máximo: 5MB
- Nome único gerado: `just_{uniqid}_{timestamp}.{ext}`
- Armazenado em: `storage/uploads/justifications/`

### **4. Interface Drag & Drop**
- ✅ Arrastar e soltar arquivo
- ✅ Indicador visual do arquivo selecionado
- ✅ Tamanho do arquivo exibido

---

## 🎨 Interface do Usuário

### **Página de Disponibilidade (`/availability`)**
**Candidaturas Aprovadas:**
- Badge verde "Aprovada"
- **Botão "Cancelar"** (vermelho)
  - Clique → verifica alocação
  - Se alocado → formulário de justificativa
  - Se não → cancelamento imediato

### **Página de Justificativa (`/availability/{id}/cancel`)**
**Elementos:**
1. **Alerta vermelho** indicando alocações
2. **Cards dos júris** com:
   - Disciplina
   - Data e horário
   - Local e sala
3. **Formulário:**
   - Justificativa (textarea grande)
   - Upload de documento
   - Aviso sobre aprovação necessária
4. **Botões:**
   - "Voltar" (cinza)
   - "Enviar Solicitação" (vermelho)

---

## 🚀 Como Usar (Vigilante)

### **Passo 1: Acessar Disponibilidade**
1. Login como vigilante
2. Ir em **Disponibilidade** (`/availability`)
3. Ver suas candidaturas

### **Passo 2: Cancelar Candidatura**
1. Encontrar candidatura **aprovada**
2. Clicar **"Cancelar"**
3. Sistema verifica alocações

### **Passo 3A: Se NÃO Alocado**
- ✅ Cancelamento imediato
- ✅ Mensagem: "Candidatura cancelada com sucesso"

### **Passo 3B: Se ALOCADO**
1. Ver lista de júris onde está alocado
2. Preencher justificativa (mínimo 20 caracteres)
3. **(Opcional)** Anexar documento comprobatório
4. Clicar **"Enviar Solicitação"**
5. Aguardar aprovação do coordenador

---

## 👨‍💼 Para Coordenadores (Futuro - v2.4)

### **Interface de Revisão** (Em desenvolvimento)
Coordenadores poderão:
- Ver solicitações pendentes
- Ler justificativa
- Baixar documento anexado
- Ver júris afetados
- **Aprovar** ou **Rejeitar** com notas

### **Ao Aprovar:**
1. Candidatura cancelada
2. Vigilante desalocado dos júris
3. Júris ficam sem vigilante (necessita realocação)

### **Ao Rejeitar:**
1. Solicitação arquivada
2. Vigilante continua alocado
3. Notas do coordenador registradas

---

## 🧪 Como Testar

### **Teste 1: Cancelamento Direto (Sem Alocação)**
1. Login como vigilante
2. Candidate-se a uma vaga (status pendente ou aprovada)
3. **NÃO seja alocado** a nenhum júri
4. Clique "Cancelar"
5. ✅ Deve cancelar imediatamente

### **Teste 2: Cancelamento com Justificativa (Com Alocação)**
1. Login como vigilante
2. Tenha candidatura aprovada
3. **Seja alocado** a 1+ júris (via planning)
4. Vá em **Disponibilidade**
5. Clique "Cancelar" na candidatura
6. ✅ Deve abrir formulário de justificativa
7. ✅ Deve mostrar lista de júris
8. Preencha justificativa com 20+ caracteres
9. (Opcional) Anexe PDF ou imagem
10. Envie solicitação
11. ✅ Status deve ser "Pendente"

### **Teste 3: Validações**
**Teste justificativa curta:**
- Tente enviar com menos de 20 caracteres
- ✅ Deve dar erro

**Teste arquivo inválido:**
- Tente anexar .exe ou .zip
- ✅ Deve dar erro "Tipo não permitido"

**Teste arquivo grande:**
- Tente anexar arquivo > 5MB
- ✅ Deve dar erro "Arquivo muito grande"

### **Teste 4: Upload de Documento**
1. Anexe PDF válido (< 5MB)
2. Envie solicitação
3. ✅ Arquivo deve ser salvo em `storage/uploads/justifications/`
4. ✅ Nome do arquivo deve ter formato: `just_{id}_{timestamp}.pdf`

---

## 📊 Estatísticas Disponíveis

### **Métodos do Modelo:**
```php
$model = new AvailabilityChangeRequest();

// Contar por status
$counts = $model->countByStatus();
// ['pendente' => 5, 'aprovada' => 10, 'rejeitada' => 2]

// Buscar pendentes (para coordenador)
$pending = $model->getPending();

// Buscar por vigilante
$myRequests = $model->getByVigilante($vigilanteId);

// Verificar se tem pendente
$hasPending = $model->hasPendingRequest($vigilanteId, $applicationId);
```

---

## 🔐 Segurança

### **Validações Implementadas:**
1. ✅ Apenas vigilante dono pode cancelar
2. ✅ Apenas candidaturas aprovadas podem ser canceladas
3. ✅ Justificativa obrigatória se alocado
4. ✅ Validação de tipo e tamanho de arquivo
5. ✅ Nome de arquivo único (evita sobrescrita)
6. ✅ Diretório protegido (`storage/uploads/`)
7. ✅ CSRF token em formulários

### **Tipos de Arquivo Permitidos:**
```php
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
$maxSize = 5 * 1024 * 1024; // 5MB
```

---

## 🗂️ Estrutura de Arquivos

```
storage/
└── uploads/
    └── justifications/
        ├── just_abc123_1697032800.pdf
        ├── just_def456_1697033000.jpg
        └── ...
```

**Formato do nome:**
`just_{uniqid}_{timestamp}.{extensao}`

---

## 📝 Logs de Atividade

Todas as ações são registradas em `activity_log`:

```sql
-- Criação de solicitação
SELECT * FROM activity_log 
WHERE entity = 'availability_change_requests' 
  AND action = 'create';

-- Aprovação/Rejeição (futuro)
SELECT * FROM activity_log 
WHERE entity = 'availability_change_requests' 
  AND action IN ('approve', 'reject');
```

---

## 🚧 Próximas Implementações (v2.4)

### **Interface de Revisão para Coordenadores**
- [ ] Página `/change-requests` (coordenador)
- [ ] Listar solicitações pendentes
- [ ] Detalhes da solicitação:
  - Vigilante
  - Vaga
  - Júris afetados
  - Justificativa
  - Documento anexado
- [ ] Botões "Aprovar" e "Rejeitar"
- [ ] Campo para notas do coordenador
- [ ] Ao aprovar:
  - Cancelar candidatura
  - Desalocar vigilante dos júris
  - Notificar vigilante
- [ ] Dashboard com estatísticas

### **Notificações**
- [ ] Email ao vigilante (solicitação enviada)
- [ ] Email ao coordenador (nova solicitação)
- [ ] Email ao vigilante (aprovada/rejeitada)

### **Relatórios**
- [ ] Exportar solicitações (CSV/PDF)
- [ ] Gráficos de aprovação/rejeição
- [ ] Motivos mais comuns

---

## ✅ Checklist de Implementação

### **Backend:**
- [x] Migration v2.3 executada
- [x] Modelo `AvailabilityChangeRequest`
- [x] Método `getByVigilante()` em `JuryVigilante`
- [x] Controller `requestCancel()`
- [x] Controller `submitCancelRequest()`
- [x] Upload de documentos
- [x] Validações

### **Frontend:**
- [x] View `request_cancel.php`
- [x] Botão "Cancelar" em candidaturas
- [x] Formulário de justificativa
- [x] Upload drag & drop
- [x] Avisos e validações visuais

### **Infraestrutura:**
- [x] Diretório `storage/uploads/justifications/`
- [x] Permissões de escrita
- [x] Rotas configuradas

### **Pendente (v2.4):**
- [ ] Interface de revisão (coordenador)
- [ ] Aprovação/rejeição
- [ ] Desalocação automática ao aprovar
- [ ] Notificações por email

---

## 🎉 Status Final

**Implementação**: ✅ **Concluída (100% Vigilante / 0% Coordenador)**

### **Funcional:**
- ✅ Vigilante pode solicitar cancelamento
- ✅ Sistema detecta alocação automaticamente
- ✅ Justificativa obrigatória se alocado
- ✅ Upload de documentos funcionando
- ✅ Validações completas
- ✅ Logs de auditoria

### **Próxima Fase:**
- ⏳ Interface de revisão para coordenadores
- ⏳ Fluxo de aprovação/rejeição
- ⏳ Notificações

---

**🚀 Sistema pronto para uso pelos vigilantes!**

Os vigilantes já podem solicitar cancelamento com justificativa. A interface de revisão para coordenadores será implementada na próxima versão.
