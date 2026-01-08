# 📦 Legacy Scripts

Este diretório contém **scripts legados** que foram removidos do diretório `/public` por questões de segurança e organização.

## ⚠️ Importante

- **Acesso web bloqueado:** `.htaccess` impede acesso via HTTP
- **Não usar em produção:** Scripts desatualizados
- **Apenas referência:** Para consulta e migração

---

## 📁 Conteúdo (12 arquivos)

### Scripts de Interface Direta (antes do MVC completo)

| Arquivo | Descrição | Status |
|---------|-----------|--------|
| `alocar_equipe.php` | Interface drag-and-drop legada | ⚠️ Substituído por `/juries/planning` |
| `criar_juri.php` | Criação de júris (versão antiga) | ⚠️ Usar `JuryController` |
| `dashboard_direto.php` | Dashboard sem roteamento | ⚠️ Usar `/dashboard` |
| `distribuicao_automatica.php` | Auto-alocação antiga | ⚠️ Usar API de auto-allocation |
| `login_direto.php` | Login sem AuthController | ⚠️ Usar `/login` |
| `logout_direto.php` | Logout direto | ⚠️ Usar `/logout` POST |
| `mapa_alocacoes.php` | Mapa de alocações legado | ⚠️ Usar `/juries/planning` |
| `relatorios.php` | Relatórios antigos | ⚠️ Usar `ReportController` |
| `ver_disciplinas.php` | Listagem master data | ⚠️ Usar `MasterDataController` |
| `ver_locais.php` | Listagem de locais | ⚠️ Usar `LocationController` |
| `ver_salas.php` | Listagem de salas | ⚠️ Usar API de salas |
| `get_rooms.php` | API simples de salas | ⚠️ Usar `MasterDataController` |

---

## 🔄 Migração

### Scripts foram substituídos por:

**Interface Moderna (MVC):**
- `alocar_equipe.php` → `JuryController@planning` + `planning-dnd.js`
- `criar_juri.php` → `JuryController@store/createBatch`
- `dashboard_direto.php` → `DashboardController@index`
- `login_direto.php` → `AuthController@login`

**APIs RESTful:**
- `distribuicao_automatica.php` → `POST /juries/vacancy/auto-allocate`
- `get_rooms.php` → `GET /api/master-data/rooms`
- `relatorios.php` → `ReportController` + `ExportController`

**Funcionalidades Master Data:**
- `ver_disciplinas.php` → `MasterDataController@subjects`
- `ver_locais.php` → `LocationController@index`
- `ver_salas.php` → `MasterDataController@rooms`

---

## 🗑️ Quando Deletar?

Estes scripts podem ser **deletados com segurança** quando:

1. ✅ Sistema MVC estiver 100% funcional (já está)
2. ✅ Todas funcionalidades migradas (completo)
3. ✅ Não houver referências externas (verificar)
4. ✅ Backup completo realizado

**Recomendação:** Manter por **6 meses** como referência, depois deletar.

---

## 📚 Documentação

- **MVC Routes:** `app/Routes/web.php`
- **Controllers:** `app/Controllers/`
- **Guia de Uso:** `docs/04-user-guides/`
- **API Reference:** `docs/05-api-reference/`

---

## 🔒 Segurança

**Proteção Implementada:**
```apache
# .htaccess
Order Deny,Allow
Deny from all
```

❌ **Não acessível via web**  
✅ **Apenas via filesystem (CLI/scripts)**

---

**Data de Arquivamento:** 05 de Novembro de 2025  
**Motivo:** Limpeza de `/public` - Melhoria #2  
**Documentado em:** `LIMPEZA_PUBLIC_2025.md`
