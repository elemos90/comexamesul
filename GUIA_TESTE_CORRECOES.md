# 🧪 Guia de Testes - Correções Críticas

**Data:** 12/10/2025  
**Objetivo:** Validar as 6 correções implementadas

---

## 🚀 Pré-requisitos

### 1. Verificar XAMPP
```powershell
# Verificar se Apache está rodando
Get-Service -Name "*apache*" | Select-Object Name, Status

# Verificar se MySQL está rodando
Get-Service -Name "*mysql*" | Select-Object Name, Status
```

### 2. Acessar o Sistema
```
URL: http://localhost/juries/planning-by-vacancy
Login: coordenador@unilicungo.ac.mz
Senha: password
```

---

## ✅ Teste 1: Lógica no Controller (Sem Models na View)

### Objetivo
Verificar que a página carrega sem instanciar models na view

### Passos
1. Acesse: `http://localhost/juries/planning-by-vacancy`
2. ✅ Página deve carregar normalmente
3. ✅ Vagas devem aparecer com estatísticas (se houver júris)
4. ✅ Não deve haver erros no console do navegador

### Como Verificar no Código
```php
// ❌ ANTES (View com lógica)
// app/Views/juries/planning_by_vacancy.php linha 37-44
<?php
$juryModel = new \App\Models\Jury(); // ❌ Não deve existir mais
?>

// ✅ AGORA (Dados vêm do controller)
<?php foreach ($vacancies as $vacancy): ?>
    <?php if ($vacancy['has_juries'] && $vacancy['stats']): ?>
        <!-- Dados já prontos -->
    <?php endif; ?>
<?php endforeach; ?>
```

### Resultado Esperado
✅ Página carrega rápido  
✅ Sem erros de PHP  
✅ Estatísticas aparecem nos cards das vagas

---

## ✅ Teste 2: Loading States

### Objetivo
Verificar spinner animado durante ações assíncronas

### Passos

#### 2.1 Teste: Auto-Alocar Todos
1. Acesse uma vaga com júris criados
2. Clique **"🤖 Alocar Automaticamente"**
3. ✅ Botão deve mostrar spinner + texto "Alocando..."
4. ✅ Botão deve ficar disabled (opaco, não clicável)
5. ✅ Após conclusão, botão volta ao normal

**O que observar:**
```
Antes: [🤖 Alocar Automaticamente]
Durante: [⏳ Alocando...] (spinner girando, botão opaco)
Depois: [🤖 Alocar Automaticamente] (volta ao normal)
```

#### 2.2 Teste: Auto-completar Júri Individual
1. Em um júri incompleto, clique **"Auto-completar"**
2. ✅ Botão deve mostrar "Auto-completando..."
3. ✅ Spinner deve aparecer

#### 2.3 Teste: Remover Vigilante
1. Remova um vigilante de um júri
2. ✅ Loading deve aparecer durante a operação

### Resultado Esperado
✅ Spinner SVG animado visível  
✅ Botão fica disabled durante operação  
✅ Mensagem contextual ("Alocando...", "Removendo...")  
✅ Previne cliques duplos

---

## ✅ Teste 3: Toasts (Substituição de Alerts)

### Objetivo
Verificar que alerts foram substituídos por toasts não-bloqueantes

### Passos

#### 3.1 Teste: Toast de Sucesso
1. Auto-aloque vigilantes em um júri
2. ✅ **Toast verde** deve aparecer no canto superior direito
3. ✅ Deve ter **progress bar** animada
4. ✅ Deve mostrar detalhes (ex: "Total alocados: 4 | Júris completos: 2/2")
5. ✅ Deve desaparecer automaticamente após ~5 segundos
6. ✅ Você deve conseguir continuar navegando (não-bloqueante)

**Aparência esperada:**
```
┌──────────────────────────────────────┐
│ ✅ Alocação Concluída          [×]   │
│ ─────────────────────────────────    │ ← Progress bar
│ Alocação bem-sucedida!               │
│ Total alocados: 4 | Júris: 2/2       │
└──────────────────────────────────────┘
```

#### 3.2 Teste: Toast de Erro
1. Tente uma ação inválida (ex: adicionar vigilante sem selecionar)
2. ✅ **Toast vermelho** deve aparecer
3. ✅ Deve permanecer ~10 segundos (erros ficam mais tempo)
4. ✅ Botão de fechar [×] deve funcionar

#### 3.3 Comparação com Antes
```
❌ ANTES:
- Alert bloqueante
- Pausa toda interação
- Sem formatação
- Não mostra detalhes

✅ AGORA:
- Toast não-bloqueante
- Continua navegando
- Suporta HTML/formatação
- Progress bar visual
```

### Resultado Esperado
✅ Nenhum `alert()` do navegador aparece  
✅ Toasts aparecem no canto superior direito  
✅ Fecham automaticamente ou manualmente  
✅ Suportam HTML (detalhes formatados)

---

## ✅ Teste 4: Atualização AJAX (Sem Reload)

### Objetivo
Verificar atualização parcial das estatísticas sem reload completo

### Passos

#### 4.1 Observar Estatísticas Antes
1. Acesse gestão de júris de uma vaga
2. **Anote os números** na barra de estatísticas:
   ```
   Total Júris: 3
   Vigilantes Necessários: 6
   Alocados: 4
   Taxa Ocupação: 67%
   ```

#### 4.2 Adicionar Vigilante
1. Clique **"+ Adicionar Vigilante Manualmente"**
2. Selecione um vigilante
3. Clique **"Adicionar"**
4. ✅ Toast verde aparece
5. ✅ **Aguarde 1 segundo**
6. ✅ Números nas estatísticas devem **piscar** (animação pulse)
7. ✅ "Alocados" deve mudar de 4 → 5
8. ✅ "Taxa Ocupação" deve recalcular

#### 4.3 Verificar Scroll Position
1. Role a página até o final
2. Adicione um vigilante
3. ✅ Após atualização AJAX, **scroll deve permanecer** onde estava
4. ⚠️ Após 2 segundos, página recarrega (reload completo)

### Código HTML Verificar
```html
<!-- Cards devem ter data-stat -->
<div class="bg-white p-4" data-stat="total_allocated">
    <div class="text-2xl font-bold">4</div> <!-- Atualiza dinamicamente -->
</div>
```

### Resultado Esperado
✅ Números atualizam sem reload (primeiros 2s)  
✅ Animação de pulse visível  
✅ Scroll position mantida temporariamente  
✅ Reload completo só após 2 segundos

---

## ✅ Teste 5: Validação de Horários em Tempo Real

### Objetivo
Validar horários instantaneamente (antes do submit)

### Passos

#### 5.1 Teste: Horário Inválido (Fim < Início)
1. Clique **"Criar Júris"** em uma vaga
2. Preencha:
   - Horário Início: `08:00`
   - Horário Fim: `07:30`
3. ✅ **Borda vermelha** deve aparecer no campo "Horário Fim"
4. ✅ Mensagem de erro vermelha abaixo: "❌ Horário de término deve ser maior que o de início"
5. ✅ Botão submit deve ficar **bloqueado** (HTML5 validation)

**Aparência esperada:**
```
┌─────────────────────────────────────┐
│ Horário Fim *                       │
│ ┌───────────────┐                   │
│ │ 07:30         │ ← Borda vermelha  │
│ └───────────────┘                   │
│ ❌ Horário de término deve ser...   │
└─────────────────────────────────────┘
```

#### 5.2 Teste: Duração Muito Curta
1. Horário Início: `08:00`
2. Horário Fim: `08:15` (15 minutos)
3. ✅ Aviso: "⚠️ Duração mínima recomendada: 30 minutos"
4. ✅ Borda vermelha
5. ⚠️ Permite submit (é só aviso)

#### 5.3 Teste: Duração Muito Longa
1. Horário Início: `08:00`
2. Horário Fim: `14:00` (6 horas)
3. ✅ Aviso: "⚠️ Duração muito longa (>4h). Verifique se está correto."

#### 5.4 Teste: Horário Válido
1. Horário Início: `08:00`
2. Horário Fim: `10:00` (2 horas)
3. ✅ **Borda verde** no campo
4. ✅ Mensagem verde: "✓ Duração: 120 minutos"
5. ✅ Submit liberado

### Validações Implementadas
| Condição | Duração | Resultado |
|----------|---------|-----------|
| Fim ≤ Início | - | ❌ Erro (bloqueia submit) |
| < 30 min | 0-29 min | ⚠️ Aviso (permite submit) |
| 30 min - 4h | 30-240 min | ✅ Válido (verde) |
| > 4h | 241+ min | ⚠️ Aviso (permite submit) |

### Resultado Esperado
✅ Feedback visual instantâneo (borda colorida)  
✅ Mensagens claras de erro/sucesso  
✅ Cálculo automático de duração  
✅ Previne horários impossíveis

---

## ✅ Teste 6: Otimização de Queries N+1

### Objetivo
Verificar redução drástica de queries ao banco

### Passos

#### 6.1 Abrir DevTools
1. Pressione `F12` no navegador
2. Vá na aba **"Network"** (Rede)
3. **Limpe** o log (ícone 🚫)

#### 6.2 Carregar Página
1. Acesse: `http://localhost/juries/planning-by-vacancy`
2. ✅ Observe o número de requisições
3. ✅ Deve haver **apenas 1-2 requisições** principais

#### 6.3 Verificar no Código (Opcional)
```sql
-- Query ANTES (N+1):
SELECT * FROM juries WHERE vacancy_id = 1;  -- Query 1
SELECT COUNT(*) FROM jury_vigilantes WHERE jury_id = 1; -- Query 2
SELECT COUNT(*) FROM jury_vigilantes WHERE jury_id = 2; -- Query 3
...
-- Total: 1 + N queries (N = número de júris)

-- Query DEPOIS (Otimizada):
SELECT 
    j.*,
    s.name AS supervisor_name,
    COUNT(DISTINCT jv.id) as vigilantes_allocated,
    CEIL(j.candidates_quota / 30) as required_vigilantes
FROM juries j
LEFT JOIN users s ON s.id = j.supervisor_id
LEFT JOIN jury_vigilantes jv ON jv.jury_id = j.id
WHERE j.vacancy_id = 1
GROUP BY j.id;
-- Total: 1 query apenas!
```

#### 6.4 Teste de Performance
```powershell
# Criar múltiplas vagas para testar escala
# (Fazer via interface ou importar dados)

# Cenário:
# - 5 vagas abertas
# - 8 júris por vaga = 40 júris total

# ANTES:
# Queries: 1 + (5 × 8) = 41 queries
# Tempo: ~3 segundos

# DEPOIS:
# Queries: 5 queries (1 por vaga)
# Tempo: ~0.5 segundos
```

### Ferramentas de Monitoramento

#### Opção 1: MySQL Query Log
```sql
-- Ativar log de queries (temporário)
SET GLOBAL general_log = 'ON';
SET GLOBAL general_log_file = 'C:/xampp/mysql/data/queries.log';

-- Carregar a página

-- Desativar log
SET GLOBAL general_log = 'OFF';

-- Ver arquivo: C:/xampp/mysql/data/queries.log
```

#### Opção 2: Chrome DevTools
1. Network tab
2. Filtrar por tipo: `XHR` ou `Fetch`
3. ✅ Deve haver POUCAS requisições AJAX

### Resultado Esperado
✅ Redução de 75-90% nas queries  
✅ Página carrega em < 1 segundo  
✅ Escalável para 100+ vagas  
✅ Single query com JOINs

---

## 📊 Resumo dos Testes

### Checklist Completo

| # | Teste | Passou? | Observações |
|---|-------|---------|-------------|
| 1️⃣ | Lógica no Controller | ☐ | Sem models na view |
| 2️⃣ | Loading States | ☐ | Spinner em todas ações |
| 3️⃣ | Toasts | ☐ | Sem alerts bloqueantes |
| 4️⃣ | AJAX Update | ☐ | Stats atualizam sem reload |
| 5️⃣ | Validação Tempo Real | ☐ | Horários validados |
| 6️⃣ | Queries Otimizadas | ☐ | Redução de queries |

### Critérios de Sucesso

✅ **PASSOU** se:
- Todos os 6 testes funcionam conforme esperado
- Sem erros no console do navegador
- UX visivelmente melhorada
- Performance notavelmente mais rápida

⚠️ **ATENÇÃO** se:
- Algum teste falha
- Erros aparecem no console
- Loading states não aparecem
- Toasts não funcionam

❌ **FALHOU** se:
- Página não carrega
- Erros de sintaxe PHP
- Funcionalidades quebradas

---

## 🐛 Troubleshooting

### Problema: Toasts Não Aparecem
**Solução:**
```html
<!-- Verificar se toastr.js está carregado -->
<!-- No layout: app/Views/layouts/main.php -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
```

### Problema: Validação Não Funciona
**Solução:**
```javascript
// Verificar no console do navegador:
console.log(document.getElementById('start_time')); // Não deve ser null
console.log(document.getElementById('end_time')); // Não deve ser null
```

### Problema: Loading States Não Aparecem
**Solução:**
```javascript
// Verificar função no console:
console.log(typeof showLoading); // Deve ser "function"
```

### Problema: Queries Ainda Lentas
**Solução:**
```php
// Verificar se método correto está sendo usado:
// app/Controllers/JuryController.php linha 1026

// ✅ CORRETO:
$juries = $juryModel->getByVacancyWithStats((int) $vacancy['id']);

// ❌ ERRADO:
$juries = $juryModel->getByVacancy((int) $vacancy['id']);
```

---

## 🎯 Próximos Passos Após Testes

### Se Todos os Testes Passarem ✅
1. Marcar como **PRONTO PARA PRODUÇÃO**
2. Implementar **Fase 2** (melhorias de UX)
3. Documentar para equipe

### Se Houver Falhas ❌
1. Anotar quais testes falharam
2. Reportar erros específicos
3. Ajustar código conforme necessário

---

**Boa sorte nos testes!** 🚀

Se encontrar problemas, anote:
- Qual teste falhou
- Mensagem de erro exata
- Screenshot se possível
