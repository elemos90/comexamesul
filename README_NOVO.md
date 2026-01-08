# Portal da Comissão de Exames de Admissão

Sistema web para gestão de exames de admissão universitária da **UniLicungo**. Permite gerenciar vagas de vigilância, candidaturas, criação de júris com drag-and-drop, alocação automática e relatórios PDF/XLS.

**📍 Produção:** [admissao.cycode.net](https://admissao.cycode.net)  
**📦 Versão:** 2.6  
**🔧 Stack:** PHP 8.1+ | MySQL 8 | Tailwind CSS

---

## 🚀 Quick Start

### Novo no Projeto?
1. **[Começar Aqui →](docs/01-getting-started/QUICK_START.md)** - Instalação e configuração
2. **[Primeiro Acesso →](docs/01-getting-started/GUIA_PRIMEIRO_ACESSO.md)** - Para usuários
3. **[Guia Rápido →](docs/01-getting-started/GUIA_RAPIDO_REFERENCIA.md)** - Referência rápida

### Já Conhece o Sistema?
- **[Deploy em Produção →](docs/03-deployment/DEPLOY_RAPIDO.md)** - 30 minutos
- **[Documentação Completa →](docs/README.md)** - Índice organizado
- **[FAQ & Troubleshooting →](docs/03-deployment/FAQ_TROUBLESHOOTING.md)** - Resolução de problemas

---

## 📚 Documentação Organizada

A documentação está agora organizada em categorias:

### 🎯 [01 - Getting Started](docs/01-getting-started/)
Primeiros passos, instalação, configuração inicial
- Quick Start
- Guia de Primeiro Acesso
- Referência Rápida

### 💻 [02 - Development](docs/02-development/)
Desenvolvimento, testes, arquitetura
- Design System
- Análise do Codebase
- Guias de Teste

### 🚀 [03 - Deployment](docs/03-deployment/)
Deploy, migrações, troubleshooting
- Deploy Rápido (30min)
- Guia de Produção
- Checklists
- FAQ & Troubleshooting

### 👥 [04 - User Guides](docs/04-user-guides/)
Guias para utilizadores do sistema
- Guias por Perfil (Vigilante, Coordenador, Membro)
- Alocação de Equipes
- Criação de Júris
- Sistema de Candidaturas

### 📖 [05 - API Reference](docs/05-api-reference/)
Referência técnica, APIs, funcionalidades
- Sistema de Alocação Drag-and-Drop
- Auto-Allocation
- Smart Suggestions
- Novas Funcionalidades

### 📝 [Changelog](docs/changelog/)
Histórico de versões e implementações
- CHANGELOG v2.x
- Implementações por versão

### 📦 [Archive](docs/archive/)
Documentos históricos (65 documentos)

---

## ✨ Funcionalidades Principais

### 🎯 Gestão de Vagas
- Criação/edição com deadline automático
- Estados: aberta, fechada, encerrada
- Fecho automático via cron

### 📋 Sistema de Candidaturas
- Candidatura a vagas específicas
- Dashboard com análise e gráficos
- Aprovação/rejeição em massa
- Cancelamento justificado

### 👨‍⚖️ Criação de Júris
- Por vaga, local ou lote
- Import Excel em massa
- Templates reutilizáveis
- Agrupamento hierárquico

### 🎨 Alocação Drag-and-Drop
- Interface visual SortableJS
- Validação tempo real
- Auto-alocação inteligente
- Métricas KPI

### 📊 Relatórios
- Exportação PDF/Excel
- Dashboards estatísticas
- Relatórios de supervisores

---

## 🛠️ Tecnologias

**Backend**
- PHP 8.1+ (strict types, MVC customizado)
- PDO MySQL 8
- Composer (Dompdf, PHPSpreadsheet, PHPMailer)

**Frontend**
- Tailwind CSS 3 (responsivo, mobile-first)
- Vanilla JavaScript (modular)
- SortableJS (drag-and-drop)

**Segurança**
- ✅ CSRF Protection
- ✅ XSS Prevention
- ✅ SQL Injection (prepared statements)
- ✅ Rate Limiting (brute force)
- ✅ RBAC (3 níveis: vigilante, membro, coordenador)

---

## ⚡ Instalação Rápida

### Requisitos
- PHP 8.1+
- MySQL 8+
- Composer
- Extensões: `pdo_mysql`, `mbstring`, `json`, `fileinfo`

### Passos

```bash
# 1. Clonar repositório
git clone https://github.com/unilicungo/portal-comissao-exames.git
cd portal-comissao-exames

# 2. Configurar ambiente
cp .env.example .env
# Editar .env com suas credenciais

# 3. Instalar dependências
composer install

# 4. Criar base de dados
mysql -u root -p
CREATE DATABASE comexamesul;
exit;

# 5. Executar migrations
mysql -u root -p comexamesul < app/Database/migrations.sql
mysql -u root -p comexamesul < app/Database/seed.sql

# 6. Instalar funcionalidades
php scripts/install_locations_features.php

# 7. Configurar cron (fecho automático)
# Adicionar ao crontab:
*/30 * * * * /usr/bin/php /caminho/do/projeto/app/Cron/check_deadlines.php
```

**Credenciais Padrão (seed):**
- Coordenador: `coordenador@unilicungo.ac.mz` / `password`
- Membro: `membro@unilicungo.ac.mz` / `password`
- Vigilante: `vigilante1@unilicungo.ac.mz` / `password`

**📖 Guia Completo:** [docs/01-getting-started/QUICK_START.md](docs/01-getting-started/QUICK_START.md)

---

## 🚀 Deploy em Produção

**Para deploy em produção (cPanel):**

1. **[Deploy Rápido (30min) →](docs/03-deployment/DEPLOY_RAPIDO.md)**
2. **[Checklist Completo →](docs/03-deployment/CHECKLIST_DEPLOY.md)**
3. **[Comandos de Produção →](docs/03-deployment/COMANDOS_PRODUCAO.md)**

**Servidor Produção:**
```
Domínio:  admissao.cycode.net
Usuário:  cycodene
IP:       57.128.126.160
```

---

## 🧪 Desenvolvimento

### Estrutura do Projeto
```
comexamesul/
├── app/
│   ├── Controllers/     # 18 controladores MVC
│   ├── Models/          # 17 modelos
│   ├── Views/           # 43 views
│   ├── Services/        # Lógica de negócio
│   ├── Utils/           # Helpers
│   ├── Middlewares/     # Auth, CSRF, RBAC
│   └── Database/        # Migrations e seeds
├── docs/                # 📚 Documentação organizada
├── public/              # Entry point + assets
├── scripts/             # Scripts de manutenção
└── tests/               # Testes automatizados
```

### Executar Testes
```bash
# Testes unitários
php scripts/run_tests.php

# Testes de performance
php scripts/test_performance.php

# Verificar alocação
php scripts/verify_allocation_system.php
```

**📖 Guia de Desenvolvimento:** [docs/02-development/](docs/02-development/)

---

## 🤝 Contribuir

### Workflow
1. Fork o repositório
2. Criar branch feature (`git checkout -b feature/NovaFuncionalidade`)
3. Commit changes (`git commit -m 'Add: Nova funcionalidade'`)
4. Push to branch (`git push origin feature/NovaFuncionalidade`)
5. Abrir Pull Request

### Code Style
- PHP: PSR-12, strict types
- JavaScript: ES6+, camelCase
- CSS: Tailwind utility classes

### Antes de Commitar
```bash
# Verificar sintaxe PHP
php -l app/Controllers/*.php

# Executar testes
php scripts/run_tests.php
```

---

## 📞 Suporte

### Documentação
- **Índice Completo:** [docs/README.md](docs/README.md)
- **FAQ:** [docs/03-deployment/FAQ_TROUBLESHOOTING.md](docs/03-deployment/FAQ_TROUBLESHOOTING.md)
- **Troubleshooting:** [docs/03-deployment/TROUBLESHOOTING_503.md](docs/03-deployment/TROUBLESHOOTING_503.md)

### Contato
- **Email:** suporte@unilicungo.ac.mz
- **Issues:** [GitHub Issues](https://github.com/unilicungo/portal-comissao-exames/issues)

---

## 📜 Licença

Este projeto é propriedade da **Universidade Licungo** e é de uso interno institucional.

---

## 🎉 Agradecimentos

Desenvolvido com ❤️ para a **UniLicungo**

**Equipe de Desenvolvimento:**
- Análise & Arquitetura: Cascade AI
- Implementação: Equipe UniLicungo

---

**📌 Links Rápidos:**
- [Documentação Completa](docs/README.md)
- [Quick Start](docs/01-getting-started/QUICK_START.md)
- [Deploy Produção](docs/03-deployment/DEPLOY_RAPIDO.md)
- [Changelog](docs/changelog/CHANGELOG_V2.md)
- [Análise Técnica](docs/02-development/ANALISE_CODEBASE_2025.md)
