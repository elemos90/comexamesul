---
description: Como desenvolver localmente e fazer deploy de melhorias
---

# Fluxo de Trabalho: Desenvolvimento e Deploy

Siga estes passos para garantir que as novas funcionalidades cheguem ao servidor com segurança.

## 1. Desenvolvimento Local
- Trabalhe sempre na pasta local do XAMPP.
- Certifique-se de que o seu `.env` local tem `APP_ENV=local` e `APP_DEBUG=true`.
- Teste todas as rotas em `http://localhost/comexamesul`.

## 2. Gerar o Pacote de Produção
Quando terminar uma melhoria e quiser enviá-la para o servidor:
1. Abra o PowerShell ou o Terminal do VS Code.
2. Execute o comando:
   // turbo
   `powershell -ExecutionPolicy Bypass -File create_deploy.ps1`
3. O script criará (ou atualizará) o ficheiro `deploy_jogos_v2.zip` na raiz do projeto.

## 3. Deploy no Servidor (cPanel)
1. Aceda ao Gestor de Ficheiros do cPanel no domínio `comissaoexames.cycode.net`.
2. Faça upload do `deploy_jogos_v2.zip`.
3. Extraia o ficheiro ZIP. 
   > [!IMPORTANT]
   > A extração deve ser feita na raiz do diretório do domínio. O ZIP já contém a estrutura correta (app, public, vendor, etc.).
4. Se houver alterações na base de dados, execute os SQLs necessários no PHPMyAdmin do servidor.

## 4. Limpeza e Segurança
- Se o pacote incluir ficheiros de diagnóstico (como `diagnose.php`), apague-os do servidor assim que confirmar que o sistema está online.
- Limpe o cache do seu navegador caso as alterações de CSS ou JS não apareçam imediatamente.
