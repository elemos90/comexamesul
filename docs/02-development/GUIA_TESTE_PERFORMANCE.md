# 🧪 GUIA DE TESTE - Performance do Sistema

**Execute estes testes na ordem para verificar as melhorias**

---

## ✅ CHECKLIST DE TESTES

### [ ] Teste 1: Índices de Base de Dados
### [ ] Teste 2: Cache do Dashboard  
### [ ] Teste 3: N+1 Queries Resolvido
### [ ] Teste 4: Planning com Cache
### [ ] Teste 5: Invalidação de Cache

---

## 🔍 TESTE 1: Verificar Índices (COMPLETO ✅)

```bash
# Já executado - Resultado: SUCCESS!
# Índices criados:
✅ idx_juries_location_date
✅ idx_juries_vacancy
✅ idx_juries_subject
✅ idx_users_available
✅ idx_jury_vigilantes_jury
```

**Status**: ✅ PASSOU

---

## 📊 TESTE 2: Cache do Dashboard

### Passo a Passo:

#### 1. Abrir DevTools
```
1. Abra navegador
2. Pressione F12 (DevTools)
3. Clique na aba "Network"
4. Mantenha aberto
```

#### 2. Primeiro Acesso (SEM cache)
```
URL: http://localhost/dashboard

Observe no Network:
- Nome: dashboard (ou o request principal)
- Time: deve estar entre 400-800ms
```

**Anote o tempo**: __________ ms

#### 3. Segundo Acesso (COM cache)
```
1. Pressione F5 (recarregar)
2. Observe o tempo no Network
3. Deve ser MUITO mais rápido (50-150ms)
```

**Anote o tempo**: __________ ms

#### 4. Calcular Melhoria
```
Tempo 1º acesso: _____ ms
Tempo 2º acesso: _____ ms
Melhoria: _____ %

Exemplo:
- 1º: 600ms
- 2º: 50ms
- Melhoria: 92% mais rápido! 🚀
```

### ✅ Critérios de Sucesso:
- [ ] 2º acesso é pelo menos 70% mais rápido
- [ ] Tempo 2º acesso < 150ms
- [ ] Sistema responsivo

---

## 📁 TESTE 3: Verificar Arquivos de Cache

```powershell
# Execute no PowerShell
Get-ChildItem storage\cache\stats -File | Select-Object Name, Length, LastWriteTime

# Deve mostrar arquivos .json
# Exemplo:
# Name: a1b2c3d4e5f6.json
# Length: 1234 bytes
# LastWriteTime: 2025-10-14 14:15:00
```

### ✅ Critérios de Sucesso:
- [ ] Pelo menos 1 arquivo .json criado
- [ ] LastWriteTime é recente (últimos minutos)
- [ ] Tamanho > 0 bytes

---

## 🔢 TESTE 4: N+1 Queries - Contar Queries

### Método Visual (Recomendado)

#### 1. Limpar Cache Primeiro
```
1. Feche navegador completamente
2. Abra novamente
3. Acesse: http://localhost/juries
```

#### 2. Observar Tempo de Carregamento
```
F12 → Network → Reload

Tempo esperado:
- ANTES (sem otimização): ~800-1200ms
- DEPOIS (com otimização): ~150-300ms
```

**Tempo observado**: __________ ms

### ✅ Critérios de Sucesso:
- [ ] Página carrega em < 300ms
- [ ] Lista de júris aparece rapidamente
- [ ] Vigilantes carregam junto (não em partes)

---

## 📋 TESTE 5: Planning com Cache

```
URL: http://localhost/juries/planning

1º Acesso: _____ ms (sem cache)
2º Acesso: _____ ms (com cache)
Melhoria: _____ %
```

### ✅ Critérios de Sucesso:
- [ ] 1º acesso < 500ms (graças aos índices)
- [ ] 2º acesso < 100ms (graças ao cache)
- [ ] Tabela renderiza suavemente

---

## 🔄 TESTE 6: Invalidação de Cache

### Objetivo: Verificar se cache é limpo ao alterar dados

#### 1. Acessar Dashboard
```
http://localhost/dashboard
(cache será criado)
```

#### 2. Criar/Editar um Júri
```
http://localhost/juries/planning-by-vacancy
1. Crie ou edite qualquer júri
2. Salve as alterações
```

#### 3. Voltar ao Dashboard
```
http://localhost/dashboard
1. O cache deve ter sido invalidado
2. Dados devem estar atualizados
3. Novo cache será criado
```

### ✅ Critérios de Sucesso:
- [ ] Alterações aparecem no dashboard
- [ ] Cache foi recriado com dados novos
- [ ] Sistema continua rápido

---

## 📈 TESTE 7: Stress Test (Opcional)

### Simular Múltiplos Usuários

```powershell
# Abrir 5 abas do navegador simultaneamente
# Todas acessando: http://localhost/dashboard

# Observar:
# - Sistema deve continuar responsivo
# - Tempo de resposta similar para todos
# - Sem travamentos
```

### ✅ Critérios de Sucesso:
- [ ] Sistema responde em < 200ms para todos
- [ ] Sem erros de timeout
- [ ] CPU do servidor não satura

---

## 🎯 RESULTADOS ESPERADOS

### Performance Geral

| Página | Sem Cache | Com Cache | Melhoria |
|--------|-----------|-----------|----------|
| Dashboard | 400-800ms | 50-150ms | 80-92% |
| Planning | 600-1200ms | 80-150ms | 87-93% |
| Lista Júris | 400-800ms | 150-300ms | 62-75% |

### Queries ao BD

| Operação | Antes | Depois | Redução |
|----------|-------|--------|---------|
| Dashboard (cache) | 8-10 | 0 | 100% |
| Lista 50 júris | 51 | 2 | 96% |
| Planning | 60+ | 2-3 | 95% |

---

## 🐛 Troubleshooting

### Cache não funciona

**Problema**: Tempos continuam iguais

**Soluções**:
```powershell
# 1. Verificar permissões
icacls storage\cache\stats

# 2. Limpar cache manualmente
Remove-Item storage\cache\stats\*.json -Force

# 3. Recarregar página
```

### Índices não melhoraram performance

**Problema**: Queries ainda lentas

**Soluções**:
```powershell
# 1. Verificar se índices existem
mysql -u root comexamesul -e "SHOW INDEX FROM juries;"

# 2. Re-executar script
Get-Content scripts\add_indexes_simple.sql | C:\xampp\mysql\bin\mysql.exe -u root comexamesul
```

### Erro ao acessar página

**Problema**: Erro 500 ou página em branco

**Soluções**:
```powershell
# 1. Ver logs de erro
Get-Content C:\xampp\php\logs\php_error_log -Tail 50

# 2. Verificar sintaxe PHP
php -l app\Controllers\DashboardController.php
```

---

## 📊 FORMULÁRIO DE RESULTADOS

### Resumo dos Testes

```
Data do teste: _______________
Hora: _______________

TESTE 1 - Índices: [ ] PASSOU [ ] FALHOU
TESTE 2 - Cache Dashboard: [ ] PASSOU [ ] FALHOU
TESTE 3 - Arquivos Cache: [ ] PASSOU [ ] FALHOU
TESTE 4 - N+1 Queries: [ ] PASSOU [ ] FALHOU
TESTE 5 - Planning: [ ] PASSOU [ ] FALHOU
TESTE 6 - Invalidação: [ ] PASSOU [ ] FALHOU

Performance Geral:
- Dashboard: _____ ms → _____ ms (melhoria: _____ %)
- Planning: _____ ms → _____ ms (melhoria: _____ %)
- Lista Júris: _____ ms → _____ ms (melhoria: _____ %)

Observações:
_________________________________
_________________________________
_________________________________
```

---

## 🎉 CONCLUSÃO

Se todos os testes passaram:

✅ **Sistema otimizado com sucesso!**

**Ganhos alcançados**:
- 🚀 Performance 10-15x melhor
- ⚡ Queries reduzidas em 96%
- 💚 Servidor suporta 5x mais usuários
- 📊 Cache funcionando perfeitamente

**Próximos passos**:
1. Monitorar performance em produção
2. Aplicar melhorias de segurança (XSS)
3. Implementar testes automatizados

---

**Preparado por**: Sistema de Análise Automatizada  
**Data**: 14 de Outubro de 2025  
**Versão**: 1.0
