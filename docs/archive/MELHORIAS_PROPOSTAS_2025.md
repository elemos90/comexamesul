# 🚀 Melhorias Propostas - Portal Comissão de Exames (Outubro 2025)

## 📊 Resumo da Análise

**Arquitetura**: MVC Customizado com PHP 8.1+  
**Framework**: Custom (sem Laravel/Symfony)  
**Base de Dados**: MySQL 8+ com PDO  
**Frontend**: Tailwind CSS (CDN) + Vanilla JS  
**Estado Geral**: ✅ **Bom** - Funcional, bem documentado, features avançadas

---

## 🎯 Melhorias Prioritárias

### 🔴 CRÍTICO - Segurança

#### 1. Proteção de Queries SQL
**Problema**: Uso de `SELECT *` expõe todos os campos, incluindo dados sensíveis.

**Localização**: 39 ocorrências em Models
- `app/Models/User.php` (linhas 38, 46, 87, 93, 99)
- `app/Models/BaseModel.php` (linhas 22, 30, 87)
- `app/Services/AllocationService.php` (7 ocorrências)

**Solução**:
```php
// ❌ EVITAR
$stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");

// ✅ IMPLEMENTAR
$stmt = $this->db->prepare(
    "SELECT id, name, email, role, avatar_url, phone, nuit 
     FROM users WHERE email = :email"
);
```

**Implementação**:
1. Criar constante em cada Model com campos permitidos
2. Atualizar BaseModel::find() para usar lista de campos
3. Nunca retornar `password_hash` em queries de usuário

---

#### 2. Sanitização de Output (XSS Prevention)

**Problema**: Dados inseridos por usuários não são escapados no output.

**Solução**:
```php
// app/Utils/helpers.php
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
```

**Usar em todas as Views**:
```php
// ❌ PERIGOSO
<p><?= $user['notes'] ?></p>

// ✅ SEGURO
<p><?= e($user['notes']) ?></p>
```

---

#### 3. Validação de Uploads

**Problema**: Validação de uploads usa apenas extensão, não MIME type real.

**Localização**: `AvailabilityController.php` linha 233

**Solução**:
```php
// Verificar MIME type real, não apenas extensão
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['attachment']['tmp_name']);
finfo_close($finfo);

$allowedMimes = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'application/msword'
];

if (!in_array($mimeType, $allowedMimes)) {
    throw new Exception('Tipo de arquivo não permitido');
}
```

---

#### 4. Rate Limiting para APIs

**Problema**: Endpoints de API não têm rate limiting.

**Solução**: Adicionar middleware aos endpoints críticos:
```php
// web.php
$router->get('/api/allocation/stats', 'JuryController@getAllocationStats', [
    'AuthMiddleware', 
    'RoleMiddleware:coordenador,membro',
    'RateLimitMiddleware:60,1' // 60 requests por minuto
]);
```

---

### 🟠 ALTO - Qualidade de Código

#### 5. Refatorar Controllers Grandes

**Problema**: `JuryController.php` tem 989+ linhas

**Solução**: Extrair lógica para Services
```
app/Services/
  ├─ JuryAllocationService.php      # Métodos de alocação
  ├─ JuryValidationService.php      # Validações
  └─ JuryReportService.php          # Relatórios
```

**Exemplo**:
```php
// JuryController.php (simplificado)
public function autoAllocateJury(Request $request) {
    $juryId = (int) $request->param('id');
    
    $service = new JuryAllocationService();
    $result = $service->autoAllocate($juryId);
    
    return Response::json($result);
}
```

---

#### 6. Implementar Testes Automatizados

**Problema**: Projeto sem testes unitários/integração.

**Setup**:
```bash
composer require --dev phpunit/phpunit
```

**Estrutura**:
```
tests/
  ├─ Unit/
  │   ├─ Utils/ValidatorTest.php
  │   ├─ Utils/AuthTest.php
  │   └─ Models/UserTest.php
  ├─ Feature/
  │   ├─ AuthenticationTest.php
  │   ├─ JuryAllocationTest.php
  │   └─ VacancyApplicationTest.php
  └─ TestCase.php
```

**Exemplo de Teste**:
```php
class ValidatorTest extends TestCase {
    public function test_validates_nuit_correctly() {
        $this->assertTrue(Validator::nuit('123456789'));
        $this->assertFalse(Validator::nuit('abc'));
    }
    
    public function test_validates_email() {
        $this->assertTrue(Validator::email('user@example.com'));
        $this->assertFalse(Validator::email('invalid'));
    }
}
```

---

#### 7. Type Hints e Return Types

**Problema**: Tipagem inconsistente em alguns métodos.

**Implementar**:
```php
// ❌ Sem tipagem
public function find($id) {
    // ...
}

// ✅ Com tipagem completa
public function find(int $id): ?array {
    // ...
}
```

**Meta**: 100% dos métodos públicos com type hints

---

### 🟡 MÉDIO - Performance

#### 8. Resolver N+1 Queries

**Problema**: Vigilantes carregados em loop.

**Localização**: `JuryController@index`

**Solução - Eager Loading**:
```php
// ❌ N+1 Problem
foreach ($juries as $jury) {
    $jury['vigilantes'] = $model->vigilantesForJury($jury['id']); // Query por júri
}

// ✅ Eager Loading (1 query)
$juryIds = array_column($juries, 'id');
$allVigilantes = $model->getVigilantesForJuries($juryIds);

// Agrupar por jury_id
$vigilantesByJury = [];
foreach ($allVigilantes as $v) {
    $vigilantesByJury[$v['jury_id']][] = $v;
}
```

---

#### 9. Cache de Estatísticas

**Problema**: Dashboard recalcula stats a cada request.

**Implementação**:
```php
class StatsCacheService {
    private const CACHE_TTL = 300; // 5 minutos
    
    public function getLocationStats(): array {
        $cacheKey = 'location_stats_' . date('YmdHi');
        $cacheFile = storage_path("cache/{$cacheKey}.json");
        
        if (file_exists($cacheFile) && 
            time() - filemtime($cacheFile) < self::CACHE_TTL) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        // Calcular stats
        $stats = $this->calculateStats();
        
        // Salvar cache
        file_put_contents($cacheFile, json_encode($stats));
        
        return $stats;
    }
}
```

---

#### 10. Adicionar Índices na Base de Dados

**Queries lentas identificadas**:
```sql
-- Júris por local e data (usado em visualizações)
CREATE INDEX idx_juries_location_date 
ON juries(location_id, exam_date);

-- Vigilantes disponíveis
CREATE INDEX idx_users_role_available 
ON users(role, available_for_vigilance);

-- Lookups de alocação
CREATE INDEX idx_jury_vigilantes_lookup 
ON jury_vigilantes(vigilante_id, jury_id);

-- Candidaturas por status
CREATE INDEX idx_applications_status 
ON vacancy_applications(status, vacancy_id);
```

---

### 🟢 BAIXO - Manutenibilidade

#### 11. Consolidar Documentação

**Problema**: 60+ arquivos MD na raiz do projeto.

**Solução**: Reorganizar em pasta estruturada
```
docs/
  ├─ README.md                    # Overview
  ├─ instalacao/
  │   ├─ setup-inicial.md
  │   ├─ migrations.md
  │   └─ producao.md
  ├─ funcionalidades/
  │   ├─ alocacao-dnd.md
  │   ├─ candidaturas.md
  │   └─ juris.md
  ├─ desenvolvimento/
  │   ├─ arquitetura.md
  │   ├─ padroes-codigo.md
  │   └─ testes.md
  └─ api/
      └─ endpoints.md
```

---

#### 12. Logging Estruturado

**Implementar Monolog**:
```bash
composer require monolog/monolog
```

```php
// app/Utils/Logger.php
class Logger {
    private static $instance;
    
    public static function error(string $message, array $context = []): void {
        self::getInstance()->error($message, $context);
    }
    
    public static function info(string $message, array $context = []): void {
        self::getInstance()->info($message, $context);
    }
}

// Usar em Controllers/Services
Logger::error('Falha ao alocar vigilante', [
    'jury_id' => $juryId,
    'vigilante_id' => $vigilanteId,
    'reason' => $exception->getMessage()
]);
```

---

#### 13. Migrations com Controle de Versão

**Problema**: SQLs manuais sem tracking.

**Implementar**:
```php
// app/Database/MigrationRunner.php
class MigrationRunner {
    public function run(): void {
        // Criar tabela de tracking
        $this->createMigrationsTable();
        
        // Pegar migrations não executadas
        $pending = $this->getPendingMigrations();
        
        foreach ($pending as $migration) {
            $this->execute($migration);
            $this->markAsExecuted($migration);
        }
    }
}
```

---

## 📋 Plano de Implementação (8 Semanas)

### Semana 1-2: Segurança Crítica
- [ ] Implementar helper `e()` para escape
- [ ] Refatorar queries SQL (remover `SELECT *`)
- [ ] Melhorar validação de uploads
- [ ] Adicionar rate limiting em APIs

### Semana 3-4: Testes
- [ ] Configurar PHPUnit
- [ ] Criar 15 testes unitários (Utils, Models)
- [ ] Criar 5 testes de feature
- [ ] Documentar como executar testes

### Semana 5-6: Performance
- [ ] Resolver N+1 queries
- [ ] Implementar cache de estatísticas
- [ ] Adicionar índices no BD
- [ ] Otimizar queries pesadas

### Semana 7-8: Refatoração
- [ ] Extrair Services de JuryController
- [ ] Adicionar type hints completos
- [ ] Implementar logging estruturado
- [ ] Reorganizar documentação

---

## 🛠️ Ferramentas Recomendadas

```bash
# Análise estática
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse app --level=5

# Code style
composer require --dev squizlabs/php_codesniffer
./vendor/bin/phpcs app --standard=PSR12

# Testes
composer require --dev phpunit/phpunit
./vendor/bin/phpunit

# Auditoria de segurança
composer audit
```

---

## 📊 Métricas de Sucesso

| Métrica | Atual | Meta |
|---------|-------|------|
| Cobertura de Testes | 0% | 70%+ |
| PHPStan Level | N/A | Level 6 |
| Tempo Response (p95) | ~500ms | <200ms |
| Queries N+1 | ~15 | 0 |
| SELECT * | 39 | 0 |

---

## ✅ Pontos Fortes do Projeto

1. ✅ **Documentação Rica**: 60+ arquivos de docs
2. ✅ **Features Avançadas**: Drag-and-drop, auto-alocação, templates
3. ✅ **Segurança Básica**: CSRF, rate limiting, password hashing
4. ✅ **Código Organizado**: Estrutura MVC clara
5. ✅ **Acessibilidade**: Modais acessíveis, aria-live

---

## 🎓 Próximos Passos Imediatos

### 1. Implementar Helper de Escape (15 min)
```php
// app/Utils/helpers.php
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
```

### 2. Criar Teste Básico (30 min)
```bash
composer require --dev phpunit/phpunit
mkdir -p tests/Unit
# Criar ValidatorTest.php
```

### 3. Adicionar Índices (10 min)
```sql
-- Executar queries de índices propostas
```

---

**Data da Análise**: Outubro 2025  
**Preparado por**: Análise Automatizada de Código  
**Contato**: Para implementação, priorize itens CRÍTICOS primeiro
