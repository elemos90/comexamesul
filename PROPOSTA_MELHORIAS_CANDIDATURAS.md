# 🎯 Proposta de Melhorias - Sistema de Candidaturas de Vigilantes

**Data**: 11/10/2025  
**Versão Atual**: v2.4.1  
**Versão Proposta**: v2.5.0  

---

## 📋 Resumo Executivo

O sistema de candidaturas de vigilantes está **100% funcional**. Esta proposta visa **estender** o sistema com funcionalidades complementares que melhoram a experiência do usuário e a gestão administrativa.

---

## 🆕 Melhorias Propostas

### **1. Histórico de Status das Candidaturas** 🔄
**Prioridade**: ⭐⭐⭐ Alta  
**Impacto**: Auditoria e Transparência

#### Descrição
Criar um histórico completo de todas as mudanças de status de cada candidatura, permitindo rastreabilidade total.

#### Funcionalidades
- **Timeline visual** de cada candidatura
- Registro de: quem alterou, quando, motivo
- Visualização para vigilante e coordenador
- Exportação do histórico em PDF

#### Implementação
**Nova Tabela:**
```sql
CREATE TABLE application_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    old_status ENUM('pendente', 'aprovada', 'rejeitada', 'cancelada') NULL,
    new_status ENUM('pendente', 'aprovada', 'rejeitada', 'cancelada') NOT NULL,
    changed_by INT NULL,
    changed_at DATETIME NOT NULL,
    reason TEXT NULL,
    metadata JSON NULL,
    FOREIGN KEY (application_id) REFERENCES vacancy_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_application (application_id),
    INDEX idx_date (changed_at)
);
```

**Novo Endpoint:**
- `GET /applications/{id}/history` - Ver histórico

**Exemplo de UI:**
```
Timeline da Candidatura #123

[10/10/2025 14:30] João Silva
└─ CRIAÇÃO → Pendente
   "Candidatura inicial"

[11/10/2025 09:15] Maria Coordenadora
└─ Pendente → Aprovada
   "Perfil completo, experiência adequada"

[12/10/2025 16:45] João Silva
└─ Aprovada → Solicitação de Cancelamento
   "Motivo: Doença familiar"
   📎 atestado.pdf

[13/10/2025 08:20] Maria Coordenadora
└─ Solicitação → Cancelada (Aprovado)
   "Motivo justificado, deferido"
```

---

### **2. Motivos de Rejeição Visíveis** 💬
**Prioridade**: ⭐⭐⭐ Alta  
**Impacto**: Comunicação e Transparência

#### Descrição
Permitir que coordenadores escrevam um motivo ao rejeitar candidaturas, e que vigilantes vejam esse feedback.

#### Funcionalidades
- Campo "Motivo da Rejeição" no formulário de revisão
- Vigilante vê o motivo na sua página de candidaturas
- Motivos pré-definidos + campo livre
- Notificação por email com o motivo

#### Implementação
**Alteração na Tabela:**
```sql
ALTER TABLE vacancy_applications 
ADD COLUMN rejection_reason TEXT NULL AFTER reviewed_by;
```

**Motivos Pré-definidos:**
```php
const REJECTION_REASONS = [
    'perfil_incompleto' => 'Perfil incompleto',
    'falta_experiencia' => 'Falta de experiência comprovada',
    'conflito_horario' => 'Conflito de horário com outras atividades',
    'vagas_preenchidas' => 'Vagas já preenchidas',
    'outro' => 'Outro motivo (especificar)',
];
```

**Exemplo de UI (Coordenador):**
```
┌─────────────────────────────────────┐
│ Rejeitar Candidatura                │
├─────────────────────────────────────┤
│ Motivo:                             │
│ [v] Perfil incompleto               │
│ [ ] Falta de experiência            │
│ [ ] Conflito de horário             │
│ [ ] Vagas já preenchidas            │
│ [v] Outro                           │
│                                     │
│ Detalhes adicionais (opcional):     │
│ ┌─────────────────────────────────┐ │
│ │ Falta preencher NIB e telefone  │ │
│ │ de emergência. Por favor,       │ │
│ │ complete e recandidature-se.    │ │
│ └─────────────────────────────────┘ │
│                                     │
│ [Rejeitar]  [Cancelar]              │
└─────────────────────────────────────┘
```

**Exemplo de UI (Vigilante):**
```
┌─────────────────────────────────────┐
│ 📋 Exames 2025                      │
│ [Rejeitada 🔴]                      │
├─────────────────────────────────────┤
│ ⚠️ Motivo da Rejeição:              │
│                                     │
│ • Perfil incompleto                 │
│                                     │
│ Observações do Coordenador:         │
│ "Falta preencher NIB e telefone     │
│  de emergência. Por favor, complete │
│  e recandidature-se."               │
│                                     │
│ [Completar Perfil] [Recandidatar-se]│
└─────────────────────────────────────┘
```

---

### **3. Notificações por Email** 📧
**Prioridade**: ⭐⭐ Média-Alta  
**Impacto**: Comunicação Proativa

#### Descrição
Sistema automático de notificações por email para eventos importantes.

#### Eventos para Notificação

**Para Vigilantes:**
- ✅ Candidatura aprovada
- ❌ Candidatura rejeitada (com motivo)
- ⏰ Prazo de candidatura próximo (48h antes)
- ✅ Cancelamento aprovado
- ❌ Cancelamento rejeitado
- 📋 Nova vaga publicada (se perfil completo)

**Para Coordenadores:**
- 📝 Nova candidatura recebida
- 🔄 Solicitação de cancelamento (com justificativa)
- ⚠️ Vigilante alocado solicitou cancelamento urgente

#### Implementação
**Nova Tabela:**
```sql
CREATE TABLE email_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at DATETIME NULL,
    error_message TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);
```

**Nova Classe:**
```php
// app/Services/EmailNotificationService.php
class EmailNotificationService
{
    public function notifyApplicationApproved(int $applicationId): bool
    public function notifyApplicationRejected(int $applicationId, string $reason): bool
    public function notifyDeadlineApproaching(int $vacancyId): bool
    public function notifyNewApplication(int $applicationId): bool
    public function notifyCancellationRequest(int $requestId): bool
}
```

**Template de Email (Exemplo):**
```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .header { background: #2563eb; color: white; padding: 20px; }
        .content { padding: 20px; }
        .button { background: #2563eb; color: white; padding: 12px 24px; 
                  text-decoration: none; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>
    <div class="header">
        <h2>🎉 Candidatura Aprovada!</h2>
    </div>
    <div class="content">
        <p>Olá <strong>{{name}}</strong>,</p>
        
        <p>Temos o prazer de informar que sua candidatura para 
           <strong>{{vacancy_title}}</strong> foi <strong>aprovada</strong>!</p>
        
        <p>Você será notificado em breve sobre a alocação aos júris.</p>
        
        <p>
            <a href="{{app_url}}/availability" class="button">
                Ver Minhas Candidaturas
            </a>
        </p>
        
        <p>Atenciosamente,<br>
        <strong>Comissão de Exames de Admissão</strong></p>
    </div>
</body>
</html>
```

**Cron Job:**
```bash
# Enviar emails pendentes a cada 5 minutos
*/5 * * * * /usr/bin/php /caminho/app/Cron/send_emails.php >> /caminho/storage/logs/emails.log 2>&1
```

---

### **4. Limite de Recandidaturas** 🔢
**Prioridade**: ⭐⭐ Média  
**Impacto**: Prevenção de Spam

#### Descrição
Limitar o número de vezes que um vigilante pode recandidatar-se à mesma vaga, evitando ciclos infinitos de cancelar/recandidatar.

#### Funcionalidades
- Máximo de **3 recandidaturas** por vaga por vigilante
- Contador visível na interface
- Mensagem clara quando limite atingido
- Coordenador pode resetar o contador (caso especial)

#### Implementação
**Alteração na Tabela:**
```sql
ALTER TABLE vacancy_applications 
ADD COLUMN reapply_count INT DEFAULT 0 AFTER updated_at,
ADD INDEX idx_reapply_count (reapply_count);
```

**Lógica de Validação:**
```php
// AvailabilityController.php
public function reapply(Request $request)
{
    // ... código existente ...
    
    // Nova validação
    if ($application['reapply_count'] >= 3) {
        Flash::add('error', 'Você atingiu o limite de 3 recandidaturas para esta vaga.');
        redirect('/availability');
    }
    
    // Incrementar contador
    $applicationModel->update($applicationId, [
        'status' => 'pendente',
        'reapply_count' => $application['reapply_count'] + 1,
        'applied_at' => now(),
        // ...
    ]);
}
```

**Exemplo de UI:**
```
┌─────────────────────────────────────┐
│ 📋 Exames 2025                      │
│ [Cancelada ⚫]                      │
├─────────────────────────────────────┤
│ Você se candidatou 2 vezes          │
│ Restam: 1 recandidatura             │
│                                     │
│ [Recandidatar-me]                   │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ 📋 Exames 2025                      │
│ [Cancelada ⚫]                      │
├─────────────────────────────────────┤
│ ⚠️ Limite de Recandidaturas         │
│                                     │
│ Você atingiu o limite de 3          │
│ recandidaturas para esta vaga.      │
│                                     │
│ Entre em contato com a coordenação  │
│ para casos especiais.               │
└─────────────────────────────────────┘
```

---

### **5. Dashboard de Candidaturas (Coordenador)** 📊
**Prioridade**: ⭐⭐ Média  
**Impacto**: Gestão e Análise

#### Descrição
Painel visual com estatísticas e métricas das candidaturas.

#### Funcionalidades
- Gráficos de candidaturas por status
- Taxa de aprovação/rejeição
- Tempo médio de revisão
- Top vigilantes mais ativos
- Exportação de relatórios

#### Implementação
**Novo Controller:**
```php
// app/Controllers/ApplicationDashboardController.php
class ApplicationDashboardController extends Controller
{
    public function index()
    {
        // Estatísticas gerais
        $stats = [
            'total' => 120,
            'pendentes' => 15,
            'aprovadas' => 85,
            'rejeitadas' => 12,
            'canceladas' => 8,
        ];
        
        // Taxa de aprovação
        $approvalRate = 85 / (85 + 12) * 100; // 87.6%
        
        // Tempo médio de revisão
        $avgReviewTime = '2.3 dias';
        
        // ...
    }
}
```

**Exemplo de UI:**
```
┌─────────────────────────────────────────────────────┐
│ Dashboard de Candidaturas                           │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ╭───────────╮  ╭───────────╮  ╭───────────╮      │
│  │   120     │  │    15     │  │   85      │      │
│  │  Total    │  │ Pendentes │  │ Aprovadas │      │
│  ╰───────────╯  ╰───────────╯  ╰───────────╯      │
│                                                     │
│  Taxa de Aprovação: 87.6% ████████████████░░       │
│  Tempo Médio Revisão: 2.3 dias                     │
│                                                     │
│  ┌─ Candidaturas por Dia ─────────────────┐        │
│  │      ▁▃▄▆█▆▄▃▁                         │        │
│  │     1 2 3 4 5 6 7 8 9 10               │        │
│  └────────────────────────────────────────┘        │
│                                                     │
│  ┌─ Status Distribution ──────────────────┐        │
│  │  Aprovadas:  70% ██████████████████     │        │
│  │  Pendentes:  12% ████                   │        │
│  │  Rejeitadas: 10% ███                    │        │
│  │  Canceladas:  8% ███                    │        │
│  └────────────────────────────────────────┘        │
│                                                     │
│  [Exportar Relatório] [Ver Detalhes]              │
└─────────────────────────────────────────────────────┘
```

---

### **6. Pré-visualização de Vagas** 👁️
**Prioridade**: ⭐ Baixa  
**Impacto**: Experiência do Usuário

#### Descrição
Modal com detalhes completos da vaga antes de se candidatar.

#### Funcionalidades
- Modal com descrição detalhada
- Requisitos e critérios
- Datas e horários
- Número de vagas disponíveis
- Botão "Candidatar-me" direto no modal

#### Implementação
**Exemplo de UI:**
```javascript
// public/js/vacancy-preview.js
function showVacancyPreview(vacancyId) {
    fetch(`/api/vacancies/${vacancyId}`)
        .then(res => res.json())
        .then(data => {
            // Exibir modal com detalhes
            openModal('vacancy-preview', data);
        });
}
```

**Modal:**
```html
<div id="modal-vacancy-preview" class="modal">
    <div class="modal-content">
        <h2>Exames de Admissão 2025</h2>
        
        <section>
            <h3>Descrição</h3>
            <p>Vigilantes necessários para fiscalização...</p>
        </section>
        
        <section>
            <h3>Requisitos</h3>
            <ul>
                <li>✅ Perfil completo</li>
                <li>✅ Disponibilidade nas datas</li>
                <li>✅ Experiência em vigilância (preferencial)</li>
            </ul>
        </section>
        
        <section>
            <h3>Informações</h3>
            <ul>
                <li>📅 Prazo: 15/10/2025 23:59</li>
                <li>👥 Vagas: 50</li>
                <li>📝 Candidatos: 32</li>
            </ul>
        </section>
        
        <div class="modal-actions">
            <button onclick="closeModal()">Fechar</button>
            <button onclick="apply()">Candidatar-me</button>
        </div>
    </div>
</div>
```

---

## 📐 Arquitetura das Melhorias

### **Diagrama de Componentes**

```
┌─────────────────────────────────────────────────────┐
│                  FRONTEND (Views)                   │
├─────────────────────────────────────────────────────┤
│ • availability/index.php    (vigilante)             │
│ • applications/index.php    (coordenador)           │
│ • applications/history.php  (novo)                  │
│ • applications/dashboard.php (novo)                 │
└──────────────────────┬──────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│              CONTROLLERS                            │
├─────────────────────────────────────────────────────┤
│ • AvailabilityController     (existente)            │
│ • ApplicationReviewController (existente)           │
│ • ApplicationDashboardController (novo)             │
└──────────────────────┬──────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│                  SERVICES                           │
├─────────────────────────────────────────────────────┤
│ • EmailNotificationService   (novo)                 │
│ • ApplicationHistoryService  (novo)                 │
│ • ApplicationStatsService    (novo)                 │
└──────────────────────┬──────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│                   MODELS                            │
├─────────────────────────────────────────────────────┤
│ • VacancyApplication         (existente + extend)   │
│ • ApplicationStatusHistory   (novo)                 │
│ • EmailNotification          (novo)                 │
└──────────────────────┬──────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│                   DATABASE                          │
├─────────────────────────────────────────────────────┤
│ • vacancy_applications       (+ novos campos)       │
│ • application_status_history (nova tabela)          │
│ • email_notifications        (nova tabela)          │
└─────────────────────────────────────────────────────┘
```

---

## 🗄️ Migrações de Banco de Dados

### **Migration 1: Histórico de Status**
```sql
-- migrations_v2.5_history.sql
CREATE TABLE application_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    old_status ENUM('pendente', 'aprovada', 'rejeitada', 'cancelada') NULL,
    new_status ENUM('pendente', 'aprovada', 'rejeitada', 'cancelada') NOT NULL,
    changed_by INT NULL,
    changed_at DATETIME NOT NULL,
    reason TEXT NULL,
    metadata JSON NULL,
    FOREIGN KEY (application_id) REFERENCES vacancy_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_application (application_id),
    INDEX idx_date (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger para registrar mudanças automaticamente
DELIMITER $$
CREATE TRIGGER trg_application_status_history
AFTER UPDATE ON vacancy_applications
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO application_status_history 
            (application_id, old_status, new_status, changed_by, changed_at)
        VALUES 
            (NEW.id, OLD.status, NEW.status, NEW.reviewed_by, NOW());
    END IF;
END$$
DELIMITER ;
```

### **Migration 2: Motivos de Rejeição e Limite**
```sql
-- migrations_v2.5_rejection_reasons.sql
ALTER TABLE vacancy_applications 
ADD COLUMN rejection_reason TEXT NULL AFTER reviewed_by,
ADD COLUMN reapply_count INT DEFAULT 0 AFTER updated_at,
ADD INDEX idx_reapply_count (reapply_count);
```

### **Migration 3: Notificações por Email**
```sql
-- migrations_v2.5_email_notifications.sql
CREATE TABLE email_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at DATETIME NULL,
    error_message TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📅 Cronograma de Implementação

### **Fase 1: Alta Prioridade** (5-7 dias)
- ✅ Motivos de rejeição visíveis
- ✅ Histórico de status
- ✅ Notificações por email (básico)

### **Fase 2: Média Prioridade** (3-5 dias)
- ✅ Limite de recandidaturas
- ✅ Dashboard de candidaturas

### **Fase 3: Baixa Prioridade** (2-3 dias)
- ✅ Pré-visualização de vagas
- ✅ Refinamentos de UI/UX

**Total Estimado**: 10-15 dias de desenvolvimento

---

## 🧪 Plano de Testes

### **1. Histórico de Status**
- [ ] Criar candidatura → verificar registro inicial
- [ ] Aprovar → verificar transição no histórico
- [ ] Cancelar → verificar registro com justificativa
- [ ] Recandidatar → verificar novo ciclo

### **2. Motivos de Rejeição**
- [ ] Rejeitar com motivo → verificar armazenamento
- [ ] Vigilante visualiza motivo
- [ ] Email com motivo enviado

### **3. Notificações**
- [ ] Aprovação → email enviado
- [ ] Rejeição → email com motivo
- [ ] Prazo próximo → email 48h antes
- [ ] Fila de emails processada pelo cron

### **4. Limite de Recandidaturas**
- [ ] Primeira recandidatura → contador = 1
- [ ] Segunda recandidatura → contador = 2
- [ ] Terceira recandidatura → contador = 3
- [ ] Quarta tentativa → bloqueada

### **5. Dashboard**
- [ ] Estatísticas calculadas corretamente
- [ ] Gráficos renderizam
- [ ] Exportação funciona

---

## 📊 Métricas de Sucesso

### **KPIs a Monitorar:**
1. **Taxa de Aprovação**: > 70%
2. **Tempo Médio de Revisão**: < 3 dias
3. **Taxa de Recandidatura**: < 15%
4. **Emails Entregues**: > 95%
5. **Satisfação dos Vigilantes**: Feedback positivo

---

## 💰 Custo-Benefício

### **Custos:**
- 🕒 Desenvolvimento: 10-15 dias
- 📧 Serviço de Email: ~$10-20/mês (ex: SendGrid, Mailgun)
- 🗄️ Armazenamento adicional: Mínimo

### **Benefícios:**
- ✅ Transparência total (histórico)
- ✅ Comunicação proativa (emails)
- ✅ Prevenção de abusos (limites)
- ✅ Melhor tomada de decisão (dashboard)
- ✅ Experiência do usuário superior

**ROI**: Alto - Melhorias impactam diretamente na eficiência operacional

---

## 🚀 Próximos Passos

### **Para Implementação Imediata:**
1. ✅ Aprovar proposta
2. ✅ Priorizar funcionalidades
3. ✅ Executar Fase 1 (alta prioridade)
4. ✅ Testar em ambiente de desenvolvimento
5. ✅ Deploy em produção
6. ✅ Coletar feedback
7. ✅ Iterar e melhorar

### **Comandos de Instalação (após aprovação):**
```bash
# Executar migrações
php scripts/install_v2.5_improvements.php

# Verificar instalação
php scripts/verify_v2.5_system.php

# Configurar cron de emails
php scripts/setup_email_cron.php
```

---

## 📞 Contato e Suporte

Para dúvidas ou sugestões sobre esta proposta:
- **Documentação**: `PROPOSTA_MELHORIAS_CANDIDATURAS.md`
- **Issues**: Criar issue no repositório
- **Testes**: Seguir `COMO_TESTAR.txt`

---

**🎯 Objetivo Final**: Sistema de candidaturas de vigilantes **classe mundial** com auditoria completa, comunicação proativa e gestão inteligente.

---

_Proposta elaborada em 11/10/2025_
