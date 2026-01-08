# Portal da Comissão de Exames de Admissão

Projeto em PHP 8 com MVC simples, MySQL 8 e front-end com Tailwind (CDN). O sistema suporta gestão de vagas de vigilância, candidaturas, júris com drag-and-drop, relatórios de supervisores e exportações em PDF/XLS.

---

## 🚀 **DEPLOY EM PRODUÇÃO - admissao.cycode.net**

📌 **[COMECE AQUI: README_DEPLOY.md](README_DEPLOY.md)** - Guia completo de deploy

### Quick Links

- ⚡ **[Deploy Rápido (30 min)](DEPLOY_RAPIDO.md)** - Instruções passo a passo
- ✅ **[Checklist de Deploy](CHECKLIST_DEPLOY.md)** - Acompanhe o progresso
- 🖥️ **[Comandos de Produção](COMANDOS_PRODUCAO.md)** - Administração diária
- 👋 **[Guia de Primeiro Acesso](GUIA_PRIMEIRO_ACESSO.md)** - Para usuários
- ❓ **[FAQ e Troubleshooting](FAQ_TROUBLESHOOTING.md)** - Problemas comuns
- 📚 **[Índice Completo](INDICE_DOCUMENTACAO.md)** - Toda a documentação

**Informações do Servidor:**
```
Domínio:  admissao.cycode.net
Usuário:  cycodene
IP:       57.128.126.160
```

---

## Requisitos
- PHP 8.1+
- MySQL 8+
- Composer (para instalar Dompdf e PhpSpreadsheet)
- Extensões PHP: `pdo_mysql`, `mbstring`, `json`, `fileinfo`

## Instalação
1. Clone o repositório no servidor de hospedagem compartilhada.
2. Copie `.env.example` para `.env` e ajuste credenciais de base de dados, URL e e-mail.
3. Instale dependências:
   ```bash
   composer install
   ```
4. Aponte o DocumentRoot para `public/` ou crie regra `.htaccess` (já incluída).

## Base de Dados
1. Crie a base de dados no MySQL.
2. Execute as migrações e seeds locais:
   ```bash
   mysql -u usuario -p base < app/Database/migrations.sql
   mysql -u usuario -p base < app/Database/seed.sql
   ```
3. **NOVO**: Instale as funcionalidades de Locais (Templates, Import/Export, Dashboard):
   ```bash
   php scripts/install_locations_features.php
   ```
4. Para instalar diretamente na base remota informada no `.env`, utilize:
   ```bash
   php scripts/install_remote.php
   ```

Credenciais de exemplo (seed):
- Coordenador: `coordenador@unilicungo.ac.mz` / `password`
- Membro: `membro@unilicungo.ac.mz` / `password`
- Vigilantes: `vigilante1@unilicungo.ac.mz` / `password`

## Cron de fecho automático
Configure o cron de 30 em 30 minutos (ajuste conforme necessário):
```bash
*/30 * * * * /usr/bin/php /caminho/do/projeto/app/Cron/check_deadlines.php >> /caminho/do/projeto/storage/logs/cron.log 2>&1
```

## Exportações (PDF/XLS)
- Requer `dompdf/dompdf` e `phpoffice/phpspreadsheet` (instalados via Composer).
- Endpoints disponíveis (apenas membros/comissão):
  - `/exports/vigilantes.xls` e `/exports/vigilantes.pdf`
  - `/exports/supervisores.xls` e `/exports/supervisores.pdf`
  - `/exports/vigias.xls` e `/exports/vigias.pdf`

## Estrutura
```
app/
  Controllers/   # Controladores MVC
  Models/        # Modelos (PDO)
  Services/      # Serviços (exportação, relatórios)
  Utils/         # Helpers (Auth, CSRF, etc.)
  Views/         # Layouts Tailwind + modais acessíveis
  Cron/          # Scripts agendados
  Database/      # Migrations e seeds
public/          # index.php + assets
scripts/         # Instalador remoto
```

## Segurança
- Hash de senhas com `password_hash`
- CSRF token em todas as ações POST
- Rate limit simples de login por IP (`storage/cache`)
- Sessões com `httponly` e `samesite=Lax`

## Drag-and-drop e Acessibilidade
- SortableJS (CDN) para alocar vigilantes e supervisores.
- Modais com foco inicial, `Esc` para fechar, `aria-hidden` controlado por JS.
- Toasts com aria-live (`toastr`).

## 🆕 Novas Funcionalidades (v2.0)

### 1. Visualização por Local
Júris agrupados por local de realização com estrutura hierárquica (Local → Data → Disciplina → Salas).
- **Acesso**: Menu → Locais → Vis por Local

### 2. Templates de Locais
Salve configurações de locais para reutilização em futuras sessões de exames.
- **Acesso**: Menu → Locais → Templates
- **Funcionalidades**: Criar, usar, ativar/desativar, eliminar templates

### 3. Import/Export de Planilhas
Importe júris em massa via Excel/CSV. Baixe template pré-formatado.
- **Acesso**: Menu → Locais → Importar
- **Formatos**: XLSX, XLS, CSV

### 4. Dashboard de Locais
Estatísticas agregadas e análise de dados por local.
- **Acesso**: Menu → Locais → Dashboard
- **Dados**: Top locais, breakdown por data, totais de júris/candidatos/vigilantes

**📚 Documentação Completa**: Ver `NOVAS_FUNCIONALIDADES.md` e `GUIA_CRIACAO_JURIS_POR_LOCAL.md`

---

## ✨ Sistema de Alocação Drag-and-Drop (v2.1)

### Planejamento Inteligente de Júris
Interface visual completa para alocar vigilantes e supervisores com **arrastar e soltar**.

#### 🎯 Funcionalidades Principais
- **Drag-and-Drop Intuitivo**: Arraste pessoas diretamente para os júris
- **Validação em Tempo Real**: Feedback visual (verde/âmbar/vermelho)
- **Prevenção de Conflitos**: Impede alocação em horários sobrepostos
- **Equilíbrio de Carga**: Algoritmo Greedy distribui tarefas equilibradamente
- **Auto-Alocação**: Preencha júris automaticamente com um clique
- **Métricas KPI**: Dashboard com desvio padrão, ocupação e qualidade

#### 🚀 Como Usar
1. **Instalar Sistema**:
   ```bash
   php scripts/run_allocation_migration.php
   php scripts/verify_allocation_system.php
   ```

2. **Acessar Interface**:
   - URL: `http://localhost/juries/planning`
   - Menu: **Júris → Planejamento**

3. **Alocar Manualmente**:
   - Arraste vigilante da lista para a zona do júri
   - Observe feedback visual de validação
   - Verde = OK | Âmbar = Aviso | Vermelho = Bloqueado

4. **Auto-Alocação**:
   - **Rápido**: Botão "Auto" em cada júri
   - **Completo**: Botão "⚡ Auto-Alocar Completo" por disciplina

#### 📊 Algoritmo de Equilíbrio
- **Score de Carga**: Vigilância = 1 ponto, Supervisão = 2 pontos
- **Heurística Greedy**: Prioriza pessoas com menor carga
- **Desvio Padrão**: Monitora distribuição (≤1.0 = Excelente)
- **Badges Visuais**: Cores indicam nível de carga

#### 🗄️ Componentes do Sistema
- **5 Views SQL**: Carga, slots, elegibilidade, estatísticas
- **3 Triggers**: Validação de capacidade e conflitos
- **9 Endpoints API**: Validação, alocação, métricas
- **Interface Responsiva**: Tailwind CSS + SortableJS

**📚 Documentação Detalhada**:
- `SISTEMA_ALOCACAO_DND.md` - Manual completo
- `INSTALACAO_DND.md` - Guia de instalação
- Scripts: `run_allocation_migration.php`, `verify_allocation_system.php`

## Notas finais
- Logs do sistema: `storage/logs`.
- Uploads de avatar: `public/uploads/avatars`.
- Ajuste `APP_URL` e `APP_TIMEZONE` conforme o ambiente.
- Atualize os dados seeds para produção.
