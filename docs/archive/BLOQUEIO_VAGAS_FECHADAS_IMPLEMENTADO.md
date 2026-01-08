# ✅ Bloqueio de Alterações em Vagas Fechadas - IMPLEMENTADO

**Data**: 13 de Outubro de 2025  
**Funcionalidade**: Proteção contra alterações em candidaturas após vaga ser encerrada pelo coordenador

---

## 🎯 Objetivo

Garantir que vigilantes não possam alterar (cancelar ou recandidatar-se) suas candidaturas após o coordenador encerrar/fechar uma vaga.

---

## ✅ Implementação Realizada

### 1. **Validação no Backend (Controller)**

**Arquivo**: `app/Controllers/AvailabilityController.php`

#### Métodos Protegidos:

##### ✅ `requestCancel()` - Linha 139-146
Bloqueio ao solicitar cancelamento de candidatura aprovada:
```php
// Verificar se a vaga ainda está aberta
$vacancyModel = new ExamVacancy();
$vacancy = $vacancyModel->find($application['vacancy_id']);

if (!$vacancy || $vacancy['status'] !== 'aberta') {
    Flash::add('error', 'Esta vaga ja foi encerrada. Nao e possivel alterar a candidatura.');
    redirect('/availability');
}
```

##### ✅ `submitCancelRequest()` - Linha 215-222
Bloqueio ao submeter justificativa de cancelamento:
```php
// Verificar se a vaga ainda está aberta
$vacancyModel = new ExamVacancy();
$vacancy = $vacancyModel->find($application['vacancy_id']);

if (!$vacancy || $vacancy['status'] !== 'aberta') {
    Flash::add('error', 'Esta vaga ja foi encerrada. Nao e possivel alterar a candidatura.');
    redirect('/availability');
}
```

##### ✅ `cancelDirect()` - Linha 465-472
Bloqueio ao cancelar candidatura pendente diretamente:
```php
// Verificar se a vaga ainda está aberta
$vacancyModel = new ExamVacancy();
$vacancy = $vacancyModel->find($application['vacancy_id']);

if (!$vacancy || $vacancy['status'] !== 'aberta') {
    Flash::add('error', 'Esta vaga ja foi encerrada. Nao e possivel alterar a candidatura.');
    redirect('/availability');
}
```

##### ✅ `reapply()` - Linha 511-519
Bloqueio ao tentar recandidatar-se:
```php
// Verificar se a vaga ainda está aberta
$vacancyModel = new ExamVacancy();
$vacancy = $vacancyModel->find($application['vacancy_id']);

if (!$vacancy || $vacancy['status'] !== 'aberta') {
    Flash::add('error', 'Esta vaga ja foi encerrada pelo coordenador. Nao e possivel recandidatar-se.');
    redirect('/availability');
    return;
}
```

---

### 2. **Proteção Visual no Frontend (View)**

**Arquivo**: `app/Views/availability/index.php`

#### Alterações Implementadas:

##### ✅ Lista "Minhas Candidaturas" - Linha 84-118
```php
<?php if ($app['vacancy_status'] === 'fechada'): ?>
    <!-- Vaga fechada - não permitir alterações -->
    <span class="px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-500 rounded cursor-not-allowed" 
          title="Vaga encerrada pelo coordenador">
        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </span>
<?php else: ?>
    <!-- Botões de ação normais (Cancelar, Recandidatar-se) -->
<?php endif; ?>
```

##### ✅ Cards de Vagas Abertas - Candidaturas Pendentes - Linha 207-221
```php
<?php if ($vacancy['status'] !== 'fechada'): ?>
    <form method="POST" action="/applications/<?= $myApplication['id'] ?>/cancel-direct" 
          class="mt-3" onsubmit="return confirm('Deseja cancelar esta candidatura?');">
        <button type="submit" class="w-full px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
            Cancelar Candidatura
        </button>
    </form>
<?php else: ?>
    <div class="mt-3 px-3 py-2 bg-gray-100 text-gray-600 text-xs text-center rounded">
        <svg class="w-4 h-4 inline mr-1">...</svg>
        Vaga encerrada
    </div>
<?php endif; ?>
```

##### ✅ Cards de Vagas - Candidaturas Rejeitadas - Linha 233-247
```php
<?php if (!$expired && $vacancy['status'] !== 'fechada'): ?>
    <form method="POST" action="/applications/<?= $myApplication['id'] ?>/reapply">
        <button type="submit" class="w-full px-3 py-2 text-sm font-medium bg-primary-600 text-white rounded">
            Recandidatar-me
        </button>
    </form>
<?php elseif ($vacancy['status'] === 'fechada'): ?>
    <div class="px-3 py-2 bg-gray-100 text-gray-600 text-xs text-center rounded">
        <svg class="w-4 h-4 inline mr-1">...</svg>
        Vaga encerrada
    </div>
<?php endif; ?>
```

##### ✅ Cards de Vagas - Candidaturas Canceladas - Linha 260-274
```php
<?php if (!$expired && $vacancy['status'] !== 'fechada'): ?>
    <form method="POST" action="/applications/<?= $myApplication['id'] ?>/reapply">
        <button type="submit" class="w-full px-3 py-2 text-sm font-medium bg-primary-600 text-white rounded">
            Recandidatar-me
        </button>
    </form>
<?php elseif ($vacancy['status'] === 'fechada'): ?>
    <div class="px-3 py-2 bg-gray-100 text-gray-600 text-xs text-center rounded">
        <svg class="w-4 h-4 inline mr-1">...</svg>
        Vaga encerrada
    </div>
<?php endif; ?>
```

---

## 🔒 Cenários Protegidos

### 1. **Candidatura Pendente**
- ❌ Não pode cancelar se vaga fechada
- ✅ Mostra ícone de cadeado e mensagem "Vaga encerrada"

### 2. **Candidatura Aprovada**
- ❌ Não pode solicitar cancelamento se vaga fechada
- ✅ Mostra ícone de cadeado no lugar do botão "Cancelar"

### 3. **Candidatura Rejeitada**
- ❌ Não pode recandidatar-se se vaga fechada
- ✅ Mostra mensagem "Vaga encerrada" no lugar do botão "Recandidatar-me"

### 4. **Candidatura Cancelada**
- ❌ Não pode recandidatar-se se vaga fechada
- ✅ Mostra mensagem "Vaga encerrada" no lugar do botão "Recandidatar-me"

---

## 🛡️ Camadas de Segurança

### Camada 1: **Interface (View)**
- Esconde botões de ação quando `$app['vacancy_status'] === 'fechada'`
- Mostra ícone de cadeado e mensagem informativa
- Impede cliques acidentais

### Camada 2: **Backend (Controller)**
- Valida status da vaga antes de executar qualquer ação
- Retorna mensagem de erro clara
- Redireciona para página de candidaturas

### Camada 3: **Dados (Model)**
- Query `getByVigilante()` já inclui `vacancy_status` via JOIN
- Linha 31 em `VacancyApplication.php`:
  ```php
  v.status as vacancy_status
  ```

---

## 📊 Fluxo de Proteção

```
Vigilante tenta alterar candidatura
         ↓
1. View verifica vacancy_status
   ├─ Se 'fechada' → Mostra ícone cadeado (sem botão)
   └─ Se 'aberta' → Mostra botão de ação
         ↓
2. Se botão clicado (via URL direto ou hack)
         ↓
3. Controller verifica status da vaga no BD
   ├─ Se 'fechada' → Flash error + redirect
   └─ Se 'aberta' → Permite ação
```

---

## 🧪 Como Testar

### Teste 1: Candidatura Pendente com Vaga Fechada
1. Vigilante se candidata a vaga
2. Coordenador fecha a vaga
3. Vigilante acessa "Minhas Candidaturas"
4. **Esperado**: Ícone de cadeado visível, botão "Cancelar" escondido
5. **Se tentar via URL**: `/applications/{id}/cancel-direct` → Erro + redirect

### Teste 2: Recandidatura com Vaga Fechada
1. Vigilante tem candidatura rejeitada
2. Coordenador fecha a vaga
3. Vigilante acessa "Minhas Candidaturas"
4. **Esperado**: Mensagem "Vaga encerrada", botão "Recandidatar-me" escondido
5. **Se tentar via URL**: `/applications/{id}/reapply` → Erro + redirect

### Teste 3: Cancelamento de Aprovada com Vaga Fechada
1. Vigilante tem candidatura aprovada
2. Coordenador fecha a vaga
3. Vigilante tenta cancelar
4. **Esperado**: Ícone de cadeado no lugar do botão "Cancelar"
5. **Se tentar via URL**: `/availability/{id}/cancel` → Erro + redirect

---

## 🎨 Elementos Visuais

### Ícone de Cadeado (Vaga Fechada)
```html
<svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
</svg>
```

### Cores e Estilo
- **Background**: `bg-gray-100`
- **Texto**: `text-gray-500` / `text-gray-600`
- **Cursor**: `cursor-not-allowed`
- **Tooltip**: "Vaga encerrada pelo coordenador"

---

## 📝 Mensagens de Erro

1. **Cancelamento bloqueado**:
   > "Esta vaga ja foi encerrada. Nao e possivel alterar a candidatura."

2. **Recandidatura bloqueada**:
   > "Esta vaga ja foi encerrada pelo coordenador. Nao e possivel recandidatar-se."

---

## ✅ Checklist de Implementação

- [x] Validação em `requestCancel()`
- [x] Validação em `submitCancelRequest()`
- [x] Validação em `cancelDirect()`
- [x] Validação em `reapply()`
- [x] UI: Esconder botão "Cancelar" (pendente)
- [x] UI: Esconder botão "Cancelar" (aprovada)
- [x] UI: Esconder botão "Recandidatar-me" (rejeitada)
- [x] UI: Esconder botão "Recandidatar-me" (cancelada)
- [x] UI: Mostrar ícone de cadeado
- [x] UI: Mostrar mensagem "Vaga encerrada"
- [x] Mensagens de erro claras
- [x] Documentação completa

---

## 🎯 Resultado Final

✅ **Segurança Total**: Vigilantes não podem alterar candidaturas de vagas fechadas  
✅ **UX Claro**: Interface mostra visualmente quando vaga está encerrada  
✅ **Proteção Dupla**: Validação em view E controller  
✅ **Mensagens Claras**: Feedback compreensível para o usuário  

---

**Status**: ✅ **IMPLEMENTADO E TESTADO**  
**Compatibilidade**: Funciona com todas as candidaturas (pendente, aprovada, rejeitada, cancelada)  
**Impacto**: Zero alterações possíveis após coordenador fechar a vaga
