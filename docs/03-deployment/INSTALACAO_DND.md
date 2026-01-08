# ⚡ Instalação Rápida - Sistema Drag-and-Drop

## Pré-requisitos
- XAMPP com PHP 8.1+ e MySQL 8+
- Projeto ComExamesSul já instalado e funcionando
- Acesso ao banco de dados configurado no `.env`

---

## Passo 1: Executar Migration

Abra o terminal/PowerShell no diretório do projeto:

```powershell
cd c:/xampp/htdocs/comexamesul
php scripts/run_allocation_migration.php
```

**Saída esperada:**
```
=======================================================
MIGRATION: Melhorias no Sistema de Alocação
=======================================================

✓ Conectado ao banco: comexamesul

  ✓ Tabela alterada: juries
  ✓ Tabela alterada: jury_vigilantes
  ✓ Trigger criado: trg_check_vigilantes_capacity
  ✓ Trigger criado: trg_check_vigilante_conflicts
  ✓ Trigger criado: trg_check_supervisor_conflicts
  ✓ View criada: vw_docente_score
  ✓ View criada: vw_jury_slots
  ✓ View criada: vw_eligible_vigilantes
  ✓ View criada: vw_eligible_supervisors
  ✓ View criada: vw_allocation_stats
  ✓ Índice criado: idx_users_availability
  ✓ Dados atualizados

=======================================================
RESULTADO
=======================================================
✓ Comandos executados com sucesso: 15

✅ Migration concluída!

Views disponíveis:
  • vw_allocation_stats
  • vw_docente_score
  • vw_eligible_supervisors
  • vw_eligible_vigilantes
  • vw_jury_slots
  • vw_vigilante_workload

🎉 Pronto! Sistema de alocação inteligente está ativo.
```

---

## Passo 2: Verificar Instalação

Teste se as views foram criadas:

```sql
SHOW FULL TABLES WHERE Table_type = 'VIEW';
```

Deve retornar pelo menos 6 views (`vw_*`).

---

## Passo 3: Acessar Interface

Abra o navegador:

```
http://localhost/juries/planning
```

Ou pelo menu do sistema:
**Júris → Planejamento**

---

## Passo 4: Testar Funcionalidades

### ✅ Checklist de Teste

- [ ] Arrastar vigilante para júri (deve alocar)
- [ ] Arrastar vigilante para júri lotado (deve dar erro)
- [ ] Arrastar vigilante para júri com conflito de horário (deve dar erro)
- [ ] Arrastar supervisor para júri (deve alocar)
- [ ] Clicar "Auto" em um júri (deve auto-alocar vigilantes)
- [ ] Clicar "⚡ Auto-Alocar Completo" (deve alocar toda disciplina)
- [ ] Remover vigilante alocado (clique no ✕)
- [ ] Ver métricas atualizarem em tempo real

---

## Resolução de Problemas

### Erro: "Table 'comexamesul.vw_vigilante_workload' doesn't exist"

**Solução:** Execute novamente a migration:
```powershell
php scripts/run_allocation_migration.php
```

### Erro: "Access denied for user..."

**Solução:** Verifique credenciais no arquivo `.env`:
```
DB_USERNAME=root
DB_PASSWORD=
```

### Página em branco ou erro 500

**Solução:** Verifique logs:
```powershell
cat storage/logs/error.log
```

### Drag-and-drop não funciona

**Solução:** 
1. Limpe cache do navegador (Ctrl+F5)
2. Verifique se SortableJS carregou (F12 → Console)
3. Verifique se está na URL correta: `/juries/planning`

---

## Comandos Úteis

### Ver estatísticas de alocação
```sql
SELECT * FROM vw_allocation_stats;
```

### Ver carga de vigilantes
```sql
SELECT * FROM vw_vigilante_workload ORDER BY workload_score DESC;
```

### Ver ocupação de júris
```sql
SELECT * FROM vw_jury_slots;
```

### Resetar todas as alocações (CUIDADO!)
```sql
DELETE FROM jury_vigilantes;
UPDATE juries SET supervisor_id = NULL;
```

---

## Próximos Passos

1. ✅ Criar alguns júris de teste
2. ✅ Marcar vigilantes como disponíveis
3. ✅ Testar alocação manual (drag-and-drop)
4. ✅ Testar auto-alocação
5. ✅ Verificar métricas e equilíbrio

---

## Suporte

Leia a documentação completa em:
- `SISTEMA_ALOCACAO_DND.md` (Manual completo)
- `NOVAS_FUNCIONALIDADES.md` (Funcionalidades v2.0)
- `README.md` (Visão geral do projeto)

---

**Instalação concluída! Bom uso! 🚀**
