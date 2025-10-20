# 🎨 Design System - Portal de Exames UniLicungo

Guia completo de componentes e padrões visuais para manter consistência em todo o sistema.

---

## 🎯 Paleta de Cores

### Primárias
```
Blue (Principal): #2563EB - bg-blue-600
Green (Sucesso): #059669 - bg-green-600
Red (Erro/Atenção): #DC2626 - bg-red-600
Purple (Destaque): #7C3AED - bg-purple-600
Orange (Aviso): #EA580C - bg-orange-600
```

### Neutras
```
Gray 50: #F9FAFB - Fundo claro
Gray 100: #F3F4F6 - Fundo cards
Gray 200: #E5E7EB - Bordas
Gray 600: #4B5563 - Texto secundário
Gray 900: #111827 - Texto principal
```

---

## 📦 Cards Modernos

### Card Padrão
```html
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-300">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Título do Card</h3>
    <p class="text-gray-600">Conteúdo aqui...</p>
</div>
```

### Card com Header
```html
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">Header do Card</h3>
    </div>
    <div class="p-6">
        Conteúdo aqui...
    </div>
</div>
```

### Stats Card (Dashboard)
```html
<div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white">...</svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-blue-900">245</div>
            <div class="text-sm text-blue-700">Total de Vagas</div>
        </div>
    </div>
</div>
```

---

## 🏷️ Badges e Tags

```html
<!-- Status Badges -->
<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
    Ativo
</span>

<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
    Pendente
</span>

<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
    Cancelado
</span>

<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
    Agendado
</span>
```

---

## 🔘 Botões

### Botões Primários
```html
<!-- Primary Button -->
<button class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-colors duration-200 shadow-sm hover:shadow">
    Ação Principal
</button>

<!-- Success Button -->
<button class="px-5 py-2.5 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-sm">
    Confirmar
</button>

<!-- Danger Button -->
<button class="px-5 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors duration-200 shadow-sm">
    Excluir
</button>
```

### Botões Secundários
```html
<!-- Secondary Button -->
<button class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors duration-200">
    Cancelar
</button>

<!-- Outline Button -->
<button class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors duration-200">
    Voltar
</button>
```

---

## 📝 Inputs e Forms

### Input Padrão
```html
<div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">Nome</label>
    <input type="text" 
           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" 
           placeholder="Digite o nome">
</div>
```

### Input com Erro
```html
<div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">Email</label>
    <input type="email" 
           class="w-full px-4 py-2.5 border border-red-500 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" 
           placeholder="seu@email.com">
    <p class="text-sm text-red-600">Este campo é obrigatório</p>
</div>
```

---

## 📊 Tabelas Modernas

```html
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 border-b-2 border-gray-200">
            <tr>
                <th class="px-4 py-3 font-semibold text-gray-700 uppercase text-xs tracking-wider">Nome</th>
                <th class="px-4 py-3 font-semibold text-gray-700 uppercase text-xs tracking-wider">Status</th>
                <th class="px-4 py-3 font-semibold text-gray-700 uppercase text-xs tracking-wider">Ações</th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150">
                <td class="px-4 py-4">João Silva</td>
                <td class="px-4 py-4">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Ativo</span>
                </td>
                <td class="px-4 py-4">
                    <button class="text-blue-600 hover:text-blue-800">Editar</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

---

## 📢 Alertas

```html
<!-- Success Alert -->
<div class="p-4 rounded-lg border bg-green-50 border-green-200 text-green-800">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5">...</svg>
        <span class="font-medium">Sucesso! Operação concluída.</span>
    </div>
</div>

<!-- Error Alert -->
<div class="p-4 rounded-lg border bg-red-50 border-red-200 text-red-800">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5">...</svg>
        <span class="font-medium">Erro! Algo deu errado.</span>
    </div>
</div>

<!-- Warning Alert -->
<div class="p-4 rounded-lg border bg-yellow-50 border-yellow-200 text-yellow-800">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5">...</svg>
        <span class="font-medium">Atenção! Verifique os dados.</span>
    </div>
</div>
```

---

## 📱 Layout de Página Padrão

```html
<!-- Header da Página -->
<div class="mb-8 pb-6 border-b border-gray-200">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Título da Página</h1>
    <p class="text-gray-600 text-lg">Descrição breve da página</p>
</div>

<!-- Grid de Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Cards aqui -->
</div>
```

---

## 🎯 Empty States

```html
<div class="flex flex-col items-center justify-center py-16 text-center">
    <svg class="w-24 h-24 text-gray-300 mb-4">...</svg>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">Nenhum resultado encontrado</h3>
    <p class="text-gray-600 max-w-md mb-6">Não há dados para exibir no momento.</p>
    <button class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">
        Criar Novo
    </button>
</div>
```

---

## ⚡ Loading States

```html
<!-- Loading Spinner -->
<div class="flex items-center justify-center py-8">
    <div class="inline-block w-8 h-8 border-4 border-gray-300 border-t-blue-600 rounded-full animate-spin"></div>
</div>

<!-- Skeleton Loading -->
<div class="space-y-3">
    <div class="h-4 bg-gray-200 animate-pulse rounded w-3/4"></div>
    <div class="h-4 bg-gray-200 animate-pulse rounded w-1/2"></div>
    <div class="h-4 bg-gray-200 animate-pulse rounded w-5/6"></div>
</div>
```

---

## 🎨 Gradientes e Efeitos

### Gradientes de Fundo
```html
<!-- Blue Gradient -->
<div class="bg-gradient-to-r from-blue-600 to-blue-700">...</div>

<!-- Multi-color Gradient -->
<div class="bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50">...</div>

<!-- Subtle Gradient -->
<div class="bg-gradient-to-b from-gray-50 to-white">...</div>
```

### Sombras
```html
<!-- Shadow Small -->
<div class="shadow-sm">...</div>

<!-- Shadow Medium -->
<div class="shadow-md">...</div>

<!-- Shadow Large -->
<div class="shadow-lg">...</div>

<!-- Shadow Extra Large -->
<div class="shadow-xl">...</div>
```

---

## 🎭 Animações

```html
<!-- Fade In -->
<div class="transition-opacity duration-300 hover:opacity-75">...</div>

<!-- Scale on Hover -->
<div class="transition-transform duration-300 hover:scale-105">...</div>

<!-- Slide Up -->
<div class="transition-all duration-300 translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100">...</div>
```

---

## 📐 Espaçamentos Padrão

```
Padding pequeno: p-4 (16px)
Padding médio: p-6 (24px)
Padding grande: p-8 (32px)

Gap pequeno: gap-2 (8px)
Gap médio: gap-4 (16px)
Gap grande: gap-6 (24px)

Margin pequena: mb-4 (16px)
Margin média: mb-6 (24px)
Margin grande: mb-8 (32px)
```

---

## ✅ Checklist de Qualidade

- [ ] Cores consistentes com a paleta
- [ ] Espaçamento uniforme (4, 6, 8)
- [ ] Bordas arredondadas (rounded-lg, rounded-xl)
- [ ] Sombras suaves (shadow-sm, shadow-md)
- [ ] Transições em hover states
- [ ] Ícones com tamanho apropriado
- [ ] Texto legível (gray-600 para secundário, gray-900 para principal)
- [ ] Feedback visual em ações
- [ ] Loading states onde necessário
- [ ] Empty states informativos

---

**Mantemos este padrão em TODAS as páginas do sistema!** 🎨
