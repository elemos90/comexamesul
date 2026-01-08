# ✅ Checklist de Deploy - admissao.cycode.net

**Data de Início**: _____________  
**Responsável**: _____________  
**Ambiente**: Produção

---

## 📅 Fase 1: Preparação Local (1-2 horas)

### Verificações Pré-Deploy

- [ ] Executar `php scripts/pre_deploy_check.php`
- [ ] Todos os testes críticos passando
- [ ] Backup completo do banco de dados local criado
- [ ] Documentação de usuário revisada e atualizada
- [ ] Credenciais de produção anotadas em local seguro

### Preparar Arquivos

- [ ] Criar arquivo `env.production.example` com dados corretos
- [ ] Verificar que `.env` está no `.gitignore`
- [ ] Remover arquivos de teste (`test_*.php`, `debug_*.php`)
- [ ] Limpar pasta `storage/logs/`
- [ ] Limpar pasta `storage/cache/`
- [ ] Verificar que `vendor/` não será enviado (fazer upload depois)

### Compressão

- [ ] Comprimir projeto em `comexamesul-deploy.zip`
- [ ] Excluir: `vendor/`, `storage/logs/*.log`, `public/uploads/*`, `.env`
- [ ] Tamanho do arquivo: ~_________ MB
- [ ] Verificar integridade do arquivo ZIP

---

## 🌐 Fase 2: Configuração do Servidor (2-3 horas)

### Acesso ao Servidor

- [ ] Acesso cPanel funcionando (`https://cycode.net:2083`)
- [ ] Acesso FTP/SFTP funcionando
- [ ] Acesso SSH funcionando (opcional)
- [ ] Credenciais anotadas e testadas

**Credenciais de Acesso:**
- Usuário: `cycodene`
- IP: `57.128.126.160`
- Domínio: `admissao.cycode.net`

### Upload de Arquivos

- [ ] Criar pasta `/home/cycodene/admissao.cycode.net/`
- [ ] Upload do arquivo `comexamesul-deploy.zip`
- [ ] Descompactar no servidor
- [ ] Verificar estrutura de pastas correta
- [ ] Todos os arquivos presentes (checar via File Manager)

### Permissões de Pastas

- [ ] `chmod 775 storage/`
- [ ] `chmod 775 storage/logs/`
- [ ] `chmod 775 storage/cache/`
- [ ] `chmod 775 public/uploads/`
- [ ] `chmod 775 public/uploads/avatars/`
- [ ] `chmod 600 .env` (depois de criar)

---

## 🗄️ Fase 3: Banco de Dados (1 hora)

### Criar Banco de Dados

- [ ] cPanel > MySQL Databases
- [ ] Criar banco: `cycodene_comexames`
- [ ] Criar usuário: `cycodene_dbuser`
- [ ] Senha forte gerada e anotada: ______________
- [ ] Usuário adicionado ao banco com ALL PRIVILEGES
- [ ] Testar conexão via phpMyAdmin

### Importar Estrutura

Via phpMyAdmin, importar nesta ordem:

- [ ] 1. `app/Database/migrations.sql`
- [ ] 2. `app/Database/migrations_v2.2.sql`
- [ ] 3. `app/Database/migrations_v2.3.sql`
- [ ] 4. `app/Database/migrations_v2.5.sql`
- [ ] 5. `app/Database/migrations_master_data_simple.sql`
- [ ] 6. `app/Database/migrations_auto_allocation.sql`
- [ ] 7. `app/Database/migrations_triggers.sql`
- [ ] 8. `app/Database/performance_indexes.sql`
- [ ] 9. `install_production.sql` (usuário admin + índices)

### Verificar Tabelas

- [ ] Total de tabelas: ~15+
- [ ] Tabela `users` criada
- [ ] Tabela `juries` criada
- [ ] Tabela `jury_vigilantes` criada
- [ ] Tabela `vacancy_applications` criada
- [ ] Views criadas (vw_eligible_vigilantes, etc.)
- [ ] Triggers criados
- [ ] Usuário coordenador criado

---

## ⚙️ Fase 4: Configuração da Aplicação (30 min)

### Arquivo .env

- [ ] Copiar `env.production.example` para `.env`
- [ ] Configurar `APP_URL=https://admissao.cycode.net`
- [ ] Configurar `APP_ENV=production`
- [ ] Configurar `APP_DEBUG=false`
- [ ] Configurar credenciais do banco de dados
- [ ] Configurar SMTP (Gmail App Password ou email domínio)
- [ ] Configurar `SESSION_SECURE=true`
- [ ] Salvar arquivo `.env` no servidor
- [ ] Verificar permissões: `chmod 600 .env`

### Dependências Composer

**Via SSH (recomendado):**
- [ ] `cd ~/admissao.cycode.net`
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] Verificar pasta `vendor/` criada
- [ ] Verificar autoload funcionando

**Ou via upload manual:**
- [ ] Executar `composer install` localmente
- [ ] Fazer upload da pasta `vendor/` completa via FTP
- [ ] Verificar tamanho: ~30-50 MB

---

## 🔒 Fase 5: Domínio e SSL (30 min)

### Configurar Subdomínio

- [ ] cPanel > Domains > Subdomains
- [ ] Adicionar: `admissao.cycode.net`
- [ ] Document Root: `/home/cycodene/admissao.cycode.net/public`
- [ ] Aguardar propagação DNS (5-10 min)

### Ativar SSL/HTTPS

- [ ] cPanel > SSL/TLS Status
- [ ] Selecionar `admissao.cycode.net`
- [ ] Run AutoSSL (Let's Encrypt)
- [ ] Aguardar emissão do certificado (5-10 min)
- [ ] Verificar certificado ativo: ícone de cadeado verde

### Testar Acesso

- [ ] Acessar `http://admissao.cycode.net` (deve redirecionar para HTTPS)
- [ ] Acessar `https://admissao.cycode.net`
- [ ] Página inicial carrega sem erros
- [ ] Sem avisos de certificado SSL

---

## ⏰ Fase 6: Cron Jobs (15 min)

### Configurar Cron

- [ ] cPanel > Cron Jobs
- [ ] Adicionar novo cron job:
  - **Comando**: `/usr/bin/php /home/cycodene/admissao.cycode.net/app/Cron/check_deadlines.php >> /home/cycodene/logs/cron.log 2>&1`
  - **Minuto**: `*/30`
  - **Hora**: `*`
  - **Dia**: `*`
  - **Mês**: `*`
  - **Dia da semana**: `*`
- [ ] Salvar cron job
- [ ] Aguardar primeira execução (30 min)

### Verificar Logs

- [ ] Verificar arquivo `~/logs/cron.log` criado
- [ ] Primeira execução sem erros

---

## 🧪 Fase 7: Testes em Produção (1 hora)

### Autenticação

- [ ] Acessar página de login: `https://admissao.cycode.net/login`
- [ ] Login com: `coordenador@admissao.cycode.net` / `password`
- [ ] Dashboard carrega corretamente
- [ ] Logout funciona
- [ ] Registro de novo usuário funciona
- [ ] Recuperação de senha funciona (testar email)

### Funcionalidades Principais

- [ ] Criar vaga de vigilância
- [ ] Aplicar para vaga (criar usuário vigilante)
- [ ] Aprovar candidatura
- [ ] Criar júri
- [ ] Alocar vigilante via drag-and-drop
- [ ] Sistema de auto-alocação funciona
- [ ] Relatórios PDF/Excel funcionam
- [ ] Upload de avatar funciona

### Performance

- [ ] Tempo de carregamento da home < 3s
- [ ] Dashboard carrega < 5s
- [ ] Sem erros 500
- [ ] Sem warnings PHP visíveis

### Emails

- [ ] Email de registro enviado
- [ ] Email de recuperação de senha enviado
- [ ] Email de notificação de alocação enviado
- [ ] Emails chegam na caixa de entrada (não spam)

---

## 🔒 Fase 8: Segurança Pós-Deploy (30 min)

### Alterar Senhas Padrão

- [ ] Alterar senha do coordenador via interface web
- [ ] Nova senha forte anotada em gerenciador de senhas
- [ ] Testar login com nova senha

### Limpeza

- [ ] Remover usuários de teste do banco
- [ ] Remover júris de teste
- [ ] Limpar logs antigos
- [ ] Verificar ausência de arquivos `test_*.php` em public/

### Verificar Logs

- [ ] Verificar `storage/logs/` - sem erros críticos
- [ ] Verificar `~/logs/php_errors.log` - sem erros
- [ ] Verificar cPanel > Metrics > Errors - sem erros 500

---

## 📊 Fase 9: Backup e Monitoramento (30 min)

### Backup Inicial

- [ ] Backup completo do banco via phpMyAdmin
- [ ] Backup dos arquivos via cPanel Backup Wizard
- [ ] Arquivos de backup baixados e armazenados localmente
- [ ] Testar restauração do backup (opcional)

### Configurar Monitoramento

- [ ] Configurar UptimeRobot ou similar
- [ ] URL monitorada: `https://admissao.cycode.net`
- [ ] Intervalo: 5 minutos
- [ ] Alerta via email configurado

### Configurar Backup Automático

- [ ] cPanel > Backup > Backup Wizard
- [ ] Ativar backups diários automáticos
- [ ] Destino: Google Drive ou email
- [ ] Testar recebimento do primeiro backup

---

## 📝 Fase 10: Documentação e Handover (1 hora)

### Documentação

- [ ] Atualizar README.md com informações de produção
- [ ] Documentar credenciais em local seguro (LastPass, 1Password, etc.)
- [ ] Criar guia rápido para usuários finais
- [ ] Anotar detalhes técnicos (versão PHP, MySQL, etc.)

### Informações para Suporte

**Servidor:**
- Hospedagem: CyCode
- cPanel: `https://cycode.net:2083`
- Usuário: `cycodene`
- IP: `57.128.126.160`

**Aplicação:**
- URL: `https://admissao.cycode.net`
- Versão: 2.5+
- PHP: 8.1+
- MySQL: 8.0+

**Contatos:**
- Suporte hospedagem: _____________
- Desenvolvedor: _____________
- Admin sistema: coordenador@admissao.cycode.net

---

## ✅ Checklist Final

### Antes de Anunciar

- [ ] Todos os testes acima passando
- [ ] SSL/HTTPS ativo e funcionando
- [ ] Emails sendo enviados corretamente
- [ ] Performance aceitável (< 3s)
- [ ] Backup inicial criado
- [ ] Senhas padrão alteradas
- [ ] Documentação completa
- [ ] Equipe treinada (se aplicável)

### Comunicação

- [ ] Anunciar go-live para stakeholders
- [ ] Enviar credenciais de acesso aos usuários
- [ ] Disponibilizar guia de uso
- [ ] Configurar canal de suporte (email, WhatsApp, etc.)

---

## 🎉 SISTEMA EM PRODUÇÃO!

**Data de Go-Live**: _____________  
**Horário**: _____________  
**Status**: ✅ ATIVO

**URL**: https://admissao.cycode.net  
**Login Admin**: coordenador@admissao.cycode.net  

---

## 📞 Suporte e Manutenção

### Logs para Verificar

```bash
# Via SSH
tail -f ~/logs/php_errors.log
tail -f ~/admissao.cycode.net/storage/logs/app.log
tail -f ~/logs/cron.log
```

### Comandos Úteis

```bash
# Limpar cache
rm -rf ~/admissao.cycode.net/storage/cache/*

# Backup manual
cd /home/cycodene
tar -czf backup-$(date +%Y%m%d).tar.gz admissao.cycode.net/
mysqldump -u cycodene_dbuser -p cycodene_comexames > db-$(date +%Y%m%d).sql
```

### Problemas Comuns

| Problema | Solução |
|----------|---------|
| Erro 500 | Verificar logs PHP, permissões, .env |
| DB connection failed | Verificar credenciais .env, usuário MySQL |
| CSS não carrega | Verificar DocumentRoot, limpar cache |
| Emails não enviam | Verificar SMTP, testar credenciais Gmail |
| Cron não executa | Verificar caminho absoluto, permissões |

---

**Responsável pelo Deploy**: _____________  
**Data de Conclusão**: _____________  
**Assinatura**: _____________
