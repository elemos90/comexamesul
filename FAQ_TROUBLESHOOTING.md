# ❓ FAQ e Troubleshooting - admissao.cycode.net

Perguntas frequentes e soluções de problemas comuns.

---

## 🔐 Autenticação e Acesso

### P: Esqueci minha senha, como recuperar?

**R:** 
1. Acesse https://admissao.cycode.net/login
2. Clique em "Esqueceu sua senha?"
3. Digite seu email cadastrado
4. Verifique sua caixa de email (e spam!)
5. Clique no link recebido
6. Defina uma nova senha

### P: Não recebi o email de recuperação de senha

**R:**
1. Verifique a pasta de spam/lixo eletrônico
2. Aguarde 5-10 minutos (pode haver atraso)
3. Verifique se o email está correto
4. Tente novamente
5. Se persistir, contacte o coordenador

### P: Erro "Credenciais inválidas" mas tenho certeza da senha

**R:**
1. Verifique Caps Lock
2. Copie e cole a senha (evita erros de digitação)
3. Tente recuperar senha
4. Limpe cache do navegador
5. Tente em modo anônimo/incógnito

### P: "Muitas tentativas de login. Aguarde alguns minutos"

**R:**
- Sistema de segurança bloqueou temporariamente
- Aguarde 15 minutos
- Depois tente novamente
- Se esqueceu senha, use recuperação

---

## 👤 Perfil e Cadastro

### P: Como faço para completar meu perfil?

**R:**
1. Login no sistema
2. Clique no avatar (canto superior direito)
3. Selecione "Perfil"
4. Preencha todos os campos obrigatórios:
   - Telefone: +258 8X XXX XXXX
   - NUIT: 9 dígitos
   - NIB: 23 dígitos
   - Nome do Banco
5. Clique "Atualizar Perfil"

### P: NUIT não é aceito - "deve ter 9 dígitos"

**R:**
- NUIT deve ter exatamente 9 números
- Não use espaços, traços ou letras
- Exemplo correto: `123456789`
- Exemplo errado: `123.456.789` ou `12345678`

### P: NIB não é aceito - "deve ter 23 dígitos"

**R:**
- NIB moçambicano tem 23 dígitos
- Não use espaços ou traços
- Exemplo correto: `12345678901234567890123`
- Copie do seu extrato bancário

### P: Telefone não é aceito

**R:**
- Use formato moçambicano: `+258 8X XXX XXXX`
- Operadoras válidas: 82, 83, 84, 85, 86, 87
- Exemplos corretos:
  - `+258841234567`
  - `+258 84 123 4567`
- Exemplos errados:
  - `841234567` (falta +258)
  - `+258 91 123 4567` (91 não é válido)

### P: Não consigo fazer upload do avatar

**R:**
1. Verificar formato: JPG ou PNG
2. Tamanho máximo: 2MB
3. Se imagem for grande, comprima em: https://tinypng.com
4. Evite GIF, BMP, TIFF

---

## 📝 Vagas e Candidaturas

### P: Não vejo vagas disponíveis

**R:**
- Pode não haver vagas abertas no momento
- Vagas aparecem quando criadas pelo coordenador
- Verifique com a coordenação
- Prazo da vaga pode ter expirado

### P: Botão "Candidatar-me" está desabilitado

**R:**
1. **Perfil incompleto**: Complete NUIT, NIB, telefone
2. **Já candidatado**: Você já se candidatou a esta vaga
3. **Vaga fechada**: Prazo expirou
4. **Não é vigilante**: Apenas vigilantes podem se candidatar

### P: Como cancelar minha candidatura?

**R:**
1. Menu → Disponibilidade
2. Localizar candidatura
3. Botão "Cancelar"
4. Preencher justificativa
5. Aguardar aprovação do coordenador

**Nota**: Cancelamento sem justificativa pode afetar futuras candidaturas

### P: Minha candidatura foi rejeitada, por quê?

**R:**
- Perfil incompleto
- Não atende requisitos da vaga
- Vagas esgotadas
- Histórico de faltas
- Contacte o coordenador para detalhes

---

## 🏛️ Júris e Alocações

### P: Não consigo alocar vigilante - "Conflito de horário"

**R:**
- Vigilante já tem alocação no mesmo horário
- Verificar outras alocações do vigilante
- Ajustar horário de um dos júris
- Escolher outro vigilante

### P: "Capacidade máxima atingida"

**R:**
- Júri já tem o número máximo de vigilantes
- Padrão: 2 vigilantes por júri
- Coordenador pode aumentar capacidade se necessário
- Ou criar outro júri

### P: Drag-and-drop não funciona

**R:**
1. Atualizar navegador (F5)
2. Verificar JavaScript habilitado
3. Usar navegador moderno (Chrome, Firefox, Edge)
4. Evitar Internet Explorer
5. Desabilitar extensões que podem interferir

### P: Como remover um vigilante já alocado?

**R:**
1. Ir para Planejamento
2. Localizar o júri
3. Clicar no "X" ao lado do nome do vigilante
4. Confirmar remoção

### P: Auto-alocação não funcionou

**R:**
- Verificar se há vigilantes disponíveis
- Vigilantes devem estar aprovados
- Não devem ter conflito de horário
- Capacidade dos júris deve estar disponível

---

## 📊 Relatórios e Exportações

### P: PDF não gera/aparece em branco

**R:**
1. Aguardar alguns segundos (pode demorar)
2. Verificar se há dados para exportar
3. Desabilitar bloqueador de pop-ups
4. Tentar em outro navegador
5. Limpar cache

### P: Excel baixa mas não abre

**R:**
1. Salvar arquivo no computador
2. Abrir com Microsoft Excel ou LibreOffice
3. Arquivo pode estar corrompido se internet caiu durante download
4. Tentar gerar novamente

### P: Relatório não mostra dados recentes

**R:**
- Cache pode estar desatualizado
- Aguardar 5 minutos
- Recarregar página (Ctrl + F5)
- Gerar relatório novamente

---

## 🔧 Problemas Técnicos

### P: Erro 500 - Internal Server Error

**R:**
1. Aguardar 1-2 minutos
2. Recarregar página
3. Limpar cache: Ctrl + Shift + Delete
4. Se persistir, reportar ao suporte técnico
5. **Admin**: Verificar logs em `~/logs/php_errors.log`

### P: Erro 404 - Página não encontrada

**R:**
1. Verificar URL digitada
2. URL correta: `https://admissao.cycode.net`
3. Não usar `www.admissao.cycode.net`
4. Verificar se digitou após a URL (ex: /login)

### P: Site muito lento

**R:**
1. Verificar sua conexão de internet
2. Testar em https://fast.com
3. Pode haver muitos acessos simultâneos
4. Aguardar alguns minutos
5. **Admin**: Verificar performance do servidor

### P: CSS/Estilos não carregam - site sem formatação

**R:**
1. Ctrl + F5 (forçar recarga)
2. Limpar cache do navegador
3. Verificar se está bloqueando scripts
4. Tentar modo anônimo/incógnito
5. **Admin**: Verificar .htaccess e permissões

### P: "CSRF token inválido"

**R:**
1. Recarregar página antes de submeter formulário
2. Não deixar página aberta por muito tempo
3. Fazer login novamente
4. Limpar cookies

### P: Sessão expirou

**R:**
- Sessões duram 2 horas por segurança
- Fazer login novamente
- Manter aba ativa se estiver trabalhando
- Salvar trabalho regularmente

---

## 📧 Emails e Notificações

### P: Não recebo emails do sistema

**R:**
1. Verificar pasta spam/lixo eletrônico
2. Adicionar `noreply@admissao.cycode.net` aos contatos
3. Verificar se email no perfil está correto
4. Alguns provedores bloqueiam emails automáticos
5. **Admin**: Verificar configuração SMTP no .env

### P: Link do email expirou

**R:**
- Links de recuperação de senha expiram em 60 minutos
- Solicitar novo link
- Usar imediatamente após receber

### P: Email chegou mas link não funciona

**R:**
1. Copiar URL completa do email
2. Colar no navegador
3. Verificar se não quebrou em múltiplas linhas
4. Clicar diretamente no link do email

---

## 🔒 Segurança e Permissões

### P: "Você não tem permissão para acessar esta página"

**R:**
- Página restrita ao seu tipo de usuário
- Coordenadores têm acesso total
- Membros têm acesso parcial
- Vigilantes têm acesso limitado
- Contacte coordenador se precisar de permissão

### P: Como proteger minha conta?

**R:**
1. Use senha forte (8+ caracteres, letras, números, símbolos)
2. Não compartilhe senha
3. Não use mesma senha de outros sites
4. Faça logout em computadores públicos
5. Altere senha periodicamente

### P: Suspeito que minha conta foi acessada

**R:**
1. Alterar senha imediatamente
2. Verificar histórico de atividades (se disponível)
3. Contactar coordenador
4. Fazer logout de todos os dispositivos

---

## 🛠️ Para Administradores

### P: Como fazer backup do sistema?

**R:**
```bash
# Via SSH
bash scripts/backup_production.sh

# Ou manualmente
mysqldump -u cycodene_dbuser -p cycodene_comexames > backup.sql
tar -czf files.tar.gz admissao.cycode.net/
```

### P: Como ver logs de erro?

**R:**
```bash
# Via SSH
tail -f ~/logs/php_errors.log
tail -f ~/admissao.cycode.net/storage/logs/app.log

# Via cPanel
File Manager → /home/cycodene/logs/php_errors.log
```

### P: Como limpar cache?

**R:**
```bash
# Via SSH
rm -rf ~/admissao.cycode.net/storage/cache/*

# Via cPanel File Manager
Navegar → storage/cache/ → Selecionar tudo → Delete
```

### P: Banco de dados não conecta

**R:**
1. Verificar credenciais no `.env`
2. Testar conexão: `mysql -u cycodene_dbuser -p cycodene_comexames`
3. Verificar se usuário tem permissões
4. Verificar se banco existe
5. Contactar suporte da hospedagem

### P: Como atualizar o sistema?

**R:**
1. **Fazer backup primeiro!**
2. Fazer upload dos novos arquivos
3. Executar `composer install`
4. Executar migrations SQL (se houver)
5. Limpar cache
6. Testar funcionamento
7. Ver: `COMANDOS_PRODUCAO.md` seção "Atualização"

### P: SSL/HTTPS não está funcionando

**R:**
1. cPanel → SSL/TLS Status
2. Run AutoSSL para `admissao.cycode.net`
3. Aguardar 5-10 minutos
4. Verificar se domínio está apontando corretamente
5. Contactar suporte se não resolver

### P: Cron job não está executando

**R:**
1. Verificar configuração em cPanel → Cron Jobs
2. Comando correto:
   ```
   /usr/bin/php /home/cycodene/admissao.cycode.net/app/Cron/check_deadlines.php >> /home/cycodene/logs/cron.log 2>&1
   ```
3. Verificar log: `tail ~/logs/cron.log`
4. Testar manualmente: `/usr/bin/php ~/admissao.cycode.net/app/Cron/check_deadlines.php`
5. Verificar permissões do arquivo

---

## 📱 Compatibilidade

### P: Funciona em dispositivos móveis?

**R:**
- **Sim!** Sistema é responsivo
- Testado em smartphones e tablets
- Melhor experiência: tela ≥ 5 polegadas
- Algumas funcionalidades avançadas são melhores no desktop

### P: Quais navegadores são suportados?

**R:**
- ✅ Google Chrome (recomendado)
- ✅ Mozilla Firefox
- ✅ Microsoft Edge
- ✅ Safari (iOS/macOS)
- ⚠️ Opera (funcional)
- ❌ Internet Explorer (NÃO suportado)

### P: Preciso instalar algum software?

**R:**
- **Não!** Sistema é 100% web
- Apenas navegador moderno
- Para exportações PDF/Excel: leitor nativo do SO

---

## 💡 Dicas de Uso

### P: Como otimizar meu uso do sistema?

**R:**
1. Complete perfil logo após cadastro
2. Candidate-se cedo às vagas
3. Verifique dashboard regularmente
4. Use drag-and-drop para alocações rápidas
5. Aproveite auto-alocação para economizar tempo
6. Exporte relatórios antes dos exames
7. Mantenha dados atualizados

### P: Posso usar o sistema offline?

**R:**
- **Não.** Sistema requer conexão à internet
- Dados são salvos em tempo real
- Recomenda-se conexão estável

### P: Como sugerir melhorias?

**R:**
1. Contactar coordenador
2. Enviar email para suporte
3. Descrever melhoria detalhadamente
4. Incluir casos de uso

---

## 🆘 Suporte

### Quando Contactar Suporte?

- Erro persistente após tentar soluções do FAQ
- Problema afeta funcionamento do sistema
- Dúvida não coberta neste guia
- Suspeita de falha de segurança

### Como Reportar Problema?

Incluir na mensagem:
1. **O quê**: Descrição do problema
2. **Quando**: Data e hora
3. **Onde**: Página/funcionalidade
4. **Como**: Passos para reproduzir
5. **Resultado**: O que esperava vs. o que aconteceu
6. **Navegador**: Chrome, Firefox, etc.
7. **Screenshot**: Se possível

**Exemplo de reporte:**
```
O quê: Erro ao fazer upload de avatar
Quando: 17/10/2025 às 14:30
Onde: Página de Perfil
Como: 1) Login, 2) Ir para Perfil, 3) Escolher imagem, 4) Upload
Resultado: Esperava upload, mas apareceu erro "Tipo de arquivo não permitido"
Navegador: Chrome 118
Screenshot: [anexo]
```

### Contactos de Suporte

**Email**: coordenador@admissao.cycode.net  
**Sistema**: https://admissao.cycode.net  
**Horário**: Segunda a Sexta, 8h-17h

---

## 📚 Recursos Adicionais

- **Guia Completo**: `GUIA_PRIMEIRO_ACESSO.md`
- **Deploy**: `DEPLOY_RAPIDO.md`
- **Comandos Admin**: `COMANDOS_PRODUCAO.md`
- **README**: `README.md`

---

**Última atualização**: 17 de Outubro de 2025  
**Versão**: 2.5+

**Não encontrou sua dúvida?** Contacte o suporte!
