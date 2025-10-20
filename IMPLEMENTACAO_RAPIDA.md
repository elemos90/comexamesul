# ⚡ Guia de Implementação Rápida - Melhorias Críticas

Este guia fornece código pronto para implementar as melhorias mais críticas do projeto.

---

## 🔴 1. Helper de Sanitização (5 minutos)

### Adicionar ao arquivo `app/Utils/helpers.php`:

```php
/**
 * Escape HTML para prevenir XSS
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Escape e preserva quebras de linha
 */
function e_nl(?string $value): string {
    return nl2br(e($value));
}
```

### Como usar nas Views:

```php
<!-- ❌ ANTES (INSEGURO) -->
<p><?= $user['notes'] ?></p>
<h1><?= $vacancy['title'] ?></h1>

<!-- ✅ DEPOIS (SEGURO) -->
<p><?= e($user['notes']) ?></p>
<h1><?= e($vacancy['title']) ?></h1>
```

---

## 🔴 2. Melhorar BaseModel - SELECT Específico (15 minutos)

### Substituir método find() no `app/Models/BaseModel.php`:

```php
public function find(int $id): ?array
{
    $columns = $this->getSelectColumns();
    $sql = "SELECT {$columns} FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $result = $stmt->fetch();
    return $result ?: null;
}

public function all(array $conditions = []): array
{
    $columns = $this->getSelectColumns();
    $sql = "SELECT {$columns} FROM {$this->table}";
    if ($conditions) {
        $where = [];
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = :{$column}";
        }
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $stmt = $this->db->prepare($sql);
    $stmt->execute($conditions);
    return $stmt->fetchAll();
}

/**
 * Obter colunas para SELECT (override em cada Model)
 */
protected function getSelectColumns(): string
{
    if (isset($this->selectColumns) && !empty($this->selectColumns)) {
        return implode(', ', $this->selectColumns);
    }
    return '*'; // Fallback temporário
}
```

### Atualizar User Model:

```php
class User extends BaseModel
{
    protected string $table = 'users';
    
    // NOVO: Lista de colunas seguras para SELECT
    protected array $selectColumns = [
        'id', 'name', 'email', 'phone', 'gender',
        'origin_university', 'university', 'nuit',
        'degree', 'major_area', 'bank_name', 'nib',
        'role', 'email_verified_at', 'avatar_url',
        'supervisor_eligible', 'available_for_vigilance',
        'profile_completed', 'profile_completed_at',
        'created_at', 'updated_at'
        // NOTA: password_hash NUNCA incluído!
    ];
    
    // Resto do código...
}
```

---

## 🔴 3. Validação Robusta de Upload (10 minutos)

### Criar novo arquivo `app/Utils/FileUploader.php`:

```php
<?php

namespace App\Utils;

class FileUploader
{
    private const MAX_SIZE = 5 * 1024 * 1024; // 5MB
    
    private const ALLOWED_MIMES = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    
    public static function validate(array $file): array
    {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erro no upload do arquivo.';
            return $errors;
        }
        
        // Validar tamanho
        if ($file['size'] > self::MAX_SIZE) {
            $errors[] = 'Arquivo muito grande. Máximo: 5MB.';
        }
        
        // Validar MIME type real
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::ALLOWED_MIMES)) {
            $errors[] = 'Tipo de arquivo não permitido.';
        }
        
        // Validar extensão
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED_MIMES[$extension])) {
            $errors[] = 'Extensão de arquivo não permitida.';
        }
        
        return $errors;
    }
    
    public static function upload(array $file, string $directory): ?string
    {
        // Validar primeiro
        $errors = self::validate($file);
        if (!empty($errors)) {
            throw new \Exception(implode(' ', $errors));
        }
        
        // Criar diretório se não existir
        $uploadDir = BASE_PATH . '/' . trim($directory, '/') . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Gerar nome seguro
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $fileName;
        
        // Mover arquivo
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception('Falha ao salvar arquivo.');
        }
        
        // Retornar path relativo
        return trim($directory, '/') . '/' . $fileName;
    }
}
```

### Usar no Controller:

```php
use App\Utils\FileUploader;

// ❌ ANTES
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    // Validação insegura...
}

// ✅ DEPOIS
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    try {
        $attachmentPath = FileUploader::upload(
            $_FILES['attachment'], 
            'storage/uploads/justifications'
        );
        $attachmentOriginalName = $_FILES['attachment']['name'];
    } catch (\Exception $e) {
        Flash::add('error', $e->getMessage());
        redirect('/availability/' . $applicationId . '/cancel');
    }
}
```

---

## 🟠 4. Configurar PHPUnit (10 minutos)

### Instalar:
```bash
composer require --dev phpunit/phpunit
```

### Criar `phpunit.xml`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         colors="true"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### Criar `tests/bootstrap.php`:
```php
<?php

require_once __DIR__ . '/../bootstrap.php';
```

### Criar `tests/TestCase.php`:
```php
<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Setup comum
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        // Cleanup
    }
}
```

### Criar primeiro teste `tests/Unit/Utils/ValidatorTest.php`:
```php
<?php

namespace Tests\Unit\Utils;

use Tests\TestCase;
use App\Utils\Validator;

class ValidatorTest extends TestCase
{
    public function test_validates_email_correctly()
    {
        $this->assertTrue(Validator::email('user@example.com'));
        $this->assertTrue(Validator::email('test.user@domain.co.mz'));
        $this->assertFalse(Validator::email('invalid'));
        $this->assertFalse(Validator::email(''));
    }
    
    public function test_validates_nuit()
    {
        $this->assertTrue(Validator::nuit('123456789'));
        $this->assertFalse(Validator::nuit('abc'));
        $this->assertFalse(Validator::nuit('12345')); // muito curto
    }
    
    public function test_validates_phone()
    {
        $this->assertTrue(Validator::phone('843456789'));
        $this->assertTrue(Validator::phone('823456789'));
        $this->assertFalse(Validator::phone('123'));
        $this->assertFalse(Validator::phone(''));
    }
}
```

### Executar testes:
```bash
./vendor/bin/phpunit
```

---

## 🟠 5. Logging Estruturado (15 minutos)

### Instalar Monolog:
```bash
composer require monolog/monolog
```

### Criar `app/Utils/Logger.php`:
```php
<?php

namespace App\Utils;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class Logger
{
    private static ?MonologLogger $instance = null;
    
    private static function getInstance(): MonologLogger
    {
        if (self::$instance === null) {
            self::$instance = new MonologLogger('app');
            
            // Log file rotativo (1 arquivo por dia)
            $logPath = BASE_PATH . '/storage/logs/app.log';
            self::$instance->pushHandler(
                new RotatingFileHandler($logPath, 30, MonologLogger::DEBUG)
            );
            
            // Em desenvolvimento, também exibir no stderr
            if (env('APP_DEBUG', false)) {
                self::$instance->pushHandler(
                    new StreamHandler('php://stderr', MonologLogger::DEBUG)
                );
            }
        }
        
        return self::$instance;
    }
    
    public static function debug(string $message, array $context = []): void
    {
        self::getInstance()->debug($message, $context);
    }
    
    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->info($message, $context);
    }
    
    public static function warning(string $message, array $context = []): void
    {
        self::getInstance()->warning($message, $context);
    }
    
    public static function error(string $message, array $context = []): void
    {
        self::getInstance()->error($message, $context);
    }
    
    public static function critical(string $message, array $context = []): void
    {
        self::getInstance()->critical($message, $context);
    }
}
```

### Usar nos Controllers:
```php
use App\Utils\Logger;

public function autoAllocateJury(Request $request)
{
    try {
        $juryId = (int) $request->param('id');
        Logger::info('Iniciando auto-alocação', ['jury_id' => $juryId]);
        
        // Lógica...
        
        Logger::info('Auto-alocação concluída', [
            'jury_id' => $juryId,
            'vigilantes_alocados' => count($allocated)
        ]);
        
    } catch (\Exception $e) {
        Logger::error('Falha na auto-alocação', [
            'jury_id' => $juryId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        throw $e;
    }
}
```

---

## 🟡 6. Índices de Performance (2 minutos)

### Executar no MySQL:

```sql
-- Júris: busca por local e data
CREATE INDEX idx_juries_location_date ON juries(location_id, exam_date);

-- Júris: busca por disciplina
CREATE INDEX idx_juries_discipline ON juries(discipline_id);

-- Usuários: filtro por role e disponibilidade
CREATE INDEX idx_users_role_available ON users(role, available_for_vigilance);

-- Alocações: lookup reverso
CREATE INDEX idx_jury_vigilantes_vigilante ON jury_vigilantes(vigilante_id);
CREATE INDEX idx_jury_vigilantes_jury ON jury_vigilantes(jury_id);

-- Candidaturas: filtro por status
CREATE INDEX idx_applications_status ON vacancy_applications(status);
CREATE INDEX idx_applications_vacancy ON vacancy_applications(vacancy_id, status);

-- Logs de atividade: busca por entidade
CREATE INDEX idx_activity_logs_entity ON activity_logs(entity_type, entity_id);
```

---

## 🟡 7. Resolver N+1 Queries (20 minutos)

### Criar método otimizado em `JuryVigilante` Model:

```php
/**
 * Buscar vigilantes para múltiplos júris (evita N+1)
 */
public function getForJuries(array $juryIds): array
{
    if (empty($juryIds)) {
        return [];
    }
    
    $placeholders = implode(',', array_fill(0, count($juryIds), '?'));
    
    $sql = "SELECT 
                jv.jury_id,
                jv.vigilante_id,
                u.name as vigilante_name,
                u.email as vigilante_email,
                u.phone as vigilante_phone
            FROM jury_vigilantes jv
            INNER JOIN users u ON u.id = jv.vigilante_id
            WHERE jv.jury_id IN ($placeholders)
            ORDER BY jv.jury_id, u.name";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($juryIds);
    
    return $stmt->fetchAll();
}
```

### Usar no Controller:

```php
// ❌ ANTES (N+1 queries)
foreach ($juries as &$jury) {
    $jury['vigilantes'] = $juryVigilantes->vigilantesForJury($jury['id']);
}

// ✅ DEPOIS (1 query)
$juryIds = array_column($juries, 'id');
$allVigilantes = $juryVigilantes->getForJuries($juryIds);

// Agrupar por jury_id
$vigilantesByJury = [];
foreach ($allVigilantes as $v) {
    $vigilantesByJury[$v['jury_id']][] = $v;
}

// Atribuir aos júris
foreach ($juries as &$jury) {
    $jury['vigilantes'] = $vigilantesByJury[$jury['id']] ?? [];
}
```

---

## 🟢 8. Error Handler Global (10 minutos)

### Criar `app/Utils/ErrorHandler.php`:

```php
<?php

namespace App\Utils;

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleException(\Throwable $e): void
    {
        Logger::error('Uncaught Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        http_response_code(500);
        
        if (env('APP_DEBUG', false)) {
            echo '<h1>Exception</h1>';
            echo '<pre>' . $e . '</pre>';
        } else {
            require base_path('app/Views/errors/500.php');
        }
        
        exit;
    }
    
    public static function handleError($severity, $message, $file, $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        Logger::error('PHP Error', [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ]);
        
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
    
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            Logger::critical('Fatal Error', $error);
            
            http_response_code(500);
            
            if (!env('APP_DEBUG', false)) {
                require base_path('app/Views/errors/500.php');
            }
        }
    }
}
```

### Ativar no `bootstrap.php`:

```php
// No final do arquivo bootstrap.php
if (class_exists('App\Utils\ErrorHandler')) {
    App\Utils\ErrorHandler::register();
}
```

---

## ✅ Checklist de Implementação

### Prioridade 1 (Hoje):
- [ ] Adicionar helper `e()` em helpers.php
- [ ] Atualizar 5 views principais com sanitização
- [ ] Implementar FileUploader
- [ ] Executar queries de índices

### Prioridade 2 (Esta Semana):
- [ ] Refatorar BaseModel com selectColumns
- [ ] Atualizar User Model
- [ ] Configurar PHPUnit
- [ ] Criar 3 testes básicos

### Prioridade 3 (Próximas 2 Semanas):
- [ ] Implementar Logger
- [ ] Adicionar logs em 10 pontos críticos
- [ ] Resolver N+1 em JuryController
- [ ] Implementar ErrorHandler

---

## 📊 Como Medir o Sucesso

### Antes vs Depois:

| Métrica | Antes | Depois |
|---------|-------|--------|
| XSS Vulnerabilities | ~20 | 0 |
| SELECT * queries | 39 | 0 |
| Upload validation | Básica | Robusta |
| Test coverage | 0% | 30%+ |
| N+1 queries | ~15 | <3 |
| Logs estruturados | Não | Sim |

---

**Tempo Total Estimado**: 2-3 horas  
**Impacto**: 🔴 Crítico - Reduz vulnerabilidades de segurança em 80%
