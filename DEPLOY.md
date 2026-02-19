# Guia de Deploy (FTP/FileZilla)

Este guia descreve os passos para colocar a aplicação em produção.

## 1. Preparação dos Arquivos

Antes de enviar, certifique-se de que os arquivos locais estão prontos.
- [x] Assets (CSS/JS) compilados (`npm run build:css`)
- [x] Arquivo de configuração de produção criado (`.env.production`)

## 2. O Que Enviar via FTP

Arraste as seguintes pastas e arquivos para a pasta `public_html` (ou raiz) do seu servidor:

### Pastas Obrigatórias:
- `app/` (Código fonte da aplicação)
- `config/` (Configurações)
- `public/` (Arquivos públicos - index.php, assets, etc.)
- `resources/` (Views e assets brutos)
- `routes/` (Rotas, se houver pasta separada, ou dentro de app)
- `storage/` (Logs e cache - **Importante: Ver permissões abaixo**)
- `vendor/` (Bibliotecas PHP - **Muito Importante**)
- `src/` (Se houver código fonte extra)

### Arquivos Obrigatórios:
- `bootstrap.php`
- `.env.production` (**RENOMEAR PARA `.env` NO SERVIDOR**)
- `composer.json` e `composer.lock` (Opcional, mas bom ter)

### O Que NÃO Enviar (Ignorar):
- `.git/` (Pasta oculta do Git)
- `.env` (Este é o seu local de desenvolvimento)
- `node_modules/` (Não é necessário em produção, pois ja compilamos o CSS)
- `tests/`
- `writable/` (Se existir, geralmente é o `storage`)
- `*.sql` (Arquivos de backup de banco de dados, exceto se for importar)

## 3. Configuração no Servidor

1. **Renomear .env**:
   - Localize o arquivo `.env.production` que você enviou.
   - Renomeie-o para `.env`.

2. **Permissões de Pasta (CHMOD)**:
   - Clique com o botão direito na pasta `storage`.
   - Selecione "Permissões do Ficheiro..." (File permissions).
   - Defina o valor numérico para **755** ou **775** (precisa ser gravável pelo servidor web).
   - Marque "Recurso em subdiretórios" (Recurse into subdirectories).

3. **Banco de Dados**:
   - Utilize o PHPMyAdmin do seu servidor (cPanel) para importar o banco de dados, se necessário. Use o arquivo `install_production.sql` (se criado) ou o dump mais recente.

## 4. Teste

Acesse o URL do seu site. Se vir uma página em branco ou erro 500:
- Verifique os logs na pasta `storage/logs/app.log`.
- Verifique se o arquivo `.env` está com as credenciais de banco corretas.
