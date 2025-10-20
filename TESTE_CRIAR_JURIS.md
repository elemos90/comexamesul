# 🧪 GUIA DE TESTES - CRIAÇÃO DE JÚRIS

## 🔍 DIAGNÓSTICO DO PROBLEMA

### **Problema 1: "Erro ao carregar dados"**
Isso indica que os endpoints `/api/master-data/locations-rooms` ou `/api/vacancies/{id}/subjects` estão retornando HTML em vez de JSON.

### **Problema 2: "Erro ao Remover"**
O endpoint `/juries/{id}/delete` também está retornando HTML.

---

## 📋 CHECKLIST DE VERIFICAÇÃO

### **1. Abra o Console do Navegador (F12)**
```
Chrome/Edge: F12 → aba "Console"
Firefox: F12 → aba "Console"
```

### **2. Vá para a página "Gestão de Alocação"**
URL: `http://localhost/juries/vacancy/{ID_DA_VAGA}/manage`

### **3. Abra o Console e procure por:**
```
❌ Erros em vermelho
⚠️ Avisos em amarelo
🔵 Mensagens "ERRO:" ou "Resposta não é JSON:"
```

### **4. Cole a primeira linha de erro aqui e me envie**

---

## 🧪 TESTE MANUAL DOS ENDPOINTS

### **Teste 1: Verificar Endpoint de Locais**

**No navegador, abra uma nova aba e cole:**
```
http://localhost/api/master-data/locations-rooms
```

**Resultado esperado:**
```json
{
  "success": true,
  "locations": [...],
  "rooms": [...]
}
```

**Se ver HTML em vez de JSON:**
- ❌ O endpoint não está funcionando
- Copie o HTML que apareceu e me envie

---

### **Teste 2: Verificar Endpoint de Disciplinas**

**No navegador:**
```
http://localhost/api/vacancies/1/subjects
```
*(Troque "1" pelo ID real da vaga)*

**Resultado esperado:**
```json
{
  "success": true,
  "subjects": ["INGLÊS", "MATEMÁTICA", ...]
}
```

---

### **Teste 3: Verificar se há Locais Cadastrados**

**No navegador:**
```
http://localhost/master-data/locations
```

**Perguntas:**
1. Existem locais cadastrados?
2. Existem salas cadastradas?

**Se NÃO:**
- Você precisa cadastrar locais e salas primeiro
- Ir para: Menu → Dados Mestre → Locais
- Criar pelo menos 1 local
- Adicionar pelo menos 2-3 salas a esse local

---

## 🔧 VERIFICAÇÕES TÉCNICAS

### **1. Verificar Logs do PHP**

**No terminal/PowerShell:**
```powershell
Get-Content C:\xampp\php\logs\php_error_log -Tail 20
```

ou

```powershell
Get-Content C:\xampp\apache\logs\error.log -Tail 20
```

**Procure por:**
- `ERRO: Headers já enviados`
- `Warning:`
- `Fatal error:`

---

### **2. Verificar Network Tab**

**No navegador (F12 → Network):**
1. Clique em "Criar Novo Júri"
2. Veja a requisição `locations-rooms`
3. Clique nela
4. Veja a aba "Response"

**Me envie:**
- Status Code (200, 404, 500?)
- Response (primeiras 10 linhas)
- Request URL

---

## 🎯 TESTES ESPECÍFICOS

### **Teste A: Criar Júri com Erro Detalhado**

1. Abra o Console (F12)
2. Clique "Criar Novo Júri"
3. No console, copie TODA a mensagem de erro que aparece
4. Me envie

### **Teste B: Eliminar Júri com Erro Detalhado**

1. Abra o Console (F12)
2. Clique "Eliminar" em um júri
3. Confirme a eliminação
4. No console, copie TODA a mensagem de erro
5. Me envie

---

## 📊 POSSÍVEIS CAUSAS

| Causa | Sintoma | Solução |
|-------|---------|---------|
| **Sem dados mestre** | "Nenhum local cadastrado" | Cadastrar locais e salas |
| **Headers já enviados** | HTML antes do JSON | Já corrigido no Response.php |
| **Rota não encontrada** | 404 Not Found | Verificar web.php |
| **Sem permissão** | 403 Forbidden | Verificar role do usuário |
| **Erro no controller** | 500 Server Error | Ver logs do PHP |

---

## 🚀 SOLUÇÕES RÁPIDAS

### **Solução 1: Se não há locais cadastrados**

**SQL Direto (phpMyAdmin):**
```sql
-- Inserir local de teste
INSERT INTO exam_locations (code, name, address, city, capacity, active, created_at)
VALUES ('TESTE', 'Local de Teste', 'Endereço Teste', 'Beira', 100, 1, NOW());

-- Inserir salas de teste
SET @location_id = LAST_INSERT_ID();

INSERT INTO exam_rooms (location_id, code, name, capacity, active, created_at)
VALUES 
(@location_id, 'A101', 'Sala A101', 40, 1, NOW()),
(@location_id, 'A102', 'Sala A102', 35, 1, NOW()),
(@location_id, 'B201', 'Sala B201', 30, 1, NOW());
```

### **Solução 2: Limpar Cache do Navegador**

```
Chrome: Ctrl+Shift+Delete
Edge: Ctrl+Shift+Delete
Firefox: Ctrl+Shift+Delete

Marcar: "Cached images and files"
Período: "Last hour"
Clicar "Clear data"
```

### **Solução 3: Reiniciar Apache**

```powershell
# No XAMPP Control Panel:
# 1. Clicar "Stop" no Apache
# 2. Aguardar 3 segundos
# 3. Clicar "Start" no Apache
```

---

## 📞 O QUE ME ENVIAR

Para eu poder ajudar melhor, me envie:

1. **Screenshot do Console (F12)** quando clicar "Criar Novo Júri"
2. **Screenshot da aba Network** mostrando a requisição `locations-rooms`
3. **Primeiras 10 linhas** do que aparece ao acessar:
   - `http://localhost/api/master-data/locations-rooms`
4. **Últimas 20 linhas** do log de erros do PHP

---

## ✅ TESTE FINAL

Se tudo funcionar, você deve ver:

**Ao clicar "Criar Novo Júri":**
```
✅ Modal abre
✅ Dropdown de "Local" tem opções
✅ Campo "Disciplina" tem autocomplete (se já houver júris)
✅ Nenhum erro no console
```

**Ao criar júris:**
```
✅ Salas são adicionadas na tabela
✅ Resumo mostra "X salas | Y candidatos"
✅ Ao clicar "Criar Júris" → Toast verde de sucesso
✅ Página recarrega com novos júris
```

**Ao eliminar júri:**
```
✅ Confirmação aparece 2x
✅ Digita "ELIMINAR"
✅ Toast verde "Júri eliminado com sucesso!"
✅ Júri desaparece da lista
```

---

🎯 **AGUARDO SEU RETORNO COM AS INFORMAÇÕES!**
