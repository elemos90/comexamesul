# 🚀 Quick Start - Portal de Exames v2.0

Guia rápido para começar a usar as novas funcionalidades.

---

## 📦 Instalação Completa (15 minutos)

### Passo 1: Configurar Ambiente
```bash
# 1. Clone o projeto
git clone [url-do-projeto]
cd comexamesul

# 2. Configure .env
cp .env.example .env
# Edite .env com suas credenciais de BD

# 3. Instale dependências
composer install
```

### Passo 2: Criar Base de Dados
```bash
# Crie o banco no MySQL
mysql -u root -p
CREATE DATABASE comexamesul CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Execute migrações principais
mysql -u root -p comexamesul < app/Database/migrations.sql
mysql -u root -p comexamesul < app/Database/seed.sql

# Execute migrações das novas funcionalidades
php scripts/install_locations_features.php
```

### Passo 3: Configurar Servidor
```bash
# Opção A: PHP Built-in Server (desenvolvimento)
php -S localhost:8000 -t public

# Opção B: Apache/Nginx
# Aponte DocumentRoot para: /caminho/comexamesul/public
```

### Passo 4: Acessar Sistema
```
URL: http://localhost:8000 (ou sua URL configurada)

Credenciais:
- Coordenador: coordenador@unilicungo.ac.mz / password
- Membro: membro@unilicungo.ac.mz / password
- Vigilante: vigilante1@unilicungo.ac.mz / password
```

---

## 🎯 Uso Rápido das Novas Funcionalidades

### 1️⃣ Criar Júris por Local (Modo Novo)

**Cenário**: Criar exames para Campus Central em 15/11/2025

1. Login como Coordenador
2. Menu → **Júris**
3. Clique em **"Criar Exames por Local"** (botão azul)
4. Preencha:
   ```
   Local: Campus Central
   Data: 15/11/2025
   ```
5. Adicione Disciplinas:
   - **Matemática I**: 08:00 - 11:00 → Salas: 101, 102, 103
   - **Física I**: 14:00 - 17:00 → Salas: 201, 202
6. Clique em **"Criar Todos os Júris"**
7. ✅ 5 júris criados automaticamente!

**Tempo estimado**: 2 minutos

---

### 2️⃣ Salvar Template de Local

**Cenário**: Salvar configuração padrão do Campus Central

1. Menu → **Locais** → **Templates**
2. Clique em **"Novo Template"**
3. Preencha:
   ```
   Nome: Campus Central - Padrão Matutino
   Local: Campus Central
   Descrição: 3 disciplinas no período matutino
   ```
4. Adicione disciplinas (mesmo processo do item 1)
5. Clique em **"Salvar Template"**
6. ✅ Template salvo!

**Reutilizar Template**:
- Na lista de templates, clique em **"Usar"**
- Apenas informe a data
- Pronto!

**Tempo estimado**: 3 minutos para criar, 30 segundos para reutilizar

---

### 3️⃣ Importar Júris via Excel

**Cenário**: Importar 50 júris de uma planilha

1. Menu → **Locais** → **Importar**
2. Clique em **"Baixar Template"**
3. Abra o arquivo Excel baixado
4. Preencha as linhas:
   ```
   Local         | Data       | Disciplina   | Início | Fim   | Sala | Candidatos
   Campus Norte  | 20/11/2025 | Matemática I | 08:00  | 11:00 | A1   | 30
   Campus Norte  | 20/11/2025 | Matemática I | 08:00  | 11:00 | A2   | 28
   Campus Norte  | 20/11/2025 | Física I     | 14:00  | 17:00 | B1   | 35
   ...
   ```
5. Salve o arquivo
6. Volte ao sistema e faça upload
7. ✅ Júris criados automaticamente!

**Tempo estimado**: 10 minutos para 50 júris

---

### 4️⃣ Visualizar por Local

**Cenário**: Ver todos os júris organizados por local

1. Menu → **Locais** → **Vis por Local**
2. Você verá cards agrupados:
   ```
   📍 Campus Central - 15/11/2025
       📚 Matemática I (08:00-11:00)
           🚪 Sala 101 (30 cand.) → Clique para detalhes
           🚪 Sala 102 (28 cand.)
           🚪 Sala 103 (32 cand.)
       📚 Física I (14:00-17:00)
           🚪 Sala 201 (35 cand.)
           🚪 Sala 202 (35 cand.)
   ```
3. Clique em qualquer sala para ver detalhes
4. Continue alocando vigilantes via drag-and-drop

**Tempo estimado**: Visualização instantânea

---

### 5️⃣ Ver Estatísticas

**Cenário**: Analisar recursos por local

1. Menu → **Locais** → **Dashboard**
2. Veja:
   - **Top Locais**: Ranking por capacidade
   - **Estatísticas por Local**: Cards individuais
   - **Breakdown por Data**: Detalhes de cada data
3. Use para:
   - Identificar locais mais usados
   - Planejar alocação de vigilantes
   - Gerar relatórios gerenciais

**Tempo estimado**: Análise instantânea

---

## 🎓 Fluxo de Trabalho Recomendado

### Início de Semestre

```
1. Criar Templates
   ↓
2. Definir Locais e Datas
   ↓
3. Usar Templates OU Importar Planilha
   ↓
4. Alocar Vigilantes (Drag-and-Drop)
   ↓
5. Atribuir Supervisores
   ↓
6. Acompanhar via Dashboard
```

### Durante Exames

```
1. Visualizar por Local
   ↓
2. Verificar alocações
   ↓
3. Fazer ajustes se necessário
   ↓
4. Supervisores submetem relatórios
```

### Após Exames

```
1. Dashboard de Locais
   ↓
2. Extrair estatísticas
   ↓
3. Gerar relatórios
   ↓
4. Planejar próximo período
```

---

## 💡 Dicas Importantes

### ✅ Boas Práticas

1. **Use Templates para locais recorrentes**
   - Poupa tempo em 90%

2. **Importe planilhas para grandes volumes**
   - Melhor que criar manualmente

3. **Visualize por Local durante períodos intensos**
   - Visão clara de todos os exames

4. **Consulte Dashboard regularmente**
   - Identifica gargalos antecipadamente

5. **Nomeie Templates descritivamente**
   - Ex: "Campus A - Matutino 5 salas"

### ⚠️ Evite

1. ❌ Criar júris um a um quando há template disponível
2. ❌ Duplicar configurações manualmente
3. ❌ Ignorar conflitos de horário de vigilantes
4. ❌ Não usar a visualização hierárquica

---

## 🔧 Troubleshooting

### Problema: "Erro ao criar júris"
**Solução**: Verifique se as tabelas foram instaladas
```bash
php scripts/install_locations_features.php
```

### Problema: "Importação falhou"
**Solução**: Verifique formato da planilha
- Use template fornecido
- Formato de data: dd/mm/yyyy
- Formato de hora: HH:MM

### Problema: "Template não carrega"
**Solução**: Limpe cache do navegador (Ctrl+F5)

### Problema: "Dashboard vazio"
**Solução**: Crie alguns júris primeiro
- Sistema calcula estatísticas automaticamente

---

## 📚 Documentação Adicional

- **NOVAS_FUNCIONALIDADES.md**: Documentação técnica completa
- **GUIA_CRIACAO_JURIS_POR_LOCAL.md**: Guia detalhado de criação
- **TESTE_DRAG_DROP.md**: Como testar alocação de vigilantes

---

## 🎯 Checklist de Verificação Inicial

- [ ] Base de dados criada
- [ ] Migrações principais executadas
- [ ] Migrações de locais executadas
- [ ] Servidor rodando
- [ ] Login funcionando
- [ ] Menu "Locais" visível
- [ ] 4 submenu items presentes
- [ ] Primeiro template criado com sucesso
- [ ] Primeiro júri criado via template
- [ ] Dashboard exibindo estatísticas

---

## 🚀 Você está Pronto!

Com estes passos, você terá o sistema completo funcionando com todas as novas funcionalidades.

**Dúvidas?** Consulte a documentação técnica ou abra uma issue.

**Última atualização**: 09/10/2025 | **Versão**: 2.0
