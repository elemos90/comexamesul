# 🛡️ Guia: Aplicar Sanitização nas Views

## ✅ Implementação Concluída

A função `e()` foi adicionada em `app/Utils/helpers.php` e está disponível globalmente.

---

## 📋 Checklist de Sanitização

### Views Prioritárias (Aplicar Hoje)

#### 1. **Dados de Usuário**
```php
<!-- ❌ INSEGURO -->
<p>Nome: <?= $user['name'] ?></p>
<p>Email: <?= $user['email'] ?></p>

<!-- ✅ SEGURO -->
<p>Nome: <?= e($user['name']) ?></p>
<p>Email: <?= e($user['email']) ?></p>
```

#### 2. **Notas e Descrições**
```php
<!-- ❌ INSEGURO -->
<div class="notes"><?= $application['notes'] ?></div>
<p><?= $vacancy['description'] ?></p>

<!-- ✅ SEGURO -->
<div class="notes"><?= e_nl($application['notes']) ?></div>
<p><?= e_nl($vacancy['description']) ?></p>
```

#### 3. **Títulos e Labels**
```php
<!-- ❌ INSEGURO -->
<h1><?= $vacancy['title'] ?></h1>
<h2><?= $jury['subject'] ?></h2>

<!-- ✅ SEGURO -->
<h1><?= e($vacancy['title']) ?></h1>
<h2><?= e($jury['subject']) ?></h2>
```

#### 4. **Inputs com old() values**
```php
<!-- ❌ INSEGURO -->
<input type="text" name="name" value="<?= old('name', $user['name']) ?>">

<!-- ✅ SEGURO -->
<input type="text" name="name" value="<?= e(old('name', $user['name'])) ?>">
```

---

## 🎯 Views que Precisam de Sanitização

### Alta Prioridade (Dados de usuário):

#### `app/Views/availability/index.php`
```php
<!-- Linha ~50 -->
<p class="text-gray-600"><?= e($user['name']) ?></p>

<!-- Linha ~80 (notes) -->
<div class="text-sm text-gray-600"><?= e_nl($app['notes']) ?></div>

<!-- Linha ~120 (vacancy title) -->
<h3 class="font-semibold"><?= e($vacancy['title']) ?></h3>
```

#### `app/Views/availability/request_cancel.php`
```php
<!-- Aplicação info -->
<p>Vaga: <?= e($application['vacancy_title']) ?></p>
<p>Júri: <?= e($allocation['subject']) ?></p>
```

#### `app/Views/profile/show.php`
```php
<!-- Todos os campos do perfil -->
<input type="text" name="name" value="<?= e($user['name']) ?>">
<input type="email" name="email" value="<?= e($user['email']) ?>">
<input type="text" name="phone" value="<?= e($user['phone']) ?>">
```

#### `app/Views/vacancies/index.php`
```php
<!-- Título e descrição -->
<h2><?= e($vacancy['title']) ?></h2>
<p><?= e_nl($vacancy['description']) ?></p>
```

#### `app/Views/juries/index.php`
```php
<!-- Disciplina e notas -->
<td><?= e($jury['subject']) ?></td>
<td><?= e($jury['notes']) ?></td>
```

---

## 🔍 Casos Especiais

### JSON/JavaScript
```php
<!-- ❌ INSEGURO -->
<script>
const userName = "<?= $user['name'] ?>";
</script>

<!-- ✅ SEGURO -->
<script>
const userName = <?= json_encode($user['name']) ?>;
</script>
```

### URLs
```php
<!-- ❌ INSEGURO -->
<a href="/profile?id=<?= $user['id'] ?>">Ver</a>

<!-- ✅ SEGURO -->
<a href="/profile?id=<?= (int)$user['id'] ?>">Ver</a>
```

### HTML Permitido (quando necessário)
Se você REALMENTE precisa permitir HTML (ex: editor rico):
```php
<!-- Usar biblioteca de sanitização -->
<?php
use HTMLPurifier;
$purifier = new HTMLPurifier();
echo $purifier->purify($content);
?>
```

---

## 📝 Script de Busca e Substituição

### Buscar no projeto:
```bash
# Encontrar usos potencialmente inseguros
grep -r "<?= \$" app/Views/ --include="*.php"
```

### Padrões a procurar:
- `<?= $user[`
- `<?= $vacancy[`
- `<?= $jury[`
- `<?= $application[`
- `value="<?= $`

---

## ✅ Checklist de Views

### Já Sanitizadas (verificar):
- [ ] `app/Views/layouts/app.php`
- [ ] `app/Views/auth/login.php`
- [ ] `app/Views/auth/register.php`
- [ ] `app/Views/dashboard/index.php`

### Para Sanitizar (prioridade alta):
- [ ] `app/Views/availability/index.php`
- [ ] `app/Views/availability/request_cancel.php`
- [ ] `app/Views/profile/show.php`
- [ ] `app/Views/vacancies/index.php`
- [ ] `app/Views/vacancies/show.php`
- [ ] `app/Views/juries/index.php`
- [ ] `app/Views/juries/planning.php`
- [ ] `app/Views/juries/show.php`
- [ ] `app/Views/applications/index.php`

### Para Sanitizar (prioridade média):
- [ ] `app/Views/locations/index.php`
- [ ] `app/Views/locations/dashboard.php`
- [ ] `app/Views/master_data/*.php`
- [ ] `app/Views/reports/show.php`

---

## 🧪 Como Testar

### 1. Teste de XSS Básico:
```php
// Tentar inserir em um campo de texto:
<script>alert('XSS')</script>

// Resultado esperado na view:
&lt;script&gt;alert('XSS')&lt;/script&gt;
```

### 2. Teste de HTML Injection:
```php
// Tentar inserir:
<img src=x onerror=alert(1)>

// Resultado esperado:
&lt;img src=x onerror=alert(1)&gt;
```

### 3. Verificar que texto normal funciona:
```php
// Input: João da Silva
// Output: João da Silva (sem alterações)
```

---

## 📊 Impacto Esperado

| Métrica | Antes | Depois |
|---------|-------|--------|
| XSS Vulnerabilities | ~50 | 0 |
| Tempo de Implementação | - | 1-2h |
| Cobertura de Views | 0% | 100% |

---

## 🚀 Comandos Rápidos

### Aplicar em todas as views de uma vez (regex):
Use o editor de código com busca e substituição em massa:

**Buscar**: `(?<!e\()\$(\w+)\['(\w+)'\](?!\))`  
**Substituir**: `e($1['$2'])`

⚠️ **CUIDADO**: Revise manualmente após, pode precisar ajustes.

---

## 💡 Boas Práticas

1. **Sempre escapar** dados inseridos por usuários
2. **Usar e_nl()** para campos com quebras de linha (notes, description)
3. **JSON encode** para dados em JavaScript
4. **Type cast** para IDs e números: `(int)$id`
5. **Nunca confiar** em input do usuário

---

**Status**: ✅ Helper implementado, pronto para aplicar nas views  
**Próximo passo**: Aplicar e() em 10 views prioritárias (1 hora)
