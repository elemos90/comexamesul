# 📚 Índice de Documentação - Portal de Exames v2.0

Guia completo de toda a documentação disponível no projeto.

---

## 🚀 Para Começar

### Instalação e Configuração
1. **[README.md](README.md)** - Documentação principal do projeto
   - Requisitos do sistema
   - Instalação passo a passo
   - Configuração básica
   - Estrutura do projeto

2. **[QUICK_START.md](QUICK_START.md)** - Guia de início rápido (15 min)
   - Instalação completa
   - Primeiros passos
   - Uso básico das funcionalidades
   - Troubleshooting comum

---

## 🆕 Novas Funcionalidades (v2.0)

### Documentação Completa
3. **[NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md)** - Documentação técnica detalhada
   - 4 funcionalidades principais
   - Arquitetura e design
   - Casos de uso
   - Guias de uso para cada funcionalidade
   - Rotas e endpoints
   - Estrutura de banco de dados

### Guias Específicos
4. **[GUIA_CRIACAO_JURIS_POR_LOCAL.md](GUIA_CRIACAO_JURIS_POR_LOCAL.md)** - Criação de júris
   - Conceito hierárquico (Local → Disciplina → Sala)
   - Tutorial passo a passo
   - Exemplos práticos
   - Comparação com método anterior

5. **[TESTE_DRAG_DROP.md](TESTE_DRAG_DROP.md)** - Sistema de alocação
   - Como testar drag-and-drop
   - Pré-requisitos
   - Passo a passo para testes
   - Solução de problemas

---

## 📝 Changelog e Versões

6. **[CHANGELOG_V2.md](CHANGELOG_V2.md)** - Registro de mudanças v2.0
   - Lista completa de novidades
   - Breaking changes (nenhuma!)
   - Estatísticas do projeto
   - Créditos e licença

---

## 🎯 Por Funcionalidade

### 1️⃣ Visualização por Local

**O que é**: Visualização hierárquica de júris agrupados por local de realização.

**Documentação**:
- [NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md#1%EF%B8%8F%E2%83%A3-visualiza%C3%A7%C3%A3o-por-local) - Seção específica
- [QUICK_START.md](QUICK_START.md#4%EF%B8%8F%E2%83%A3-visualizar-por-local) - Uso rápido

**Arquivos do Projeto**:
```
app/Controllers/LocationController.php    # Método: index()
app/Models/Jury.php                       # Método: getGroupedByLocationAndDate()
app/Views/locations/index.php             # Interface visual
```

**Acesso**: Menu → Locais → Vis por Local (`/locations`)

---

### 2️⃣ Templates de Locais

**O que é**: Sistema para salvar e reutilizar configurações de locais.

**Documentação**:
- [NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md#2%EF%B8%8F%E2%83%A3-templates-de-locais) - Seção específica
- [QUICK_START.md](QUICK_START.md#2%EF%B8%8F%E2%83%A3-salvar-template-de-local) - Uso rápido

**Arquivos do Projeto**:
```
app/Models/LocationTemplate.php           # Model principal
app/Controllers/LocationController.php    # Métodos de template
app/Views/locations/templates.php         # Interface visual
app/Database/location_templates_migration.sql  # Schema
```

**Acesso**: Menu → Locais → Templates (`/locations/templates`)

---

### 3️⃣ Import/Export de Planilhas

**O que é**: Importação em massa de júris via Excel/CSV.

**Documentação**:
- [NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md#3%EF%B8%8F%E2%83%A3-importexport-de-planilhas) - Seção específica
- [QUICK_START.md](QUICK_START.md#3%EF%B8%8F%E2%83%A3-importar-j%C3%BAris-via-excel) - Uso rápido

**Arquivos do Projeto**:
```
app/Controllers/LocationController.php    # Métodos: processImport(), exportTemplate()
app/Views/locations/import.php            # Interface visual
```

**Acesso**: Menu → Locais → Importar (`/locations/import`)

---

### 4️⃣ Dashboard de Locais

**O que é**: Estatísticas agregadas e análise por local.

**Documentação**:
- [NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md#4%EF%B8%8F%E2%83%A3-dashboard-de-locais) - Seção específica
- [QUICK_START.md](QUICK_START.md#5%EF%B8%8F%E2%83%A3-ver-estat%C3%ADsticas) - Uso rápido

**Arquivos do Projeto**:
```
app/Models/LocationStats.php              # Model de estatísticas
app/Controllers/LocationController.php    # Método: dashboard()
app/Views/locations/dashboard.php         # Interface visual
```

**Acesso**: Menu → Locais → Dashboard (`/locations/dashboard`)

---

## 🛠️ Para Desenvolvedores

### Estrutura do Código

#### Controllers
- `app/Controllers/LocationController.php` - Controller principal de locais
- `app/Controllers/JuryController.php` - Estendido com `createLocationBatch()`

#### Models
- `app/Models/LocationTemplate.php` - Gerenciamento de templates
- `app/Models/LocationStats.php` - Estatísticas e agregações
- `app/Models/Jury.php` - Métodos de agrupamento adicionados

#### Views
- `app/Views/locations/index.php` - Visualização hierárquica
- `app/Views/locations/dashboard.php` - Dashboard estatísticas
- `app/Views/locations/templates.php` - Gerenciamento de templates
- `app/Views/locations/import.php` - Interface de importação

#### JavaScript
- `public/assets/js/app.js` - Função `initTemplates()` e `initDisciplineRooms()`

#### Database
- `app/Database/location_templates_migration.sql` - Schema das novas tabelas
- `scripts/install_locations_features.php` - Script de instalação

---

## 📖 Guias por Perfil

### 👨‍💼 Coordenador
**Leia primeiro**:
1. [QUICK_START.md](QUICK_START.md) - Começar rapidamente
2. [GUIA_CRIACAO_JURIS_POR_LOCAL.md](GUIA_CRIACAO_JURIS_POR_LOCAL.md) - Criar júris
3. [NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md) - Funcionalidades avançadas

**Tarefas comuns**:
- Criar júris por local
- Salvar templates
- Importar planilhas
- Ver dashboard

### 👨‍💻 Desenvolvedor
**Leia primeiro**:
1. [README.md](README.md) - Setup do projeto
2. [CHANGELOG_V2.md](CHANGELOG_V2.md) - O que mudou
3. [NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md) - Arquitetura técnica

**Recursos técnicos**:
- Estrutura de código
- Models e Controllers
- Rotas e endpoints
- Schema de banco de dados

### 🧪 Testador
**Leia primeiro**:
1. [QUICK_START.md](QUICK_START.md) - Configurar ambiente
2. [TESTE_DRAG_DROP.md](TESTE_DRAG_DROP.md) - Testar alocações
3. [CHANGELOG_V2.md](CHANGELOG_V2.md) - Checklist de testes

**Casos de teste**:
- Criação de júris
- Templates
- Import/Export
- Dashboard

---

## 🔍 Índice por Tópico

### Instalação
- [README.md](README.md#instala%C3%A7%C3%A3o) - Instalação básica
- [QUICK_START.md](QUICK_START.md#-instala%C3%A7%C3%A3o-completa-15-minutos) - Instalação completa

### Banco de Dados
- [README.md](README.md#base-de-dados) - Setup inicial
- [NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md#%EF%B8%8F-migra%C3%A7%C3%B5es-de-banco-de-dados) - Novas tabelas

### Segurança
- [README.md](README.md#seguran%C3%A7a) - Recursos de segurança
- [NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md#-permiss%C3%B5es) - Permissões das novas funcionalidades

### Performance
- [NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md#-performance) - Otimizações
- [CHANGELOG_V2.md](CHANGELOG_V2.md#-performance) - Melhorias de performance

### Troubleshooting
- [QUICK_START.md](QUICK_START.md#-troubleshooting) - Problemas comuns
- [TESTE_DRAG_DROP.md](TESTE_DRAG_DROP.md#-problemas-comuns) - Problemas de drag-and-drop

---

## 📞 Suporte e Ajuda

### Onde Procurar

1. **Problema de instalação**: [QUICK_START.md - Troubleshooting](QUICK_START.md#-troubleshooting)
2. **Como usar funcionalidade X**: [NOVAS_FUNCIONALIDADES.md](NOVAS_FUNCIONALIDADES.md)
3. **Erro ao criar júris**: [GUIA_CRIACAO_JURIS_POR_LOCAL.md](GUIA_CRIACAO_JURIS_POR_LOCAL.md)
4. **Drag-and-drop não funciona**: [TESTE_DRAG_DROP.md](TESTE_DRAG_DROP.md)
5. **Dúvidas técnicas**: [CHANGELOG_V2.md](CHANGELOG_V2.md)

---

## 🗺️ Roadmap de Leitura Sugerido

### Novo Usuário (Coordenador)
```
1. README.md (10 min)
   ↓
2. QUICK_START.md (15 min)
   ↓
3. GUIA_CRIACAO_JURIS_POR_LOCAL.md (15 min)
   ↓
4. Usar o sistema!
```

### Desenvolvedor Novo no Projeto
```
1. README.md (10 min)
   ↓
2. CHANGELOG_V2.md (10 min)
   ↓
3. NOVAS_FUNCIONALIDADES.md (30 min)
   ↓
4. Explorar código-fonte
```

### Administrador de Sistema
```
1. README.md (10 min)
   ↓
2. QUICK_START.md - Instalação (15 min)
   ↓
3. Executar scripts de instalação
   ↓
4. Configurar ambiente de produção
```

---

## 📊 Estatísticas da Documentação

- **Total de Arquivos**: 7 documentos
- **Linhas de Documentação**: ~2.500 linhas
- **Idioma**: Português
- **Última Atualização**: 09/10/2025
- **Versão**: 2.0

---

## ✅ Checklist de Documentação Lida

Use este checklist para acompanhar sua leitura:

### Essenciais
- [ ] README.md
- [ ] QUICK_START.md
- [ ] NOVAS_FUNCIONALIDADES.md

### Guias Específicos
- [ ] GUIA_CRIACAO_JURIS_POR_LOCAL.md
- [ ] TESTE_DRAG_DROP.md

### Referência
- [ ] CHANGELOG_V2.md
- [ ] DOCUMENTACAO_INDEX.md (este arquivo)

---

**Nota**: Toda a documentação está em Português e foi criada para ser clara, objetiva e prática.

**Última Atualização**: 09/10/2025  
**Versão**: 2.0.0
