# 🧪 Guia de Teste - Criação de Vagas

## ✅ Melhorias Implementadas

### **1. Exibição de Erros Específicos**
- Agora cada campo mostra o erro exato embaixo dele
- Campos com erro ficam com borda vermelha
- Mensagem de erro detalhada aparece no topo

### **2. Valores Preservados**
- Se houver erro, os valores preenchidos são mantidos
- Não precisa preencher tudo novamente

### **3. Modal Reaberto Automaticamente**
- Se houver erros, o modal reabre sozinho
- Foco automático no primeiro campo

## 🔍 Como Testar

### **Teste 1: Campos Vazios**
1. Acesse `/vacancies` como coordenador
2. Clique em "Nova vaga"
3. Deixe todos os campos vazios
4. Clique em "Publicar"
5. ✅ **Esperado**: Erros aparecem em cada campo:
   - Título: "Este campo é obrigatório."
   - Descrição: "Este campo é obrigatório."
   - Limite: "Este campo é obrigatório."

### **Teste 2: Título Muito Curto**
1. Preencha título com "Ab" (2 caracteres)
2. Preencha descrição com texto válido
3. Escolha data/hora
4. Clique em "Publicar"
5. ✅ **Esperado**: 
   - Erro: "Mínimo de 3 caracteres não atingido."
   - Valores de descrição e data mantidos

### **Teste 3: Descrição Muito Curta**
1. Preencha título válido
2. Preencha descrição com "Teste" (5 caracteres)
3. Escolha data/hora
4. Clique em "Publicar"
5. ✅ **Esperado**: 
   - Erro: "Mínimo de 10 caracteres não atingido."

### **Teste 4: Data Inválida**
1. Preencha todos os campos
2. Modifique manualmente o HTML do input de data para valor inválido
3. ✅ **Esperado**: "Data inválida."

### **Teste 5: Criação Bem-Sucedida**
1. Preencha:
   - **Título**: "Vagas para Vigilância - Exames Janeiro 2026" (mínimo 3 chars)
   - **Descrição**: "Processo de candidatura para vigilantes nos exames de admissão." (mínimo 10 chars)
   - **Limite**: Data futura (ex: 15/01/2026 23:59)
2. Clique em "Publicar"
3. ✅ **Esperado**: 
   - Mensagem verde: "Vaga criada."
   - Modal fecha
   - Nova vaga aparece na listagem

## 🐛 Possíveis Problemas e Soluções

### **Problema 1: "Verifique os dados da vaga" sem detalhes**
**Causa**: Navegador não suportando `datetime-local`
**Solução**: 
- Testar em navegador moderno (Chrome, Firefox, Edge)
- Verificar se o campo está realmente preenchido

### **Problema 2: Modal não reabre após erro**
**Causa**: JavaScript não carregou
**Solução**:
- Abrir console do navegador (F12)
- Verificar erros JavaScript
- Atualizar a página (Ctrl+R)

### **Problema 3: Valores não são preservados**
**Causa**: Sessão PHP não está funcionando
**Solução**:
- Verificar permissões da pasta `storage/cache`
- Verificar configuração de sessão no `.env`

## 📋 Validações Implementadas

| Campo | Regra | Mensagem de Erro |
|-------|-------|------------------|
| **Título** | Obrigatório | "Este campo é obrigatório." |
| | Mínimo 3 caracteres | "Mínimo de 3 caracteres não atingido." |
| | Máximo 180 caracteres | "Máximo de 180 caracteres excedido." |
| **Descrição** | Obrigatório | "Este campo é obrigatório." |
| | Mínimo 10 caracteres | "Mínimo de 10 caracteres não atingido." |
| **Data Limite** | Obrigatório | "Este campo é obrigatório." |
| | Formato válido | "Data inválida." |

## 🔧 Debug Adicional

Se ainda houver problemas, adicione debug temporário:

```php
// No início do método store() em VacancyController.php
error_log('=== DEBUG VAGA ===');
error_log('Dados recebidos: ' . print_r($data, true));
error_log('Erros validação: ' . print_r($validator->errors(), true));
```

Verificar logs em `storage/logs/php_error.log`

## ✨ Melhorias Futuras Sugeridas

- [ ] Validar que data limite seja futura
- [ ] Adicionar preview antes de publicar
- [ ] Permitir rascunhos (status 'rascunho')
- [ ] Notificar vigilantes por email quando vaga abrir
- [ ] Adicionar contador de candidaturas
