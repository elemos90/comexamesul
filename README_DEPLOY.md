# 🚀 README - Deploy em Produção

## Informações do Sistema

**Projeto**: Portal da Comissão de Exames de Admissão  
**Versão**: 2.5+  
**Ambiente de Produção**: admissao.cycode.net  
**Servidor**: Hospedagem Compartilhada CyCode

---

## 📦 O Que Foi Preparado Para Você

### Documentação de Deploy

1. **DEPLOY_RAPIDO.md** ⚡
   - Guia rápido de 30 minutos
   - 10 passos simples
   - Checklist visual
   - **COMECE POR AQUI!**

2. **GUIA_DEPLOY_PRODUCAO.md** 📖
   - Guia completo e detalhado
   - 11 fases de implementação
   - Troubleshooting extensivo
   - Configurações avançadas

3. **CHECKLIST_DEPLOY.md** ✅
   - Lista de verificação completa
   - Campos para preencher
   - Acompanhamento de progresso
   - Assinaturas e responsabilidades

4. **COMANDOS_PRODUCAO.md** 🖥️
   - Comandos SSH úteis
   - Gerenciamento do servidor
   - Troubleshooting rápido
   - Quick reference

### Arquivos de Configuração

5. **env.production.example** ⚙️
   - Template .env para produção
   - Comentários explicativos
   - Valores pré-configurados
   - Campos marcados para alterar

6. **public/.htaccess.production** 🔒
   - Configuração otimizada para produção
   - Segurança reforçada
   - HTTPS forçado
   - Proteção contra SQL injection

7. **install_production.sql** 🗄️
   - SQL consolidado para instalação
   - Cria usuário administrador
   - Adiciona índices de performance
   - Pronto para importar

### Scripts Utilitários

8. **scripts/pre_deploy_check.php** 🔍
   - Verifica se sistema está pronto
   - Testa dependências
   - Valida estrutura de arquivos
   - Relatório completo

9. **scripts/backup_production.sh** 💾
   - Backup automático
   - Banco + Arquivos
   - Limpeza de backups antigos
   - Pronto para usar via cron

### Documentação Adicional

10. **ANALISE_SUGESTOES_2025.md** 📊
    - Análise completa do código
    - Sugestões de melhorias
    - Roadmap de desenvolvimento
    - Boas práticas

---

## 🎯 Por Onde Começar

### Opção 1: Deploy Rápido (Recomendado)

```
1. Leia: DEPLOY_RAPIDO.md
2. Execute: php scripts/pre_deploy_check.php
3. Siga os 10 passos
4. Tempo: ~30 minutos
```

### Opção 2: Deploy Detalhado

```
1. Leia: GUIA_DEPLOY_PRODUCAO.md
2. Use: CHECKLIST_DEPLOY.md para acompanhar
3. Tempo: ~2-3 horas (mais completo)
```

---

## 📊 Dados de Acesso (Resumo)

### Servidor
```
Domínio:     admissao.cycode.net
Usuário:     cycodene
IP:          57.128.126.160
cPanel:      https://cycode.net:2083
```

### Banco de Dados (Criar no cPanel)
```
Nome:        cycodene_comexames
Usuário:     cycodene_dbuser
Senha:       (você define - anote!)
Host:        localhost
```

### Estrutura de Pastas
```
/home/cycodene/
├── admissao.cycode.net/          ← Projeto aqui
│   ├── app/
│   ├── public/                   ← DocumentRoot
│   ├── storage/
│   ├── .env                      ← Criar este arquivo
│   └── ...
├── backups/                      ← Backups automáticos
└── logs/                         ← Logs do sistema
```

### Primeiro Acesso
```
URL:         https://admissao.cycode.net
Email:       coordenador@admissao.cycode.net
Senha:       password (TROCAR IMEDIATAMENTE!)
```

---

## ⚡ Quick Start (Resumão)

### 1. Preparar Localmente
```bash
php scripts/pre_deploy_check.php
```

### 2. Fazer Upload
- Comprimir projeto (sem vendor/, .env, logs)
- Upload via cPanel File Manager
- Extrair em `/home/cycodene/admissao.cycode.net/`

### 3. Configurar Banco
- cPanel → MySQL Databases
- Criar: cycodene_comexames
- Criar usuário: cycodene_dbuser
- phpMyAdmin → Importar SQLs

### 4. Criar .env
- Copiar conteúdo de `env.production.example`
- Ajustar senhas e credenciais
- Salvar como `.env` no servidor

### 5. Configurar Domínio
- cPanel → Subdomains
- admissao.cycode.net → /public
- SSL/TLS → Run AutoSSL

### 6. Instalar Composer
```bash
ssh cycodene@57.128.126.160
cd ~/admissao.cycode.net
composer install --no-dev --optimize-autoloader
```

### 7. Testar
```
https://admissao.cycode.net
Login → Alterar senha → Testar funcionalidades
```

---

## 🔧 Manutenção Pós-Deploy

### Backup Semanal
```bash
# Via cPanel: Adicionar cron job
0 2 * * 0 /bin/bash /home/cycodene/admissao.cycode.net/scripts/backup_production.sh
```

### Monitoramento
- UptimeRobot: https://uptimerobot.com
- Verificar a cada 5 minutos
- Alertas via email

### Logs
```bash
# Ver erros
tail -f ~/logs/php_errors.log

# Ver atividade
tail -f ~/admissao.cycode.net/storage/logs/app.log
```

---

## 📚 Documentos por Fase

### Antes do Deploy
- ✅ `scripts/pre_deploy_check.php` - Verificação
- ✅ `ANALISE_SUGESTOES_2025.md` - Revisar melhorias

### Durante o Deploy
- ✅ `DEPLOY_RAPIDO.md` - Guia rápido
- ✅ `CHECKLIST_DEPLOY.md` - Acompanhar progresso

### Após o Deploy
- ✅ `COMANDOS_PRODUCAO.md` - Administração diária
- ✅ `scripts/backup_production.sh` - Backups

### Troubleshooting
- ✅ `GUIA_DEPLOY_PRODUCAO.md` (seção 11)
- ✅ `COMANDOS_PRODUCAO.md` (seção Troubleshooting)

---

## 🎓 Recursos de Aprendizado

### PHP e MySQL
- PHP Manual: https://www.php.net/manual/pt_BR/
- MySQL Documentation: https://dev.mysql.com/doc/

### cPanel
- cPanel Docs: https://docs.cpanel.net/
- Video Tutorials: YouTube "cPanel Tutorial"

### Segurança
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- Let's Encrypt: https://letsencrypt.org/

---

## 🆘 Suporte

### Problemas Técnicos
1. Consultar: `COMANDOS_PRODUCAO.md` (seção Troubleshooting)
2. Verificar logs: `~/logs/php_errors.log`
3. Documentação: `GUIA_DEPLOY_PRODUCAO.md`

### Hospedagem
- Email: suporte@cycode.net
- cPanel: https://cycode.net:2083

### Desenvolvedor
- Documentação completa no repositório
- Issues: Criar na plataforma de controle de versão

---

## ⚠️ Avisos Importantes

### Segurança
```
❗ TROCAR senha padrão imediatamente após primeiro login
❗ Usar HTTPS (SSL obrigatório em produção)
❗ Não commitar .env no Git
❗ Fazer backup antes de qualquer atualização
❗ Manter dependências atualizadas
```

### Performance
```
✓ Índices de BD aplicados via install_production.sql
✓ Cache configurado em storage/cache/
✓ Compressão GZIP ativada no .htaccess
✓ Imagens otimizadas recomendado
```

### Backup
```
✓ Backup automático via cron (scripts/backup_production.sh)
✓ Backup manual antes de updates
✓ Testar restauração periodicamente
✓ Armazenar backups em local seguro externo
```

---

## 📈 Próximos Passos Após Deploy

### Imediato (Dia 1)
- [ ] Alterar senha do administrador
- [ ] Configurar backup automático
- [ ] Configurar monitoramento (UptimeRobot)
- [ ] Testar envio de emails
- [ ] Criar usuários de teste

### Primeira Semana
- [ ] Monitorar logs diariamente
- [ ] Verificar performance
- [ ] Treinar usuários principais
- [ ] Coletar feedback inicial
- [ ] Ajustar configurações conforme necessário

### Primeiro Mês
- [ ] Revisar e implementar melhorias de `ANALISE_SUGESTOES_2025.md`
- [ ] Implementar testes automatizados
- [ ] Otimizar queries lentas
- [ ] Documentar processos internos
- [ ] Planejar features futuras

---

## 📞 Contatos Essenciais

### Informações do Sistema
```
Ambiente:    Produção
URL:         https://admissao.cycode.net
Versão:      2.5+
Framework:   PHP 8.1+ Custom MVC
Database:    MySQL 8.0+
```

### Acessos
```
cPanel:      https://cycode.net:2083
Email Admin: coordenador@admissao.cycode.net
phpMyAdmin:  Via cPanel
SSH:         cycodene@57.128.126.160
```

---

## ✅ Checklist Mínimo para Go-Live

```
[ ] Pre-deploy check passou
[ ] Arquivos enviados para servidor
[ ] Banco de dados criado e populado
[ ] .env configurado corretamente
[ ] Composer dependencies instaladas
[ ] Domínio apontando para /public
[ ] SSL/HTTPS ativo
[ ] Primeiro login funcional
[ ] Senha admin alterada
[ ] Emails testados e funcionando
[ ] Backup inicial criado
[ ] Monitoramento configurado
```

---

## 🎉 Conclusão

Você tem agora um **kit completo de deploy** com:

✅ **4 guias de deploy** (rápido, completo, checklist, comandos)  
✅ **3 arquivos de configuração** (env, htaccess, sql)  
✅ **2 scripts utilitários** (verificação, backup)  
✅ **1 análise completa** do sistema

**Tudo pronto para colocar seu sistema no ar!**

Siga o `DEPLOY_RAPIDO.md` para começar agora mesmo.

Boa sorte! 🚀

---

**Preparado em**: 17 de Outubro de 2025  
**Para**: Produção admissao.cycode.net  
**Responsável**: Equipe de Desenvolvimento  
**Status**: ✅ Pronto para Deploy
