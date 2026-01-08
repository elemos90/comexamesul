# 📊 Estados de Vagas - Explicação e Proposta

**Data**: 13 de Outubro de 2025  
**Análise**: Estados disponíveis vs Estados em uso

---

## 🔍 Situação Atual

### Estados Definidos no Banco de Dados:
```sql
status ENUM('aberta','fechada','encerrada') NOT NULL DEFAULT 'aberta'
```

### Estados Realmente Usados no Sistema:
✅ **'aberta'** - Em uso ativo  
✅ **'fechada'** - Em uso (auto-close quando deadline expira)  
❌ **'encerrada'** - Definido mas **NÃO USADO**

---

## ❌ Problema Identificado

Atualmente **NÃO HÁ DISTINÇÃO CLARA** entre "fechada" e "encerrada":
- Ambos bloqueiam alterações de vigilantes
- O sistema só usa "aberta" e "fechada"
- "Encerrada" está disponível no dropdown mas sem propósito específico

---

## 💡 Proposta: Definição Clara dos Estados

### 1. **ABERTA** 🟢
**Quando usar**: Vaga aceitando candidaturas

**Características**:
- ✅ Vigilantes podem se candidatar
- ✅ Vigilantes podem cancelar candidatura
- ✅ Vigilantes podem recandidatar-se
- ✅ Coordenador pode aprovar/rejeitar candidaturas
- ✅ Coordenador pode editar vaga
- ✅ Coordenador pode criar júris

**Transição Automática**:
```php
// Sistema fecha automaticamente quando prazo expira
if (deadline_at < now()) {
    status = 'fechada';
}
```

---

### 2. **FECHADA** 🟡
**Quando usar**: Prazo expirado OU coordenador fechou manualmente

**Características**:
- ❌ Vigilantes **NÃO podem** se candidatar
- ❌ Vigilantes **NÃO podem** cancelar candidaturas
- ❌ Vigilantes **NÃO podem** recandidatar-se
- ✅ Coordenador **pode** aprovar/rejeitar candidaturas pendentes
- ✅ Coordenador **pode** criar júris
- ✅ Coordenador **pode** alocar vigilantes
- ✅ Coordenador **pode** editar vaga
- ⚠️ Coordenador **pode** reabrir (mudar status para 'aberta')

**Como chegar aqui**:
1. **Automático**: Prazo de deadline_at expira
2. **Manual**: Coordenador clica "Fechar" na listagem

**Objetivo**: Fase de **revisão e organização** antes dos exames

---

### 3. **ENCERRADA** 🔴 (PROPOSTA)
**Quando usar**: Após exames realizados, vaga arquivada

**Características**:
- ❌ Vigilantes **NÃO podem** fazer nenhuma ação
- ❌ Coordenador **NÃO pode** aprovar/rejeitar candidaturas
- ❌ Coordenador **NÃO pode** criar júris
- ❌ Coordenador **NÃO pode** alocar vigilantes
- ⚠️ Coordenador **pode** visualizar (modo leitura)
- ⚠️ Coordenador **pode** editar metadados (apenas título/descrição)
- ❌ Coordenador **NÃO pode** reabrir

**Como chegar aqui**:
- **Manual**: Coordenador marca como "Encerrada" após exames concluídos

**Objetivo**: **Arquivo histórico** - dados preservados mas bloqueados

---

## 📊 Fluxo de Estados Proposto

```
CRIAÇÃO
   ↓
[ABERTA] ────────────────────────┐
   ↓                              │
   │ Prazo expira                 │ Manual
   │ OU Manual "Fechar"           │ Reabrir
   ↓                              │
[FECHADA] ◄──────────────────────┘
   ↓
   │ Manual "Encerrar"
   │ (após exames)
   ↓
[ENCERRADA]
   │
   └─► (Bloqueado permanentemente)
```

---

## 🎯 Casos de Uso

### Caso 1: Vaga Normal
```
1. Criar vaga "Física I" → ABERTA
2. Vigilantes se candidatam
3. Prazo expira → FECHADA (automático)
4. Coordenador aloca vigilantes a júris
5. Exames realizados
6. Coordenador marca → ENCERRADA
```

### Caso 2: Coordenador Fecha Antes
```
1. Criar vaga "Matemática II" → ABERTA
2. Vigilantes se candidatam
3. Vagas suficientes preenchidas
4. Coordenador clica "Fechar" → FECHADA (manual)
5. Organiza júris
6. Exames realizados → ENCERRADA
```

### Caso 3: Reabrir Vaga
```
1. Vaga "Química I" → FECHADA
2. Muitos vigilantes desistiram
3. Coordenador reabre → ABERTA
4. Novas candidaturas aceitas
5. Prazo expira → FECHADA
6. Após exames → ENCERRADA
```

---

## 🛠️ Implementação Necessária

### 1. Adicionar Método `closeExpired()` com Estado Fechada ✅
**Já implementado**:
```php
public function closeExpired(): int
{
    $sql = "UPDATE {$this->table} SET status = 'fechada', updated_at = :updated 
            WHERE status = 'aberta' AND deadline_at < :now";
    // ...
}
```

### 2. Botão "Encerrar Vaga" (A IMPLEMENTAR)
**Localização**: `app/Views/vacancies/index.php`

```php
<?php if ($vacancy['status'] === 'fechada'): ?>
    <form method="POST" action="/vacancies/<?= $vacancy['id'] ?>/finalize" class="inline">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <button type="submit" 
                class="px-3 py-1.5 text-xs font-medium bg-purple-100 text-purple-700 rounded hover:bg-purple-200"
                onclick="return confirm('Encerrar vaga permanentemente? Esta ação marca a vaga como concluída e bloqueia futuras alterações.')">
            Encerrar
        </button>
    </form>
<?php endif; ?>
```

### 3. Rota e Método no Controller (A IMPLEMENTAR)
**Arquivo**: `app/Controllers/VacancyController.php`

```php
public function finalize(Request $request)
{
    $id = (int) $request->param('id');
    $model = new ExamVacancy();
    $vacancy = $model->find($id);
    
    if (!$vacancy) {
        Flash::add('error', 'Vaga não encontrada.');
        redirect('/vacancies');
    }
    
    if ($vacancy['status'] !== 'fechada') {
        Flash::add('error', 'Apenas vagas fechadas podem ser encerradas.');
        redirect('/vacancies');
    }
    
    $model->update($id, [
        'status' => 'encerrada',
        'updated_at' => now()
    ]);
    
    ActivityLogger::log('vacancies', $id, 'finalize');
    Flash::add('success', 'Vaga encerrada e arquivada com sucesso.');
    redirect('/vacancies');
}
```

### 4. Rota em `web.php` (A IMPLEMENTAR)
```php
$router->post('/vacancies/{id}/finalize', 'VacancyController@finalize', [
    'AuthMiddleware', 
    'RoleMiddleware:coordenador', 
    'CsrfMiddleware'
]);
```

### 5. Bloquear Ações em Vagas Encerradas (A IMPLEMENTAR)

**Em `AvailabilityController.php`**:
```php
if ($vacancy['status'] === 'encerrada') {
    Flash::add('error', 'Esta vaga está encerrada e não aceita mais alterações.');
    redirect('/availability');
}
```

**Em `VacancyController.php`**:
```php
public function update(Request $request)
{
    // ...
    if ($vacancy['status'] === 'encerrada') {
        Flash::add('error', 'Vagas encerradas não podem ser editadas.');
        redirect('/vacancies');
    }
    // ...
}
```

---

## 🎨 Cores Visuais Propostas

### Badges de Status:
```php
$statusColors = [
    'aberta' => 'bg-green-100 text-green-700 border-green-300',      // Verde
    'fechada' => 'bg-yellow-100 text-yellow-700 border-yellow-300',  // Amarelo
    'encerrada' => 'bg-purple-100 text-purple-700 border-purple-300' // Roxo
];
```

### Botões de Ação:
- **Fechar**: Amarelo (`bg-yellow-100 text-yellow-700`)
- **Encerrar**: Roxo (`bg-purple-100 text-purple-700`)
- **Reabrir**: Verde (`bg-green-100 text-green-700`)

---

## 📋 Checklist de Implementação

### Já Implementado:
- [x] Estados definidos no banco de dados
- [x] Auto-close para 'fechada' quando prazo expira
- [x] Bloqueio de alterações de vigilantes em vagas fechadas

### A Implementar:
- [ ] Botão "Encerrar" para vagas fechadas
- [ ] Método `finalize()` no VacancyController
- [ ] Rota `/vacancies/{id}/finalize`
- [ ] Bloqueio de ações em vagas encerradas
- [ ] Badge roxo para vagas encerradas
- [ ] Validação: apenas vagas fechadas podem ser encerradas
- [ ] Impedir edição de vagas encerradas (exceto visualização)
- [ ] Documentação de quando usar cada estado

---

## 🔐 Regras de Transição

| De → Para | Permitido? | Quem? | Como? |
|-----------|-----------|-------|-------|
| Aberta → Fechada | ✅ Sim | Sistema/Coordenador | Auto (deadline) / Manual (botão) |
| Aberta → Encerrada | ❌ Não | - | Precisa passar por Fechada |
| Fechada → Aberta | ✅ Sim | Coordenador | Editar vaga (dropdown) |
| Fechada → Encerrada | ✅ Sim | Coordenador | Botão "Encerrar" |
| Encerrada → Aberta | ❌ Não | - | Bloqueado permanentemente |
| Encerrada → Fechada | ❌ Não | - | Bloqueado permanentemente |

---

## 💡 Recomendações

### Para Coordenadores:

1. **Durante Candidaturas**:
   - Manter vaga como **ABERTA**
   - Deixar deadline expirar automaticamente OU fechar manualmente

2. **Durante Organização**:
   - Vaga em **FECHADA**
   - Aprovar/rejeitar candidaturas
   - Criar júris
   - Alocar vigilantes

3. **Após Exames Realizados**:
   - Marcar como **ENCERRADA**
   - Preserva histórico
   - Bloqueia alterações futuras
   - Facilita auditoria

### Benefícios:

✅ **Clareza**: Estado reflete fase real do processo  
✅ **Segurança**: Dados históricos protegidos  
✅ **Auditoria**: Fácil identificar vagas concluídas  
✅ **Organização**: Separação visual clara  
✅ **Prevenção**: Evita edições acidentais em vagas antigas  

---

## 📊 Comparação Final

| Aspecto | Aberta | Fechada | Encerrada |
|---------|--------|---------|-----------|
| **Candidaturas** | ✅ Aceita | ❌ Bloqueada | ❌ Bloqueada |
| **Aprovações** | ✅ Sim | ✅ Sim | ❌ Não |
| **Criar Júris** | ✅ Sim | ✅ Sim | ❌ Não |
| **Alocar Vigilantes** | ✅ Sim | ✅ Sim | ❌ Não |
| **Editar Vaga** | ✅ Sim | ✅ Sim | ⚠️ Limitado |
| **Reabrir** | N/A | ✅ Sim | ❌ Não |
| **Fase** | Recrutamento | Organização | Arquivo |
| **Cor** | 🟢 Verde | 🟡 Amarelo | 🟣 Roxo |

---

**Status Atual**: ⚠️ "Encerrada" definida mas não implementada  
**Recomendação**: Implementar distinção clara conforme proposta acima  
**Prioridade**: Média (melhoria de organização, não crítico)
