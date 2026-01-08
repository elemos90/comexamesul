# 🧪 Public Tests

Testes que estavam no diretório `/public` foram movidos para cá por segurança.

## 📁 Conteúdo (7 arquivos)

| Arquivo | Descrição | Como Usar |
|---------|-----------|-----------|
| `test.php` | Teste básico PHP | `php tests/public/test.php` |
| `test.html` | Página de teste HTML | Abrir no browser |
| `test-drag.html` | Teste drag-and-drop | Abrir no browser |
| `test_master_data.php` | Teste master data | `php tests/public/test_master_data.php` |
| `test_routes.php` | Teste de rotas | `php tests/public/test_routes.php` |
| `index.php.test` | Entry point de teste | Backup do index.php |
| `check.php` | Verificações gerais | `php tests/public/check.php` |

---

## 🚀 Como Executar

### Via CLI
```bash
cd tests/public
php test_routes.php
php test_master_data.php
php check.php
```

### Via Browser (Development)
```bash
# Iniciar servidor PHP
php -S localhost:8000 -t tests/public

# Acessar
http://localhost:8000/test.html
http://localhost:8000/test-drag.html
```

---

## ⚠️ Importante

- **Não executar em produção**
- Apenas ambiente de desenvolvimento
- Requer configuração `.env` válida
- Alguns testes podem modificar banco de dados

---

## 🔄 Migrar para PHPUnit

Estes testes devem ser **migrados para PHPUnit**:

```php
// tests/Unit/MasterDataTest.php
class MasterDataTest extends TestCase {
    public function test_locations_are_loaded() {
        // Migrar lógica de test_master_data.php
    }
}

// tests/Feature/RoutesTest.php
class RoutesTest extends TestCase {
    public function test_public_routes_are_accessible() {
        // Migrar lógica de test_routes.php
    }
}
```

**Referência:** `docs/02-development/GUIA_TESTE_*.md`

---

**Movido em:** 05 de Novembro de 2025  
**Motivo:** Limpeza de `/public` - Melhoria #2
