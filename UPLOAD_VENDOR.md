# 📦 Como Fazer Upload da Pasta vendor/

Como o Composer não está disponível ou configurado no servidor, você precisa fazer upload manual das dependências.

## ✅ Passo a Passo

### 1️⃣ **Comprimir a pasta vendor/** (Já instalada localmente)

**Via PowerShell:**
```powershell
cd c:\xampp\htdocs\comexamesul
Compress-Archive -Path vendor -DestinationPath vendor.zip -Force
```

**Ou manualmente:**
- Clique com botão direito na pasta `vendor/`
- Enviar para → Pasta compactada
- Renomear para `vendor.zip`

### 2️⃣ **Upload via cPanel File Manager**

1. Acessar: https://cycode.net:2083
2. Login: `cycodene`
3. **File Manager**
4. Navegar para: `/home/cycodene/admissao.cycode.net/`
5. **Upload** → Selecionar `vendor.zip`
6. Aguardar upload completar (pode demorar 5-10 min)
7. Clicar em `vendor.zip` → **Extract**
8. Destination: `/home/cycodene/admissao.cycode.net/`
9. **Extract Files**
10. Aguardar extração
11. **Excluir** o arquivo `vendor.zip` (opcional)

### 3️⃣ **Verificar Estrutura**

A estrutura final deve ser:
```
/home/cycodene/admissao.cycode.net/
├── vendor/           ← Pasta extraída
│   ├── autoload.php
│   ├── composer/
│   ├── dompdf/
│   ├── phpmailer/
│   └── ...
├── app/
├── public/
├── .env
└── ...
```

### 4️⃣ **Ajustar Permissões (Via SSH ou File Manager)**

**Via Terminal SSH:**
```bash
cd ~/admissao.cycode.net
chmod -R 755 vendor/
```

**Via File Manager:**
- Selecionar pasta `vendor/`
- Change Permissions → 755
- Marcar "Recurse into subdirectories"
- Apply

### 5️⃣ **Testar**

```bash
# Acessar no navegador:
https://admissao.cycode.net/check.php

# Deve mostrar:
✓ vendor/autoload.php
```

## 🚀 Após Upload

### Limpar arquivos de teste:
```bash
# Via SSH:
cd ~/admissao.cycode.net
rm public/check.php

# Ou via File Manager:
# Excluir: public/check.php
```

### Testar site:
```
https://admissao.cycode.net
```

Deve carregar a página de login! 🎉

## ⚠️ Alternativa: Upload via FTP

Se o File Manager do cPanel for lento:

1. **Usar cliente FTP:**
   - FileZilla, WinSCP, ou Cyberduck
   
2. **Conectar:**
   ```
   Host: 57.128.126.160
   User: cycodene
   Protocol: FTP ou SFTP
   Port: 21 (FTP) ou 22 (SFTP)
   ```

3. **Upload direto da pasta vendor/**
   - Arrastar pasta `vendor/` para `/home/cycodene/admissao.cycode.net/`
   - Aguardar (pode demorar 10-20 min com muitos arquivos)

## 📊 Tamanho Estimado

- **vendor/**: ~30-50 MB
- **Arquivos**: ~3000-5000 arquivos
- **Tempo upload**: 5-15 minutos (dependendo da conexão)

---

**Boa sorte! 🚀**
