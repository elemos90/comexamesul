# ⚡ Próximos Passos Imediatos

**Guia rápido para implementar as melhorias mais críticas**

---

## 🎯 Quick Wins (2 Horas)

### 1. ✅ Adicionar Índices de Performance (10 minutos)

```bash
# Melhorar queries em 40-60%
mysql -u root -p comexamesul < scripts/add_performance_indexes.sql
```

**Resultado**: Queries de júris, vigilantes e candidaturas muito mais rápidas.

---

### 2. ✅ Implementar Cache (30 minutos)

O arquivo `app/Services/StatsCacheService.php` já foi criado.

**Aplicar no DashboardController**:

```php
// app/Controllers/DashboardController.php
use App\Services\StatsCacheService;

public function index(): string
{
    $cache = new StatsCacheService();
    
    $stats = $cache->remember('dashboard_stats', function() {
        // Suas queries de estatísticas aqui
        return [
            'total_juries' => /* ... */,
            'total_vigilantes' => /* ... */,
        ];
    }, 300); // 5 minutos
    
    return $this->view('dashboard/index', ['stats' => $stats]);
}
```

**Limpar cache ao alterar dados**:

```php
// JuryController@store, update, delete
$cache = new StatsCacheService();
$cache->forget('dashboard_stats');
```

---

### 3. ✅ A Função de Escape XSS Já Existe! (5 minutos)

A função `e()` já está em `app/Utils/helpers.php`. Agora você precisa aplicá-la nas views.

**Procurar e substituir em todas as views**:

```bash
# Buscar usos inseguros (exemplo)
grep -r "<?= \$" app/Views/
```

**Substituir padrão**:
```php
<!-- ANTES (inseguro) -->
<p><?= $user['notes'] ?></p>
<span><?= $vigilante['name'] ?></span>

<!-- DEPOIS (seguro) -->
<p><?= e($user['notes']) ?></p>
<span><?= e($vigilante['name']) ?></span>
```

**Arquivos prioritários**:
- `app/Views/juries/planning.php` ✅ Já aberto no seu editor
- `app/Views/juries/*.php`
- `app/Views/vacancies/*.php`
- `app/Views/applications/*.php`

---

### 4. ✅ Headers de Segurança (5 minutos)

```php
// bootstrap.php - ADICIONAR no final (antes da linha 58)

// Headers de segurança
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

if (env('APP_ENV') === 'production') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}
```

---

## 🧪 Configurar Testes (30 minutos)

### 1. Instalar PHPUnit

```bash
composer require --dev phpunit/phpunit
```

### 2. Configurar

```bash
# Copiar arquivo de configuração
cp phpunit.xml.example phpunit.xml
```

### 3. Executar Testes

```bash
# Executar todos os testes
./vendor/bin/phpunit

# Executar teste específico
./vendor/bin/phpunit tests/Unit/Utils/ValidatorTest.php

# Com cobertura (requer xdebug)
./vendor/bin/phpunit --coverage-html coverage
```

O arquivo `tests/Unit/Utils/ValidatorTest.php` já foi criado com 20+ testes completos!

---

## 🔴 Correções Críticas de Segurança (4 Horas)

### 1. Eliminar SELECT * nos Models

**Arquivos prioritários**:

#### `app/Models/User.php`

```php
// ADICIONAR propriedade
protected array $selectColumns = [
    'id', 'name', 'email', 'role', 'phone', 'nuit', 'nib',
    'avatar_url', 'available_for_vigilance', 'supervisor_eligible',
    'experiencia_supervisao', 'created_at', 'updated_at'
    // NUNCA: password_hash, remember_token
];

// REFATORAR métodos
public function findByEmail(string $email): ?array
{
    $columns = implode(', ', $this->selectColumns);
    $sql = "SELECT {$columns} FROM users WHERE email = :email LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['email' => $email]);
    return $stmt->fetch() ?: null;
}
```

Repetir para todos os métodos que usam `SELECT *`.

---

### 2. Validação de MIME Type Real

**Arquivo**: `app/Utils/FileUploader.php`

```php
public function validate(array $file): bool
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $this->error = 'Erro no upload';
        return false;
    }
    
    // ✅ ADICIONAR: Validar MIME type real
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf'
    ];
    
    if (!in_array($realMime, $allowedMimes, true)) {
        $this->error = 'Tipo de arquivo não permitido';
        return false;
    }
    
    // ✅ Validar tamanho
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        $this->error = 'Arquivo muito grande';
        return false;
    }
    
    return true;
}
```

---

## 📊 Verificar Melhorias

### Performance

```bash
# Antes: Tempo médio de carregamento
# Medir no navegador (DevTools > Network)

# Depois de aplicar cache + índices
# Comparar tempo de resposta
```

### Segurança

```bash
# Testar XSS
# Tentar inserir: <script>alert('XSS')</script>
# Nas notas de júri, perfil, etc.

# Verificar se é escapado corretamente
```

### Cache

```php
// Verificar informações do cache
$cache = new StatsCacheService();
$info = $cache->info();
print_r($info);
```

---

## 📝 Checklist de Implementação

### Hoje (2 horas)
- [ ] Executar `add_performance_indexes.sql`
- [ ] Adicionar headers de segurança em `bootstrap.php`
- [ ] Implementar cache no DashboardController
- [ ] Aplicar função `e()` em `juries/planning.php`

### Esta Semana (8 horas)
- [ ] Aplicar `e()` em todas as views (~50 arquivos)
- [ ] Refatorar `User.php` para remover `SELECT *`
- [ ] Melhorar validação de uploads
- [ ] Configurar PHPUnit

### Próximas 2 Semanas (20 horas)
- [ ] Refatorar todos os Models (SELECT *)
- [ ] Resolver N+1 queries em JuryController
- [ ] Escrever testes para Utils e Models
- [ ] Extrair Services de JuryController

---

## 🆘 Resolução de Problemas

### Erro ao executar SQL de índices

```bash
# Verificar se base de dados existe
mysql -u root -p -e "SHOW DATABASES LIKE 'comexamesul';"

# Se não existir, criar primeiro
mysql -u root -p -e "CREATE DATABASE comexamesul CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Cache não funciona

```bash
# Verificar permissões da pasta
mkdir -p storage/cache/stats
chmod -R 775 storage/cache/
```

### PHPUnit não encontrado

```bash
# Reinstalar dependências
composer install --dev

# Verificar instalação
./vendor/bin/phpunit --version
```

---

## 📚 Documentos de Referência

- **Análise Completa**: `ANALISE_SUGESTOES_MELHORIA.md`
- **Melhorias Detalhadas**: `MELHORIAS_PROPOSTAS_2025.md`
- **README Principal**: `README.md`

---

## 💬 Suporte

Para dúvidas sobre implementação:
1. Consultar documentação em `/docs`
2. Revisar código de exemplo nos arquivos criados
3. Testar em ambiente local antes de produção

---

**Data**: Outubro 2025  
**Status**: Pronto para implementação  
**Tempo estimado total**: 2 horas (quick wins) + 55 horas (melhorias completas)
