# Guia de Deploy (FTP/FileZilla)

Este guia descreve os passos para colocar a aplicação em produção.

## 🚀 Método Recomendado (Arquivo ZIP)

## 1. Prepare o Servidor

1.  Acesse o **cPanel** -> **File Manager**.
2.  Vá para a pasta **`comissaoexames.cycode.net`** (NÃO é public_html).
3.  **MUITO IMPORTANTE:** Apague TODOS os arquivos existentes nessa pasta (delete o `index.html` e a pasta `cgi-bin` se houver).
    *   Queremos limpar o site de teste para instalar o sistema real.

## 2. Upload dos Arquivos

1.  Faça upload do arquivo `release_v1.0.zip` para dentro da pasta `comissaoexames.cycode.net`.
2.  Clique com o botão direito no arquivo zip e escolha **Extract** (Extrair).
3.  Extraia para a própria pasta.

### Estrutura Esperada após Extração:
Você deverá ver arquivos soltos na raiz da pasta `comissaoexames.cycode.net`:
- `index.php`
- `.htaccess`
- `bootstrap.php`
- `app/` (pasta)
- ... e outros.

NÃO deve haver uma pasta chamada `public`.

## 3. Configuração do Ambiente (.env)

---

## 📂 Método Manual (Arrastar e Soltar)

Se preferir enviar os arquivos soltos, siga estas instruções:

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
   - No phpMyAdmin, **clique no nome do seu banco de dados** na barra lateral esquerda.
   - Clique na aba **Importar**.
   - Escolha o arquivo `database_production.sql`.

## 4. Teste

Acesse o URL do seu site. Se vir uma página em branco ou erro 500:
- Verifique os logs na pasta `storage/logs/app.log`.
- Verifique se o arquivo `.env` está com as credenciais de banco corretas.
