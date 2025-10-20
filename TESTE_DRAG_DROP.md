# 🧪 Teste do Sistema Drag-and-Drop

## ✅ Pré-requisitos

1. **Servidor rodando**: http://localhost:8000
2. **Login como coordenador**: `coordenador@unilicungo.ac.mz` / `password`
3. **Navegador**: Chrome, Firefox ou Edge (atualizado)

## 📋 Passo a Passo para Testar

### **1. Verificar se há júris criados**
- Acesse: http://localhost:8000/juries
- Se não houver júris, crie alguns primeiro

### **2. Criar Júris de Teste (se necessário)**

**Opção A: Criar Disciplina com Múltiplas Salas**
1. Clique em "Nova Disciplina"
2. Preencha:
   - Disciplina: `Teste Matemática`
   - Data: Qualquer data futura
   - Horário: `08:00` - `11:00`
   - Local: `Campus Central`
3. Adicione 2-3 salas
4. Clique em "Criar Júris"

**Opção B: Criar Júri Individual**
1. Clique em "Júri Individual"
2. Preencha todos os campos
3. Clique em "Guardar"

### **3. Testar Drag-and-Drop de Vigilantes**

**Verificar se o painel lateral existe:**
- ✅ Deve haver um painel à esquerda com "Vigilantes disponíveis"
- ✅ Deve listar vigilantes com badges de contagem

**Testar arrastar:**
1. **Passe o mouse** sobre um vigilante no painel lateral
   - O cursor deve mudar para "move" (mãozinha)
   - O item deve ter borda e sombra

2. **Clique e segure** no vigilante
   - O item deve ficar semi-transparente

3. **Arraste** para a área "Vigilantes" de uma sala
   - A área deve ter borda tracejada cinza
   - Deve dizer "Arraste vigilantes para aqui" se vazia

4. **Solte** o vigilante
   - Deve aparecer um toast de sucesso
   - O vigilante deve aparecer na lista da sala
   - Deve ficar verde por 1.5 segundos

### **4. Testar Drag-and-Drop de Supervisores**

**Verificar pool de supervisores:**
- ✅ Dentro de cada sala, deve haver uma seção "Supervisores elegíveis"
- ✅ Área amarela para soltar supervisor

**Testar arrastar:**
1. **Arraste** um supervisor do pool
2. **Solte** na área amarela "Supervisor"
3. Deve substituir qualquer supervisor anterior
4. Deve ficar com fundo amarelo

### **5. Testar Remoção**

**Remover vigilante:**
1. Arraste um vigilante de volta para o painel lateral
2. Deve ser removido da sala

**Trocar supervisor:**
1. Arraste outro supervisor para a mesma sala
2. Deve substituir o anterior

## 🐛 Problemas Comuns

### **Problema 1: Não consigo arrastar nada**
**Possíveis causas:**
- SortableJS não carregou
- Abra o Console do navegador (F12) e procure por erros
- Verifique se há erro: `Sortable is not defined`

**Solução:**
- Recarregue a página (Ctrl + F5)
- Limpe o cache do navegador

### **Problema 2: Arrasto mas não solta**
**Possíveis causas:**
- Área de drop não está configurada
- Falta atributos `data-jury`, `data-assign-url`

**Solução:**
- Verifique se os júris foram criados corretamente
- Recarregue a página

### **Problema 3: Erro ao soltar**
**Possíveis causas:**
- Conflito de horário
- Vigilante já alocado em outro júri no mesmo horário

**Solução:**
- Leia a mensagem de erro no toast
- Escolha outro vigilante ou remova do júri conflitante

### **Problema 4: Painel lateral vazio**
**Possíveis causas:**
- Não há vigilantes com disponibilidade ativa

**Solução:**
1. Acesse: http://localhost:8000/availability
2. Faça login como vigilante: `vigilante1@unilicungo.ac.mz` / `password`
3. Marque "Disponível para vigilância"
4. Volte como coordenador

## 🔍 Debug no Console

Abra o Console (F12) e digite:

```javascript
// Verificar se Sortable está carregado
console.log(typeof Sortable);  // Deve retornar "function"

// Verificar vigilantes disponíveis
console.log(document.querySelectorAll('.draggable-item').length);

// Verificar zonas de drop
console.log(document.querySelectorAll('.dropzone').length);

// Verificar pool de vigilantes
console.log(document.getElementById('available-vigilantes'));
```

## ✅ Checklist Final

- [ ] Servidor rodando em localhost:8000
- [ ] Login como coordenador funcionando
- [ ] Júris criados e visíveis
- [ ] Painel lateral com vigilantes aparece
- [ ] Cursor muda ao passar sobre vigilantes
- [ ] Consigo arrastar vigilantes
- [ ] Toast de sucesso aparece ao soltar
- [ ] Vigilante aparece na sala
- [ ] Consigo arrastar supervisores
- [ ] Supervisor aparece na área amarela

## 📞 Se Nada Funcionar

Execute no terminal:
```bash
# Limpar cache do navegador e recarregar
# Ou use modo anônimo/privado do navegador
```

Verifique se o arquivo `app.js` está sendo carregado:
- Abra: http://localhost:8000/assets/js/app.js
- Deve mostrar o código JavaScript

---

**Última atualização**: 2025-10-08
