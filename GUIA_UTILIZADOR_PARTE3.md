# 📕 Guia do Utilizador - Parte 3: Coordenador + FAQ

**Versão**: 2.1 | **Data**: 15/10/2025

---

## 🔴 COORDENADOR

**Permissões**: TODAS do Membro + Dados Mestres

---

## 🗄️ Dados Mestres

**Acesso**: Menu → **Dados Mestres**

### 1. Disciplinas

#### Gerir Disciplinas
1. Menu → Dados Mestres → **Disciplinas**
2. **Criar**: "+ Nova Disciplina"
   - Código (ex: MAT101)
   - Nome (ex: Matemática I)
   - Descrição
3. **Editar**: ✏️ → Alterar informações
4. **Ativar/Desativar**: Switch on/off
5. **Eliminar**: 🗑️ (se sem júris)

**Uso**:
- Disciplinas ativas aparecem em formulários
- Disciplinas inativas ficam ocultas

---

### 2. Cadastro de Locais

#### Gerir Locais de Exame
1. Menu → Dados Mestres → **Cadastro de Locais**
2. **Criar**: "+ Novo Local"
   - Código (ex: BRA_PAV_A)
   - Nome (ex: Beira - Pavilhão A)
   - Cidade
   - Endereço
   - Capacidade total
   - Descrição
3. **Editar**: ✏️
4. **Ativar/Desativar**
5. **Eliminar**: Se sem júris/salas

**Hierarquia**:
```
📍 Local (Beira - Pavilhão A)
  └── 🏫 Salas (101, 102, 103...)
      └── 🏛️ Júris
```

---

### 3. Salas

#### Gerir Salas por Local
1. Menu → Dados Mestres → **Salas**
2. **Filtrar por local**
3. **Criar**: "+ Nova Sala"
   - Local associado
   - Código (ex: 101)
   - Nome (ex: Sala 101)
   - Capacidade (nº assentos)
   - Andar
   - Edifício
   - Notas
4. **Editar**: ✏️
5. **Ativar/Desativar**
6. **Eliminar**: Se sem júris

---

## 🎯 Fluxo Coordenador

### Início do Ano Académico

1. **Atualizar Dados Mestres**
   - Adicionar novas disciplinas
   - Atualizar locais e salas
   - Desativar disciplinas não usadas

2. **Criar Templates de Locais**
   - Para cada local recorrente
   - Configurar horários padrão
   - Salvar para reutilização

3. **Configurar Permissões**
   - Adicionar novos membros da comissão
   - Atualizar lista de supervisores elegíveis

### Durante Sessão de Exames

4. **Supervisionar Operações**
   - Monitorar criação de vagas (membros)
   - Aprovar candidaturas críticas
   - Resolver conflitos

5. **Ajustes de Última Hora**
   - Criar locais temporários se necessário
   - Ajustar capacidades de salas
   - Remanejamentos de emergência

### Pós-Exames

6. **Auditoria e Relatórios**
   - Exportar dados completos
   - Analisar estatísticas
   - Arquivar vagas antigas

7. **Preparação Próxima Sessão**
   - Atualizar templates baseado em lições aprendidas
   - Ajustar configurações

---

## ❓ FAQ - Perguntas Frequentes

### 🔵 Vigilante

**Q: Não consigo candidatar-me. Porquê?**  
A: Verifique:
- ✅ Perfil completo (telefone, NUIT, NIB, banco)
- ✅ Vaga ainda aberta
- ✅ Não se candidatou antes

**Q: Posso cancelar após aprovação?**  
A: Não. Use "Solicitar Mudança de Disponibilidade" com justificativa.

**Q: Fui rejeitado. Posso candidatar-me novamente?**  
A: Sim, na próxima vaga, após corrigir o problema indicado.

**Q: Como sei em que júris estou alocado?**  
A: Menu → Júris → Lista de Júris (vê apenas seus júris)

**Q: Posso recusar um júri após ser alocado?**  
A: Não diretamente. Contate a comissão com justificativa urgente.

---

### 🟢 Membro

**Q: Júri com conflito de horário. Como resolver?**  
A: Sistema impede automaticamente. Se vê conflito, remova um vigilante e realoque.

**Q: Auto-alocação não preencheu tudo. Porquê?**  
A: Possíveis razões:
- Vigilantes insuficientes
- Muitos conflitos de horário
- Capacidades excedidas
Complete manualmente os restantes.

**Q: Posso editar júri após criar?**  
A: Sim, use ✏️ Editar. Sistema revalida conflitos.

**Q: Como desfaço uma alocação?**  
A: No júri → Lista de vigilantes → 🗑️ Remover

**Q: Vigilante pede para sair. O que faço?**  
A: Revise "Solicitação de Mudança de Disponibilidade". Se aprovar, realoque manualmente.

**Q: Posso eliminar vaga com candidaturas?**  
A: Sistema impede se há júris ou candidaturas aprovadas. Prefira Fechar ou Encerrar.

---

### 🔴 Coordenador

**Q: Como adiciono nova disciplina?**  
A: Dados Mestres → Disciplinas → "+ Nova"

**Q: Sala aparece em uso mas júri foi eliminado?**  
A: Cache do sistema. Recarregue página.

**Q: Como transfiro coordenação para outro utilizador?**  
A: Altere role do utilizador diretamente no banco de dados (requer acesso admin).

**Q: Posso eliminar local com salas?**  
A: Não. Elimine salas primeiro, depois o local.

---

### 🛠️ Técnicas

**Q: Sistema está lento. O que fazer?**  
A: 
1. Execute: `php scripts/add_critical_indexes.php` (adiciona índices)
2. Limpe cache: Elimine arquivos em `storage/cache/`
3. Contate suporte se persistir

**Q: Erro ao importar Excel. Porquê?**  
A: Verifique:
- Formato XLSX/XLS/CSV
- Colunas no formato correto
- Locais e disciplinas existem
- Datas no formato YYYY-MM-DD
- Horários no formato HH:MM

**Q: Email de notificação não enviado?**  
A: Verifique configurações SMTP no `.env`:
```
MAIL_HOST=smtp.exemplo.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
```

**Q: Vigilante não aparece na lista para alocar?**  
A: Verifique:
- Candidatura aprovada?
- Perfil completo?
- Disponibilidade ativa?
- Não tem conflito de horário?

**Q: Como faço backup do sistema?**  
A: 
1. Banco de dados: `mysqldump -u usuario -p base > backup.sql`
2. Arquivos: Copie pasta inteira do projeto
3. Agende backups automáticos (recomendado: diários)

---

## 📊 Glossário

**Vaga**: Convocatória para vigilantes se candidatarem
**Júri**: Comissão de vigilância de um exame específico
**Vigilante**: Pessoa que vigia exame
**Supervisor**: Responsável por um júri
**Alocação**: Atribuição de vigilante a júri
**Template**: Configuração salva para reutilização
**Drag-and-Drop**: Arrastar e soltar
**Auto-alocação**: Sistema aloca automaticamente
**Conflito**: Vigilante em 2 júris no mesmo horário
**Carga**: Quantidade de júris/responsabilidades

---

## 🔧 Resolução de Problemas

### Problema: Não consigo fazer login

**Soluções**:
1. Verifique email e senha
2. Use "Esqueci senha"
3. Limpe cache do navegador
4. Tente navegador diferente
5. Contate admin do sistema

### Problema: Página não carrega/erro 500

**Soluções**:
1. Recarregue página (F5)
2. Limpe cache: Ctrl+Shift+Delete
3. Verifique logs: `storage/logs/error.log`
4. Contate suporte com screenshot do erro

### Problema: Dados não aparecem após salvar

**Soluções**:
1. Recarregue página
2. Verifique se salvou mesmo (mensagem de sucesso?)
3. Verifique filtros ativos
4. Limpe cache do sistema

### Problema: Exportação falha

**Soluções**:
1. Verifique tamanho dos dados (se muito grande, filtre)
2. Tente formato diferente (Excel vs PDF)
3. Desative bloqueador de pop-ups
4. Aguarde alguns segundos antes de tentar novamente

---

## 📞 Suporte e Contactos

### Suporte Técnico
- **Email**: suporte.exames@unilicungo.ac.mz
- **Telefone**: +258 XX XXX XXXX
- **Horário**: Seg-Sex, 08:00-17:00

### Comissão de Exames
- **Email**: comissao.exames@unilicungo.ac.mz
- **Responsável**: Dr. [Nome]
- **Extensão**: XXXX

### Suporte de Emergência (Durante Exames)
- **Telefone**: +258 XX XXX XXXX (24/7)
- **WhatsApp**: +258 XX XXX XXXX

---

## 📚 Documentação Adicional

Consulte também:

- **README.md**: Informações técnicas e instalação
- **SISTEMA_ALOCACAO_DND.md**: Manual do drag-and-drop
- **NOVAS_FUNCIONALIDADES.md**: Features da v2.0
- **GUIA_CRIACAO_JURIS_POR_LOCAL.md**: Júris por local
- **CORRECOES_SELECT_IMPLEMENTADAS.md**: Melhorias de segurança

---

## 🔄 Atualizações e Manutenção

### Verificar Versão do Sistema
- Rodapé da página: "v2.1"
- Ou arquivo: `README.md`

### Atualizações Disponíveis
- Sistema notifica automaticamente
- Coordenador recebe email
- Não atualize durante exames ativos

### Manutenção Programada
- Anunciada com 48h de antecedência
- Preferencialmente aos fins de semana
- Backup automático antes de atualização

---

## ✅ Checklist Final

### Antes dos Exames

**Coordenador**:
- [ ] Dados mestres atualizados
- [ ] Templates criados/atualizados
- [ ] Locais e salas verificados

**Membro**:
- [ ] Vaga criada com prazo adequado
- [ ] Candidaturas revistas
- [ ] Júris criados
- [ ] Vigilantes alocados (todos os júris)
- [ ] Supervisores atribuídos
- [ ] Verificar conflitos (nenhum)

**Vigilante**:
- [ ] Perfil completo
- [ ] Candidatura aprovada
- [ ] Júris conhecidos (data, hora, local)
- [ ] Disponibilidade confirmada

### Durante os Exames

- [ ] Telefone de emergência disponível
- [ ] Coordenador/Membro de plantão
- [ ] Sistema acessível
- [ ] Substitutos identificados

### Após os Exames

- [ ] Relatórios submetidos
- [ ] Dados exportados
- [ ] Vaga fechada
- [ ] Pagamentos processados
- [ ] Vaga encerrada (quando tudo completo)

---

## 🎓 Conclusão

Este guia cobre as **funcionalidades principais** do sistema. Para dúvidas específicas:

1. **Consulte**: Este guia e documentos relacionados
2. **Pesquise**: FAQ acima
3. **Contate**: Suporte técnico ou comissão

**O sistema foi desenvolvido para simplificar a gestão de exames. Use as ferramentas disponíveis e economize tempo!**

---

**Fim do Guia do Utilizador**

---

**Versão**: 2.1  
**Última Atualização**: 15/10/2025  
**Preparado por**: Equipa de Desenvolvimento  
**UniLicungo** - Universidade Licungo
