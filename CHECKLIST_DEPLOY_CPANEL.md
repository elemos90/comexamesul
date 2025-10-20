# ✅ Checklist de Deploy - admissao.cycode.net via cPanel

**Data de Início**: _______________  
**Responsável**: _______________  
**Duração Estimada**: 3-4 horas

---

## 📋 INFORMAÇÕES DO DEPLOY

```
Servidor:         57.128.126.160
Usuário cPanel:   cycodene
Domínio:          admissao.cycode.net
Banco de Dados:   cycodene_comexamesul
Usuário DB:       cycodene_cycodene
Senha DB:         &~Oi)0SXsPNh7$bF
Repositório:      https://github.com/elemos90/comexamesul.git
```

---

## ⏱️ FASE 1: PRÉ-REQUISITOS (15 min)

### Acessos Verificados

- [ ] Acesso cPanel funcionando: `https://cycode.net:2083`
- [ ] Credenciais cPanel confirmadas
- [ ] Acesso SSH disponível (ou Terminal cPanel)
- [ ] Credenciais do banco de dados confirmadas
- [ ] Repositório GitHub acessível

### Verificações Locais

- [ ] PHP 8.1+ disponível no servidor
- [ ] MySQL 8.0+ disponível
- [ ] Extensões PHP necessárias habilitadas
- [ ] Composer disponível (ou preparar upload manual)

**Início**: ___:___

---

## 📂 FASE 2: CLONE DO REPOSITÓRIO (20 min)

### Via SSH/Terminal

```bash
ssh cycodene@57.128.126.160
cd ~
git clone https://github.com/elemos90/comexamesul.git admissao.cycode.net
cd admissao.cycode.net
ls -la
```

- [ ] Conectado via SSH ou Terminal cPanel
- [ ] Repositório clonado em `~/admissao.cycode.net/`
- [ ] Arquivos verificados (app/, public/, config/, etc.)
- [ ] Estrutura de pastas correta

### Alternativa: Upload Manual

Se Git não disponível:

- [ ] Repositório baixado localmente
- [ ] ZIP criado e enviado via FTP/File Manager
- [ ] Arquivos extraídos em `/home/cycodene/admissao.cycode.net/`
- [ ] Estrutura verificada

**Conclusão**: ___:___

---

## 🗄️ FASE 3: CONFIGURAR BANCO DE DADOS (45 min)

### 3.1. Verificar/Criar Banco

**cPanel > MySQL Databases**

- [ ] Banco `cycodene_comexamesul` existe ou foi criado
- [ ] Usuário `cycodene_cycodene` existe ou foi criado
- [ ] Senha confirmada: `&~Oi)0SXsPNh7$bF`
- [ ] Usuário adicionado ao banco com ALL PRIVILEGES
- [ ] Conexão testada via phpMyAdmin

### 3.2. Importar Migrations

**Via phpMyAdmin > Import** (nesta ordem):

- [ ] 1. `migrations.sql` importado ✅
- [ ] 2. `migrations_v2.2.sql` importado ✅
- [ ] 3. `migrations_v2.3.sql` importado ✅
- [ ] 4. `migrations_v2.5.sql` importado ✅
- [ ] 5. `migrations_master_data_simple.sql` importado ✅
- [ ] 6. `migrations_auto_allocation.sql` importado ✅
- [ ] 7. `migrations_triggers.sql` importado ✅
- [ ] 8. `performance_indexes.sql` importado ✅

**Caminho dos arquivos**: `/home/cycodene/admissao.cycode.net/app/Database/`

### 3.3. Criar Usuário Admin

**phpMyAdmin > SQL**:

```sql
INSERT INTO users (name, email, phone, role, password_hash, 
    email_verified_at, available_for_vigilance, supervisor_eligible, 
    created_at, updated_at)
VALUES ('Coordenador Principal', 'coordenador@cycode.net', 
    '+258840000000', 'coordenador',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    NOW(), 0, 1, NOW(), NOW());
```

- [ ] Query executada com sucesso
- [ ] Usuário criado (ID: _____)
- [ ] Credenciais anotadas (email: coordenador@cycode.net / senha: password)

### 3.4. Verificar Tabelas

- [ ] Total de tabelas: _____ (esperado: 15-20)
- [ ] Tabela `users` existe
- [ ] Tabela `juries` existe
- [ ] Tabela `vacancies` existe
- [ ] Views criadas (vw_eligible_vigilantes, etc.)
- [ ] Triggers criados

**Conclusão**: ___:___

---

## ⚙️ FASE 4: CONFIGURAR APLICAÇÃO (30 min)

### 4.1. Criar Arquivo .env

**Via SSH ou File Manager**:

```bash
cd ~/admissao.cycode.net
nano .env
```

- [ ] Arquivo `.env` criado
- [ ] Configuração copiada (ver seção abaixo)
- [ ] Credenciais do banco inseridas corretamente
- [ ] APP_URL configurado: `https://admissao.cycode.net`
- [ ] APP_DEBUG definido como `false`
- [ ] APP_ENV definido como `production`

**Conteúdo mínimo do .env**:

```env
APP_NAME="Portal da Comissão de Exames de Admissão"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admissao.cycode.net
APP_TIMEZONE=Africa/Maputo

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cycodene_comexamesul
DB_USERNAME=cycodene_cycodene
DB_PASSWORD=&~Oi)0SXsPNh7$bF

SESSION_NAME=exam_portal_prod
SESSION_LIFETIME=7200
SESSION_SECURE=true

RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_WINDOW=900
CSRF_TOKEN_KEY=csrf_token
```

### 4.2. Configurar Permissões

```bash
chmod 600 .env
mkdir -p storage/cache storage/logs public/uploads/avatars
chmod -R 775 storage/
chmod -R 775 public/uploads/
```

- [ ] `.env` com permissão 600
- [ ] Diretório `storage/cache/` criado (775)
- [ ] Diretório `storage/logs/` criado (775)
- [ ] Diretório `public/uploads/avatars/` criado (775)

### 4.3. Instalar Composer

**Via SSH**:

```bash
cd ~/admissao.cycode.net
composer install --no-dev --optimize-autoloader
```

- [ ] Comando executado com sucesso
- [ ] Pasta `vendor/` criada
- [ ] Dependências instaladas (Dompdf, PhpSpreadsheet, PHPMailer)

**Alternativa - Upload Manual**:

- [ ] Pasta `vendor/` criada localmente
- [ ] ZIP enviado e extraído no servidor
- [ ] Pasta `vendor/` presente e populada

**Conclusão**: ___:___

---

## 🌍 FASE 5: CONFIGURAR DOMÍNIO E SSL (30 min)

### 5.1. Adicionar Subdomínio

**cPanel > Domains > Subdomains**:

- [ ] Subdomain: `admissao`
- [ ] Domain: `cycode.net`
- [ ] Document Root: `/home/cycodene/admissao.cycode.net/public` ⚠️ IMPORTANTE
- [ ] Subdomínio criado
- [ ] Aguardado propagação DNS (5-10 min)

### 5.2. Verificar DocumentRoot

- [ ] DocumentRoot aponta para pasta `/public/`
- [ ] Verificado via cPanel > Domains

### 5.3. Ativar SSL/HTTPS

**cPanel > SSL/TLS Status**:

- [ ] Domínio `admissao.cycode.net` localizado
- [ ] "Run AutoSSL" clicado
- [ ] Certificado Let's Encrypt emitido
- [ ] Status: ✅ Certificate installed
- [ ] Aguardado 5-10 minutos

### 5.4. Testar Acesso

- [ ] `http://admissao.cycode.net` acessível
- [ ] Redirecionamento HTTP → HTTPS funcionando
- [ ] `https://admissao.cycode.net` acessível
- [ ] Certificado SSL válido (cadeado verde)
- [ ] Página inicial carrega sem erros

**Conclusão**: ___:___

---

## ⏰ FASE 6: CONFIGURAR CRON JOB (15 min)

### Adicionar Cron

**cPanel > Cron Jobs**:

**Configuração**:
- Minuto: `*/30`
- Hora: `*`
- Dia: `*`
- Mês: `*`
- Dia da Semana: `*`

**Comando**:
```
/usr/bin/php /home/cycodene/admissao.cycode.net/app/Cron/check_deadlines.php >> /home/cycodene/logs/cron.log 2>&1
```

- [ ] Cron job adicionado
- [ ] Frequência: A cada 30 minutos
- [ ] Comando correto
- [ ] Diretório `~/logs/` criado
- [ ] Arquivo `~/logs/cron.log` criado

### Verificar Cron

```bash
crontab -l
```

- [ ] Cron listado corretamente
- [ ] Aguardar primeira execução (30 min)

**Conclusão**: ___:___

---

## 🧪 FASE 7: TESTES FUNCIONAIS (45 min)

### 7.1. Teste de Autenticação

**URL**: `https://admissao.cycode.net/login`

- [ ] Página de login carrega
- [ ] Login com: `coordenador@cycode.net` / `password`
- [ ] Dashboard carrega com sucesso
- [ ] Menu de navegação visível
- [ ] Sem erros visíveis

### 7.2. Alterar Senha Padrão

- [ ] Perfil > Alterar Senha acessado
- [ ] Nova senha definida: ________________
- [ ] Senha salva no gerenciador de senhas
- [ ] Logout realizado
- [ ] Login com nova senha bem-sucedido

### 7.3. Testes de Funcionalidades

**Criar Vaga**:
- [ ] Menu > Vagas > Nova Vaga
- [ ] Formulário preenchido
- [ ] Vaga criada com sucesso
- [ ] Vaga listada corretamente

**Criar Júri**:
- [ ] Menu > Júris > Novo Júri
- [ ] Dados preenchidos
- [ ] Júri criado com sucesso

**Sistema de Alocação**:
- [ ] Menu > Júris > Planejamento acessado
- [ ] Interface drag-and-drop carregada
- [ ] Teste de arrastar vigilante
- [ ] Validações funcionando (cores verde/âmbar/vermelho)

**Upload de Arquivo**:
- [ ] Perfil > Upload de Avatar
- [ ] Imagem enviada
- [ ] Avatar atualizado
- [ ] Arquivo salvo em `public/uploads/avatars/`

### 7.4. Verificar Logs

```bash
tail -50 ~/admissao.cycode.net/storage/logs/app.log
tail -50 ~/logs/php_errors.log
```

- [ ] Logs verificados
- [ ] Sem erros críticos (500, fatal)
- [ ] Warnings aceitáveis (se houver)

### 7.5. Performance

- [ ] Homepage carrega em < 3s
- [ ] Dashboard carrega em < 5s
- [ ] Navegação fluida
- [ ] Sem timeouts

**Conclusão**: ___:___

---

## 🔒 FASE 8: SEGURANÇA (20 min)

### 8.1. Verificar Proteção de Arquivos

**Testar acesso** (devem retornar 403 Forbidden):

- [ ] `https://admissao.cycode.net/.env` → ❌ Bloqueado
- [ ] `https://admissao.cycode.net/composer.json` → ❌ Bloqueado
- [ ] `https://admissao.cycode.net/config/database.php` → ❌ Bloqueado
- [ ] `https://admissao.cycode.net/.htaccess` → ❌ Bloqueado

### 8.2. HTTPS Forçado

- [ ] Acesso HTTP redireciona para HTTPS
- [ ] Certificado SSL válido
- [ ] Sem avisos de conteúdo misto (mixed content)

### 8.3. Limpar Dados de Teste

**Via phpMyAdmin** (CUIDADO):

```sql
-- Verificar usuários
SELECT id, name, email, role FROM users;

-- Remover usuários de exemplo (se existirem)
DELETE FROM users WHERE email LIKE '%@example.com';
DELETE FROM users WHERE email LIKE '%@unilicungo.ac.mz';
```

- [ ] Usuários de teste removidos
- [ ] Apenas coordenador principal mantido
- [ ] Dados verificados

### 8.4. Configurações de Segurança

- [ ] `APP_DEBUG=false` no .env
- [ ] `SESSION_SECURE=true` no .env
- [ ] Rate limiting ativo
- [ ] CSRF protection ativo

**Conclusão**: ___:___

---

## 💾 FASE 9: BACKUP E MONITORAMENTO (20 min)

### 9.1. Backup Inicial

**Via SSH**:

```bash
cd ~
tar -czf ~/backups/deploy-inicial-$(date +%Y%m%d).tar.gz admissao.cycode.net/
mysqldump -u cycodene_cycodene -p cycodene_comexamesul > ~/backups/db-inicial-$(date +%Y%m%d).sql
```

- [ ] Diretório `~/backups/` criado
- [ ] Backup de arquivos criado
- [ ] Backup do banco criado
- [ ] Backups baixados para local seguro
- [ ] Tamanho do backup: _____ MB

### 9.2. Backup Automático

**cPanel > Backup > Backup Wizard**:

- [ ] Backup automático ativado
- [ ] Frequência: Diária
- [ ] Destino configurado (email/FTP)
- [ ] Notificações ativadas

### 9.3. Monitoramento

**Configurar UptimeRobot ou similar**:

- [ ] Conta criada em https://uptimerobot.com
- [ ] Monitor adicionado: `https://admissao.cycode.net`
- [ ] Intervalo: 5 minutos
- [ ] Alerta via email configurado
- [ ] Email de teste recebido

**Conclusão**: ___:___

---

## 📝 FASE 10: DOCUMENTAÇÃO (15 min)

### 10.1. Registrar Informações

**Documentar em local seguro** (LastPass, 1Password, etc.):

- [ ] URL do sistema: `https://admissao.cycode.net`
- [ ] Credenciais cPanel
- [ ] Credenciais banco de dados
- [ ] Credenciais admin do sistema
- [ ] Informações do servidor

### 10.2. Contatos

- [ ] Suporte hospedagem: ________________
- [ ] Desenvolvedor: ________________
- [ ] Email de suporte: ________________

### 10.3. Comandos Úteis Salvos

- [ ] Comandos SSH salvos para referência
- [ ] Localização dos logs anotada
- [ ] Procedimentos de backup documentados

**Conclusão**: ___:___

---

## ✅ CHECKLIST FINAL PRÉ-PRODUÇÃO

### Funcionalidades Essenciais

- [ ] Login/Logout funcionando
- [ ] Dashboard carregando
- [ ] Criar vagas funcionando
- [ ] Criar júris funcionando
- [ ] Sistema de candidaturas funcionando
- [ ] Sistema de alocação drag-and-drop funcionando
- [ ] Upload de arquivos funcionando
- [ ] Relatórios/Exportações funcionando (PDF/Excel)

### Segurança

- [ ] HTTPS ativo e funcionando
- [ ] Certificado SSL válido
- [ ] Arquivos sensíveis protegidos
- [ ] Senha admin alterada
- [ ] APP_DEBUG=false
- [ ] SESSION_SECURE=true

### Performance

- [ ] Homepage < 3s
- [ ] Dashboard < 5s
- [ ] Navegação fluida
- [ ] Sem erros 500

### Infraestrutura

- [ ] Banco de dados configurado e populado
- [ ] Dependências Composer instaladas
- [ ] Permissões corretas
- [ ] Cron job ativo
- [ ] Logs verificados (sem erros críticos)
- [ ] Backup inicial criado
- [ ] Monitoramento configurado

---

## 🎉 DEPLOY CONCLUÍDO!

**Data de Conclusão**: _______________  
**Horário**: ___:___  
**Status**: ✅ **SISTEMA EM PRODUÇÃO**

### Informações de Acesso

**Sistema**:
- URL: https://admissao.cycode.net
- Login: coordenador@cycode.net
- Senha: [VER GERENCIADOR DE SENHAS]

**Servidor**:
- cPanel: https://cycode.net:2083
- SSH: ssh cycodene@57.128.126.160
- IP: 57.128.126.160

### Próximos Passos

- [ ] Comunicar go-live aos stakeholders
- [ ] Enviar credenciais aos usuários finais
- [ ] Disponibilizar guia de uso
- [ ] Agendar treinamento (se aplicável)
- [ ] Configurar canal de suporte

---

## 📞 SUPORTE PÓS-DEPLOY

### Comandos Essenciais

```bash
# Conectar
ssh cycodene@57.128.126.160

# Ver logs
tail -f ~/admissao.cycode.net/storage/logs/app.log

# Limpar cache
rm -rf ~/admissao.cycode.net/storage/cache/*

# Backup rápido
tar -czf ~/backup-$(date +%Y%m%d).tar.gz ~/admissao.cycode.net/
mysqldump -u cycodene_cycodene -p cycodene_comexamesul > ~/db-$(date +%Y%m%d).sql
```

### Problemas Comuns

| Problema | Verificar |
|----------|-----------|
| Erro 500 | Logs PHP, permissões, .env |
| DB connection failed | Credenciais .env, usuário MySQL |
| CSS não carrega | DocumentRoot, limpar cache |
| Emails não enviam | Configurações SMTP no .env |

---

**Responsável pelo Deploy**: _______________  
**Assinatura**: _______________  
**Data**: _______________

---

**📅 Criado em**: 20 de Outubro de 2025  
**✅ Status**: Pronto para uso  
**🚀 Versão**: 1.0
