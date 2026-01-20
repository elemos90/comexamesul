# 🌐 Guia de Teste em Rede Local

Para testar a aplicação a partir de outros dispositivos (telemóveis ou outros computadores) na mesma rede Wi-Fi, siga estes passos:

## 1. Endereço de Acesso
O seu computador está identificado na rede com o seguinte IP:

👉 **URL:** `http://192.168.1.179/comexamesul`

---

## 2. Passos para o Telemóvel
1. Certifique-se de que o telemóvel está ligado ao **mesmo Wi-Fi** que o computador.
2. Abra o navegador (Chrome ou Safari) no telemóvel.
3. Digite o endereço acima.

---

## 3. Resolver Problemas de Ligação
Se a página não carregar no telemóvel, siga estas verificações:

### A. Firewall do Windows (Mais Comum)
O Windows pode estar a bloquear o acesso externo ao servidor Apache (XAMPP).
1. Abra o **Painel de Controlo** -> **Sistema e Segurança** -> **Windows Defender Firewall**.
2. Clique em "Permitir uma aplicação ou funcionalidade através do Windows Defender Firewall".
3. Procure por **"Apache HTTP Server"**.
4. Certifique-se de que as caixas **"Privada"** e **"Pública"** estão marcadas.
5. Clique em OK e tente novamente.

### B. XAMPP
Certifique-se de que o **Apache** e o **MySQL** estão a correr (verde) no XAMPP Control Panel.

### C. Rede Pública vs Privada
Certifique-se de que a sua ligação Wi-Fi no Windows está definida como **"Rede Privada"**. Se estiver como "Pública", a Firewall é muito mais restritiva.

---

## 🚀 Dica de Desenvolvedor
Pode usar o Chrome DevTools (F12) no seu computador e escolher o ícone de telemóvel para simular diferentes tamanhos de ecrã enquanto desenvolve a responsividade.
