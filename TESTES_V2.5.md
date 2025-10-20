# 🧪 Guia de Testes - Melhorias v2.5

**Data**: 11/10/2025  
**Sistema**: Candidaturas de Vigilantes - Testes Completos

---

## 📋 Preparação para Testes

### **Ambiente de Teste:**
- Sistema v2.5 instalado
- Banco de dados com dados de teste
- 3 usuários: coordenador, membro, vigilante
- Pelo menos 2 vagas abertas

### **Criar Dados de Teste:**

```bash
# Executar seed (se disponível)
php scripts/seed_test_data.php
```

---

## 🔍 Testes Funcionais

### **Teste 1: Histórico de Status**

**Objetivo**: Verificar se o histórico de mudanças é registrado corretamente

**Passos:**
1. Login como **vigilante**
2. Candidate-se a uma vaga aberta
3. ✅ Status inicial: **Pendente**
4. Logout

5. Login como **coordenador**
6. Vá para `/applications`
7. Aprove a candidatura
8. Clique em "Histórico" ou vá para `/applications/{id}/history`
9. ✅ Verificar timeline:
   - Evento 1: Criação (Pendente)
   - Evento 2: Aprovação (Pendente → Aprovada)
   - Datas e responsáveis corretos

**Resultado Esperado:**
- ✅ Timeline completa visível
- ✅ Todos os eventos registrados
- ✅ Informações de quem alterou

---

### **Teste 2: Motivos de Rejeição**

**Objetivo**: Verificar se motivos de rejeição são salvos e exibidos

**Passos:**
1. Login como **coordenador**
2. Vá para `/applications`
3. Selecione uma vaga com candidatura pendente
4. Clique em "Rejeitar"
5. No campo "Motivo da Rejeição", digite:
   ```
   Perfil incompleto. Falta preencher NUIT e NIB.
   Por favor, complete e recandidature-se.
   ```
6. Clique em "Rejeitar Candidatura"
7. ✅ Mensagem de sucesso
8. Logout

9. Login como **vigilante** (da candidatura rejeitada)
10. Vá para `/availability`
11. ✅ Verificar que a candidatura aparece como "Rejeitada"
12. ✅ Verificar que o motivo está visível

**Resultado Esperado:**
- ✅ Motivo salvo corretamente
- ✅ Vigilante vê o motivo completo
- ✅ Email de rejeição enviado (verificar fila)

---

### **Teste 3: Notificações por Email**

**Objetivo**: Verificar sistema de emails

#### **3.1: Email de Aprovação**

**Passos:**
1. Login como **coordenador**
2. Aprove uma candidatura
3. Execute o cron de emails:
   ```bash
   php app/Cron/send_emails.php
   ```
4. ✅ Verificar saída:
   ```
   Enviando para: vigilante@email.com (Candidatura Aprovada)... ✅ OK
   ```
5. ✅ Verificar banco de dados:
   ```sql
   SELECT * FROM email_notifications 
   WHERE type = 'application_approved' 
   ORDER BY created_at DESC LIMIT 1;
   ```

**Resultado Esperado:**
- ✅ Email criado na fila
- ✅ Status: 'sent'
- ✅ Email recebido pelo vigilante

#### **3.2: Email de Rejeição**

**Passos:**
1. Rejeite uma candidatura com motivo
2. Execute: `php app/Cron/send_emails.php`
3. ✅ Email enviado com motivo incluído

#### **3.3: Email de Prazo Próximo**

**Passos:**
1. Crie uma vaga com deadline em 24h
2. Execute: `php scripts/check_deadlines_cron.php`
3. ✅ Vigilantes notificados

**Resultado Esperado:**
- ✅ Emails na fila
- ✅ Taxa de sucesso > 95%

---

### **Teste 4: Limite de Recandidaturas**

**Objetivo**: Verificar limite de 3 recandidaturas

**Passos:**
1. Login como **vigilante**
2. Candidate-se a uma vaga → Status: Pendente
3. Cancele a candidatura → Status: Cancelada
4. ✅ Contador: 0/3

5. Recandidature-se (1ª vez)
6. ✅ Status: Pendente
7. ✅ Contador: 1/3
8. Cancele novamente

9. Recandidature-se (2ª vez)
10. ✅ Status: Pendente
11. ✅ Contador: 2/3
12. Cancele novamente

13. Recandidature-se (3ª vez)
14. ✅ Status: Pendente
15. ✅ Contador: 3/3
16. Cancele novamente

17. **Tente recandidatar-se (4ª vez)**
18. ✅ **ERRO**: "Você atingiu o limite de 3 recandidaturas para esta vaga. Entre em contato com a coordenação."
19. ✅ Botão "Recandidatar-se" desabilitado ou oculto

**Resultado Esperado:**
- ✅ Limite respeitado
- ✅ Mensagem clara ao usuário
- ✅ Contador visível

---

### **Teste 5: Dashboard de Candidaturas**

**Objetivo**: Verificar estatísticas e gráficos

**Passos:**
1. Login como **coordenador**
2. Acesse `/applications/dashboard`
3. ✅ Verificar Cards Principais:
   - Total de Candidaturas
   - Pendentes
   - Aprovadas
   - Tempo Médio de Revisão

4. ✅ Verificar Gráfico "Distribuição por Status":
   - Barras coloridas
   - Percentagens corretas

5. ✅ Verificar "Candidaturas por Dia":
   - Últimos 30 dias
   - Valores corretos

6. ✅ Verificar "Top 10 Vigilantes":
   - Lista ordenada
   - Dados corretos (total, aprovadas, rejeitadas)

7. ✅ Verificar "Estatísticas de Email":
   - Total, Pendentes, Enviados, Falhados
   - Taxa de sucesso

8. Clicar em "Exportar Relatório"
9. ✅ Download de arquivo CSV
10. ✅ Abrir no Excel e verificar dados

**Resultado Esperado:**
- ✅ Todas as estatísticas visíveis
- ✅ Gráficos renderizados
- ✅ Export funciona

---

### **Teste 6: Integração com Sistema Existente**

**Objetivo**: Garantir que funcionalidades antigas ainda funcionam

#### **6.1: Candidatura Nova**

**Passos:**
1. Login como **vigilante**
2. Vá para `/availability`
3. Candidate-se a uma vaga
4. ✅ Candidatura criada
5. ✅ Email de notificação para coordenadores (verificar fila)

#### **6.2: Cancelamento com Justificativa**

**Passos:**
1. Tenha candidatura aprovada E alocada a júris
2. Clique "Cancelar"
3. ✅ Formulário de justificativa aparece
4. Preencha justificativa (20+ caracteres)
5. Envie
6. ✅ Solicitação pendente criada
7. ✅ Coordenadores notificados por email

#### **6.3: Aprovação/Rejeição em Massa**

**Passos:**
1. Login como **coordenador**
2. Tenha 5+ candidaturas pendentes
3. Clique "Aprovar Todas"
4. Confirme
5. ✅ Todas aprovadas
6. ✅ 5+ emails na fila

---

## 🔒 Testes de Segurança

### **Teste 7: Permissões de Acesso**

**Objetivo**: Garantir que apenas usuários autorizados acessam funcionalidades

**Passos:**

#### **7.1: Dashboard (Somente Coordenador/Membro)**

1. Login como **vigilante**
2. Tente acessar `/applications/dashboard`
3. ✅ **BLOQUEADO**: Redirecionado com erro

#### **7.2: Histórico (Somente Coordenador/Membro)**

1. Login como **vigilante**
2. Tente acessar `/applications/1/history`
3. ✅ **BLOQUEADO**: Acesso negado

#### **7.3: Recandidatura (Somente Próprio Vigilante)**

1. Login como **vigilante A**
2. Tente recandidatar candidatura do **vigilante B**:
   ```
   POST /applications/999/reapply
   ```
3. ✅ **BLOQUEADO**: "Candidatura não encontrada"

**Resultado Esperado:**
- ✅ Todas as rotas protegidas
- ✅ Middlewares funcionando
- ✅ Mensagens claras de erro

---

### **Teste 8: Validações de Dados**

**Objetivo**: Garantir que dados inválidos são rejeitados

#### **8.1: Motivo de Rejeição Muito Curto**

**Passos:**
1. Tente rejeitar com motivo de 5 caracteres
2. ✅ **ERRO**: Validação front-end/back-end

#### **8.2: Recandidatura com Perfil Incompleto**

**Passos:**
1. Remova NIB do perfil
2. Tente recandidatar
3. ✅ **ERRO**: "Complete seu perfil antes de se candidatar"

#### **8.3: Recandidatura com Vaga Fechada**

**Passos:**
1. Coordenador fecha a vaga
2. Vigilante tenta recandidatar
3. ✅ **ERRO**: "Esta vaga não está mais aberta"

**Resultado Esperado:**
- ✅ Todas as validações funcionam
- ✅ Mensagens claras

---

## 📊 Testes de Performance

### **Teste 9: Carga de Dados**

**Objetivo**: Verificar performance com muitos dados

**Preparação:**
```sql
-- Criar 1000 candidaturas de teste
INSERT INTO vacancy_applications (vacancy_id, vigilante_id, status, applied_at, created_at, updated_at)
SELECT 1, id, 'pendente', NOW(), NOW(), NOW()
FROM users WHERE role = 'vigilante' LIMIT 1000;
```

**Testes:**

1. **Dashboard**: `/applications/dashboard`
   - ✅ Carrega em < 2 segundos
   - ✅ Gráficos renderizam corretamente

2. **Lista de Candidaturas**: `/applications?vacancy=1`
   - ✅ Carrega em < 3 segundos
   - ✅ Paginação funciona (se implementada)

3. **Histórico**: `/applications/1/history`
   - ✅ Carrega em < 1 segundo

4. **Export CSV**: `/applications/export`
   - ✅ Gera arquivo em < 5 segundos
   - ✅ Arquivo completo e correto

**Resultado Esperado:**
- ✅ Performance aceitável
- ✅ Sem erros de timeout

---

### **Teste 10: Envio de Emails em Massa**

**Objetivo**: Verificar envio de muitos emails

**Preparação:**
```sql
-- Criar 100 emails pendentes
INSERT INTO email_notifications (user_id, type, subject, body, status, created_at)
SELECT id, 'test', 'Teste', 'Corpo do email', 'pending', NOW()
FROM users LIMIT 100;
```

**Teste:**
```bash
php app/Cron/send_emails.php
```

**Resultado Esperado:**
- ✅ 50 emails enviados (limite por execução)
- ✅ Restantes ficam na fila
- ✅ Próxima execução envia os demais
- ✅ Taxa de sucesso > 95%

---

## 🔄 Testes de Integração

### **Teste 11: Fluxo Completo**

**Objetivo**: Testar o fluxo de ponta a ponta

**Cenário:**
Um vigilante se candidata, é rejeitado, corrige perfil, recandidatura e é aprovado.

**Passos:**

1. **Vigilante se candidata**
   - Login como vigilante
   - Candidate-se a vaga
   - ✅ Status: Pendente
   - ✅ Histórico: 1 evento

2. **Coordenador rejeita com motivo**
   - Login como coordenador
   - Rejeite com motivo: "Falta NIB"
   - ✅ Status: Rejeitada
   - ✅ Histórico: 2 eventos
   - ✅ Email enviado

3. **Vigilante corrige perfil**
   - Login como vigilante
   - Vá para `/profile`
   - Adicione NIB
   - ✅ Perfil completo

4. **Vigilante recandidatura**
   - Vá para `/availability`
   - Clique "Recandidatar-me"
   - ✅ Status: Pendente
   - ✅ Contador: 1/3
   - ✅ Histórico: 3 eventos

5. **Coordenador aprova**
   - Login como coordenador
   - Aprove a candidatura
   - ✅ Status: Aprovada
   - ✅ Histórico: 4 eventos
   - ✅ Email enviado

6. **Verificar Dashboard**
   - Acesse `/applications/dashboard`
   - ✅ Estatísticas atualizadas
   - ✅ Vigilante aparece em "Top Ativos"

**Resultado Esperado:**
- ✅ Fluxo completo funciona
- ✅ Todos os eventos registrados
- ✅ Emails enviados
- ✅ Dashboard atualizado

---

## 📝 Checklist de Testes

### **Funcionalidades Core:**
- [ ] Histórico de status funciona
- [ ] Motivos de rejeição salvos e exibidos
- [ ] Notificações por email funcionam
- [ ] Limite de recandidaturas respeitado
- [ ] Dashboard exibe dados corretos
- [ ] Export CSV funciona

### **Segurança:**
- [ ] Permissões de acesso corretas
- [ ] Validações de dados funcionam
- [ ] CSRF tokens validados
- [ ] Ownership verificado

### **Performance:**
- [ ] Dashboard carrega rapidamente
- [ ] Listas grandes funcionam
- [ ] Emails enviados eficientemente

### **Integração:**
- [ ] Sistema existente não quebrou
- [ ] Fluxo completo funciona
- [ ] Dados consistentes

---

## 🐛 Bugs Conhecidos e Soluções

### **Bug: Emails não enviam**

**Diagnóstico:**
```bash
php -r "var_dump(mail('teste@exemplo.com', 'Teste', 'Corpo'));"
```

**Soluções:**
1. Configurar sendmail no php.ini
2. Usar serviço SMTP externo
3. Verificar logs de erro do PHP

### **Bug: Dashboard vazio**

**Diagnóstico:**
```sql
SELECT * FROM v_application_stats;
```

**Solução:**
```bash
# Recriar views
php scripts/install_v2.5_improvements.php
```

---

## 📊 Relatório de Testes

**Template:**

```
Data: ___/___/_____
Testador: _____________________

RESULTADOS:
✅ Testes Passados: __ / __
❌ Testes Falhados: __ / __
⚠️  Testes com Avisos: __ / __

DETALHES:
[Listar testes que falharam e motivos]

CONCLUSÃO:
[ ] Sistema APROVADO para produção
[ ] Sistema REPROVADO - requer correções
[ ] Sistema APROVADO COM RESSALVAS

Assinatura: _____________________
```

---

## ✅ Aprovação Final

Após todos os testes:

1. ✅ Todos os testes passaram
2. ✅ Performance aceitável
3. ✅ Segurança verificada
4. ✅ Documentação completa
5. ✅ Backup realizado

**Sistema v2.5 APROVADO para PRODUÇÃO! 🎉**
