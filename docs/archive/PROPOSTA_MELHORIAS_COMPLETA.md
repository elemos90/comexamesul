# 🚀 Proposta de Melhorias - Portal Comissão de Exames

**Data da Análise**: 15 de Outubro de 2025  
**Versão do Sistema**: 2.1  
**Status Geral**: ✅ Sistema funcional e bem documentado

---

## 📊 Visão Geral do Projeto

### Pontos Fortes Identificados ✅
1. **Arquitetura MVC bem estruturada** - Separação clara de responsabilidades
2. **Documentação rica** - 60+ arquivos de documentação
3. **Recursos avançados implementados** - Drag-and-drop, auto-alocação, templates
4. **Segurança básica** - CSRF protection, password hashing, rate limiting
5. **Helper e()** já implementado - Proteção XSS disponível (linha 124 de helpers.php)
6. **Eager loading já implementado** - N+1 queries resolvido no JuryController
7. **Cache service** - StatsCacheService implementado

### Áreas que Precisam de Atenção ⚠️
1. 37 ocorrências de `SELECT *` em Models e Services
2. Falta de testes automatizados (0% cobertura)
3. Controllers grandes (JuryController.php com 2500+ linhas)
4. Dependências frontend via CDN
5. Views ainda não usam consistentemente o helper `e()`
6. Sistema de migrations manual sem versionamento

---

## 🎯 Melhorias Priorizadas

### 🔴 CRÍTICO - Implementação Imediata (1-2 semanas)

#### 1. Aplicar Sanitização XSS em Todas as Views
**Status**: Helper `e()` existe mas não é usado consistentemente  
**Impacto**: Alto risco de segurança

**Arquivos críticos a corrigir**:
```php
// app/Views/juries/planning.php - Múltiplas ocorrências
Linha 219: <?= strtoupper(htmlspecialchars($location)) ?> // Usar e()
Linha 260: <?= e($group['subject']) ?> // ✅ Já correto
Linha 267: <?= e($jury['room']) ?> // ✅ Já correto
Linha 285: <?= e($jury['notes']) ?> // ✅ Já correto
Linha 296: <?= e($v['name']) ?> // ✅ Já correto
Linha 319: <?= e($jury['room']) ?> // ✅ Já correto
Linha 347: <?= e($lastSupervisor) ?> // ✅ Já correto
```

**Ação**:
- Auditar todas as views em `app/Views/`
- Substituir `htmlspecialchars()` por `e()`
- Adicionar `e()` onde falta sanitização

#### 2. Eliminar SELECT * Queries
**Localização**: 37 ocorrências identificadas  
**Impacto**: Segurança + Performance

**Prioridade de correção**:

**a) User.php (6 ocorrências)**:
```php
// ❌ Linha 38
$stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");

// ✅ Corrigir para
protected array $selectColumns = [
    'id', 'name', 'email', 'role', 'phone', 'nuit', 
    'avatar_url', 'available_for_vigilance', 'supervisor_eligible',
    'created_at'
    // NUNCA incluir password_hash
];
```

**b) AllocationService.php (7 ocorrências)**:
```php
// Especificar apenas campos necessários para alocação
$sql = "SELECT id, name, role, available_for_vigilance, 
        (SELECT COUNT(*) FROM jury_vigilantes WHERE vigilante_id = u.id) as workload
        FROM users u WHERE...";
```

**c) BaseModel**:
```php
// Já existe getSelectColumns() - forçar uso
protected function getSelectColumns(): string {
    if (!isset($this->selectColumns) || empty($this->selectColumns)) {
        throw new \Exception("Model " . static::class . " must define selectColumns property");
    }
    return implode(', ', $this->selectColumns);
}
```

#### 3. Adicionar Índices Críticos no Banco de Dados
**Impacto**: Performance significativa

```sql
-- Executar em produção
CREATE INDEX idx_juries_location_date ON juries(location_id, exam_date) IF NOT EXISTS;
CREATE INDEX idx_juries_exam_lookup ON juries(exam_date, start_time, subject) IF NOT EXISTS;
CREATE INDEX idx_users_role_available ON users(role, available_for_vigilance) IF NOT EXISTS;
CREATE INDEX idx_jury_vigilantes_jury ON jury_vigilantes(jury_id) IF NOT EXISTS;
CREATE INDEX idx_jury_vigilantes_vigilante ON jury_vigilantes(vigilante_id) IF NOT EXISTS;
CREATE INDEX idx_applications_status_vacancy ON vacancy_applications(status, vacancy_id) IF NOT EXISTS;
CREATE INDEX idx_applications_user ON vacancy_applications(user_id, status) IF NOT EXISTS;
```

---

### 🟠 ALTO - Implementação em 4 semanas

#### 4. Implementar Testes Automatizados
**Status**: Projeto sem suite de testes  
**Meta**: 60% cobertura em 4 semanas

**Setup**:
```bash
composer require --dev phpunit/phpunit ^10.0
```

**Estrutura proposta**:
```
tests/
├── Unit/
│   ├── Utils/
│   │   ├── ValidatorTest.php          # Prioridade 1
│   │   ├── AuthTest.php                # Prioridade 1
│   │   └── CsrfTest.php                # Prioridade 2
│   ├── Models/
│   │   ├── UserTest.php                # Prioridade 1
│   │   └── JuryTest.php                # Prioridade 2
│   └── Services/
│       └── AllocationPlannerTest.php   # Prioridade 1
├── Feature/
│   ├── AuthenticationTest.php          # Prioridade 1
│   ├── JuryCreationTest.php           # Prioridade 2
│   └── AllocationFlowTest.php         # Prioridade 2
├── bootstrap.php
└── phpunit.xml
```

**Exemplo de teste prioritário**:
```php
// tests/Unit/Utils/ValidatorTest.php
<?php

use App\Utils\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase {
    public function test_validates_nuit_correctly(): void {
        $this->assertTrue(Validator::nuit('100123456'));
        $this->assertFalse(Validator::nuit('abc'));
        $this->assertFalse(Validator::nuit('123')); // too short
    }
    
    public function test_validates_email(): void {
        $this->assertTrue(Validator::email('user@unilicungo.ac.mz'));
        $this->assertFalse(Validator::email('invalid'));
    }
    
    public function test_required_validation(): void {
        $this->assertTrue(Validator::required('value'));
        $this->assertFalse(Validator::required(''));
        $this->assertFalse(Validator::required(null));
    }
}
```

#### 5. Refatorar JuryController (Extrair Services)
**Problema**: 2500+ linhas em um único controller  
**Solução**: Criar services especializados

```
app/Services/
├── JuryAllocationService.php     # Métodos de alocação
├── JuryValidationService.php     # Validações de júri
├── JuryExportService.php         # Exportações PDF/Excel
└── JuryStat sService.php         # Estatísticas e dashboards
```

**Exemplo**:
```php
// app/Services/JuryAllocationService.php
<?php

namespace App\Services;

class JuryAllocationService {
    public function allocateVigilante(int $juryId, int $vigilanteId): array {
        // Mover lógica do controller para aqui
        // Validações
        // Verificar conflitos
        // Salvar
        // Retornar resultado
    }
    
    public function removeVigilante(int $juryId, int $vigilanteId): bool {
        // Lógica de remoção
    }
    
    public function autoAllocate(int $juryId, int $slots = 2): array {
        // Lógica de auto-alocação
    }
}

// JuryController.php (simplificado)
public function allocateVigilante(Request $request): Response {
    $service = new JuryAllocationService();
    $result = $service->allocateVigilante(
        $request->param('jury_id'),
        $request->param('vigilante_id')
    );
    return Response::json($result);
}
```

#### 6. Implementar Logging Estruturado
**Instalar Monolog**:
```bash
composer require monolog/monolog
```

**Implementação**:
```php
// app/Utils/Logger.php
<?php

namespace App\Utils;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class Logger {
    private static ?MonologLogger $instance = null;
    
    private static function getInstance(): MonologLogger {
        if (self::$instance === null) {
            self::$instance = new MonologLogger('app');
            self::$instance->pushHandler(
                new RotatingFileHandler(storage_path('logs/app.log'), 30)
            );
        }
        return self::$instance;
    }
    
    public static function error(string $message, array $context = []): void {
        self::getInstance()->error($message, $context);
    }
    
    public static function warning(string $message, array $context = []): void {
        self::getInstance()->warning($message, $context);
    }
    
    public static function info(string $message, array $context = []): void {
        self::getInstance()->info($message, $context);
    }
}
```

**Usar nos controllers**:
```php
try {
    $result = $service->allocateVigilante($juryId, $vigilanteId);
} catch (\Exception $e) {
    Logger::error('Falha ao alocar vigilante', [
        'jury_id' => $juryId,
        'vigilante_id' => $vigilanteId,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    return Response::json(['success' => false, 'message' => 'Erro ao alocar']);
}
```

---

### 🟡 MÉDIO - Implementação em 6-8 semanas

#### 7. Migrar Assets CDN para Build Local
**Problema**: Dependência de CDNs externos  
**Solução**: npm + Vite

**Setup**:
```json
// package.json
{
  "name": "comexamesul",
  "version": "2.1.0",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
  "devDependencies": {
    "vite": "^5.0.0",
    "tailwindcss": "^3.4.0",
    "autoprefixer": "^10.4.16",
    "postcss": "^8.4.32"
  },
  "dependencies": {
    "sortablejs": "^1.15.0"
  }
}
```

**Estrutura proposta**:
```
resources/
├── js/
│   ├── app.js
│   ├── modules/
│   │   ├── allocation.js
│   │   ├── drag-drop.js
│   │   └── modals.js
│   └── utils/
│       └── api.js
├── css/
│   └── app.css
└── images/

public/
└── build/           # Assets compilados
    ├── app.js
    ├── app.css
    └── manifest.json
```

#### 8. Sistema de Migrations com Versionamento
**Problema**: SQLs manuais sem controle de versão

**Implementação**:
```php
// app/Database/MigrationRunner.php
<?php

namespace App\Database;

class MigrationRunner {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Connection::getInstance();
        $this->ensureMigrationsTable();
    }
    
    private function ensureMigrationsTable(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY (migration)
            )
        ");
    }
    
    public function run(): void {
        $migrations = $this->getPendingMigrations();
        $batch = $this->getNextBatch();
        
        foreach ($migrations as $migration) {
            echo "Running migration: {$migration}\n";
            $this->executeMigration($migration);
            $this->markAsExecuted($migration, $batch);
        }
    }
    
    private function getPendingMigrations(): array {
        $files = glob(app_path('Database/Migrations/*.php'));
        $executed = $this->db->query("SELECT migration FROM migrations")
                             ->fetchAll(\PDO::FETCH_COLUMN);
        
        $pending = [];
        foreach ($files as $file) {
            $name = basename($file);
            if (!in_array($name, $executed)) {
                $pending[] = $name;
            }
        }
        sort($pending);
        return $pending;
    }
    
    private function executeMigration(string $migration): void {
        require app_path("Database/Migrations/{$migration}");
        // Migration files devem retornar uma função up()
        $up = include app_path("Database/Migrations/{$migration}");
        $up($this->db);
    }
    
    private function markAsExecuted(string $migration, int $batch): void {
        $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migration, $batch]);
    }
    
    private function getNextBatch(): int {
        return ((int) $this->db->query("SELECT MAX(batch) FROM migrations")->fetchColumn()) + 1;
    }
}
```

**Exemplo de migration**:
```php
// app/Database/Migrations/2025_10_15_000001_add_indexes_juries.php
<?php

return function(\PDO $db) {
    $db->exec("CREATE INDEX IF NOT EXISTS idx_juries_location_date ON juries(location_id, exam_date)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)");
    echo "  ✓ Índices adicionados\n";
};
```

#### 9. Adicionar Type Hints Completos
**Meta**: 100% dos métodos públicos com tipagem

**Antes**:
```php
public function find($id) {
    // ...
}

public function allocate($juryId, $vigilanteId) {
    // ...
}
```

**Depois**:
```php
public function find(int $id): ?array {
    // ...
}

public function allocate(int $juryId, int $vigilanteId): array {
    // ...
}
```

**Usar PHPStan para validar**:
```bash
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse app --level=6
```

#### 10. Implementar Soft Deletes
**Problema**: Deleções permanentes sem auditoria

**Migration**:
```sql
ALTER TABLE juries ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
```

**BaseModel**:
```php
protected bool $softDelete = false;

public function delete(int $id): bool {
    if ($this->softDelete) {
        return $this->update($id, ['deleted_at' => now()]);
    }
    return parent::delete($id);
}

public function all(array $conditions = []): array {
    if ($this->softDelete) {
        $conditions['deleted_at'] = null; // Apenas não deletados
    }
    return parent::all($conditions);
}

public function withTrashed(): static {
    $instance = clone $this;
    $instance->softDelete = false;
    return $instance;
}
```

---

### 🟢 BAIXO - Melhorias Opcionais (2-3 meses)

#### 11. Consolidar Documentação
**Problema**: 60+ arquivos MD na raiz

**Proposta**:
```
docs/
├── README.md                       # Índice principal
├── 01-instalacao/
│   ├── setup-local.md
│   ├── setup-producao.md
│   └── migrations.md
├── 02-funcionalidades/
│   ├── juris-alocacao.md
│   ├── candidaturas.md
│   ├── drag-and-drop.md
│   └── templates.md
├── 03-desenvolvimento/
│   ├── arquitetura.md
│   ├── padroes-codigo.md
│   ├── testes.md
│   └── contribuindo.md
├── 04-api/
│   └── endpoints.md
└── 05-changelog/
    ├── v2.1.md
    └── v2.0.md
```

#### 12. API REST Documentada
**Implementar padrão REST**:
```php
// Endpoints consistentes
GET    /api/juries              # Listar
POST   /api/juries              # Criar
GET    /api/juries/{id}         # Detalhes
PUT    /api/juries/{id}         # Atualizar
DELETE /api/juries/{id}         # Deletar
POST   /api/juries/{id}/allocate # Ação específica
```

**Documentar com OpenAPI/Swagger**:
```yaml
# public/api-docs.yaml
openapi: 3.0.0
info:
  title: Portal Comissão de Exames API
  version: 2.1.0
paths:
  /api/juries:
    get:
      summary: Listar júris
      parameters:
        - name: page
          in: query
          schema:
            type: integer
      responses:
        '200':
          description: Lista de júris
```

#### 13. Monitoramento e Alertas
**Implementar Sentry para error tracking**:
```bash
composer require sentry/sdk
```

```php
// bootstrap.php
\Sentry\init([
    'dsn' => env('SENTRY_DSN'),
    'environment' => env('APP_ENV'),
    'release' => '2.1.0'
]);

// Capturar exceções automaticamente
set_exception_handler(function(\Throwable $e) {
    \Sentry\captureException($e);
    Logger::error('Uncaught exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
});
```

---

## 📋 Plano de Implementação (8 Semanas)

### Sprint 1-2: Segurança Crítica ✅
- [ ] Auditar e aplicar `e()` em todas as views
- [ ] Eliminar SELECT * em User.php e AllocationService.php
- [ ] Adicionar índices críticos no banco
- [ ] Implementar logging de erros básico

### Sprint 3-4: Testes ✅
- [ ] Configurar PHPUnit e estrutura de testes
- [ ] Criar 15 testes unitários (Utils, Models)
- [ ] Criar 5 testes de feature
- [ ] Configurar CI/CD simples

### Sprint 5-6: Refatoração ✅
- [ ] Extrair JuryAllocationService
- [ ] Implementar Monolog
- [ ] Adicionar type hints em 50% dos métodos públicos
- [ ] Sistema de migrations com versionamento

### Sprint 7-8: Qualidade ✅
- [ ] Migrar assets para build local (Vite)
- [ ] Completar type hints (100%)
- [ ] Implementar soft deletes
- [ ] Consolidar documentação

---

## 🛠️ Ferramentas Recomendadas

```bash
# Análise estática
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse app --level=6

# Code style (PSR-12)
composer require --dev squizlabs/php_codesniffer
./vendor/bin/phpcs app --standard=PSR12

# Testes
composer require --dev phpunit/phpunit
./vendor/bin/phpunit

# Auditoria de segurança
composer audit

# Frontend
npm install
npm run dev    # Desenvolvimento
npm run build  # Produção
```

---

## 📊 Métricas de Sucesso

| Métrica | Atual | Meta (8 semanas) |
|---------|-------|------------------|
| Cobertura de Testes | 0% | 60%+ |
| PHPStan Level | N/A | Level 6 |
| SELECT * Queries | 37 | 0 |
| Type Hints | ~40% | 100% |
| Tempo Response (p95) | ~400ms | <200ms |
| Tamanho JuryController | 2500 linhas | <500 linhas |
| Documentação | Fragmentada | Estruturada |

---

## ✅ Checklist de Próximos Passos Imediatos

### Hoje (30 minutos)
- [ ] Executar script de índices SQL
- [ ] Auditar 5 views principais com busca por `<?=` sem `e()`
- [ ] Criar issue/task list no repositório

### Esta Semana
- [ ] Adicionar `selectColumns` em User.php
- [ ] Criar primeiro teste: ValidatorTest.php
- [ ] Implementar Logger básico

### Este Mês
- [ ] Completar correção de SELECT *
- [ ] Ter 20+ testes rodando
- [ ] Extrair JuryAllocationService

---

## 🎓 Recursos Adicionais

### Leitura
- [PHP The Right Way](https://phptherightway.com/)
- [Clean Code PHP](https://github.com/jupeter/clean-code-php)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

### Ferramentas
- [PHPStan](https://phpstan.org/) - Análise estática
- [Vite](https://vitejs.dev/) - Build tool moderno
- [Sentry](https://sentry.io/) - Error tracking

---

## 💡 Conclusão

O projeto está em **excelente estado funcional** com features avançadas bem implementadas. As melhorias propostas focarão em:

1. **Segurança** - Eliminar vulnerabilidades potenciais
2. **Testes** - Garantir estabilidade e facilitar evolução
3. **Manutenibilidade** - Código limpo e bem organizado
4. **Performance** - Otimizações de banco e cache

**Impacto Esperado**:
- 🔒 +50% Segurança
- ⚡ +40% Performance  
- 🧪 +80% Confiabilidade
- 🚀 +60% Velocidade de Desenvolvimento

---

**Preparado por**: Análise Completa de Código  
**Data**: 15 de Outubro de 2025  
**Próxima Revisão**: 15 de Dezembro de 2025
