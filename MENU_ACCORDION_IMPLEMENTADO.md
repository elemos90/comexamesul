# 🎯 Menu Accordion (Expandir/Ocultar) - IMPLEMENTADO

## ✅ Funcionalidade Adicionada

**Data**: 09/10/2025 21:42  
**Status**: Completo e Funcional

---

## 🎯 Objetivo

Permitir que itens do menu com **submenu** possam **expandir** ou **ocultar** seus sub-itens com um clique (comportamento accordion).

---

## 📐 Comportamento Antes vs Depois

### ❌ Antes (Sempre Expandido)

```
Menu
├── Dashboard
├── Vagas
├── Júris ▼
│   ├── Planeamento    ← Sempre visível
│   └── Lista de Júris ← Sempre visível
├── Locais ▼
│   ├── Vis por Local  ← Sempre visível
│   ├── Dashboard      ← Sempre visível
│   ├── Templates      ← Sempre visível
│   └── Importar       ← Sempre visível
└── Perfil
```

**Problema**: Menu ocupava muito espaço vertical

---

### ✅ Depois (Accordion Interativo)

```
Menu
├── Dashboard
├── Vagas
├── Júris ▼ (clique para expandir/ocultar)
│   ├── Planeamento    ← Mostra/Esconde
│   └── Lista de Júris ← Mostra/Esconde
├── Locais ▶ (clique para expandir)
└── Perfil

(Após clicar em "Locais ▶")

Menu
├── Dashboard
├── Vagas
├── Júris ▶ (colapsado)
├── Locais ▼ (expandido)
│   ├── Vis por Local
│   ├── Dashboard
│   ├── Templates
│   └── Importar
└── Perfil
```

**Solução**: Menu compacto, expande apenas o necessário

---

## 🔧 Mudanças Técnicas

### 1. HTML Modificado

#### Antes:
```html
<div class="flex items-center">
    <span>Júris</span>
</div>
<div class="ml-6 space-y-1">
    <!-- Submenu sempre visível -->
</div>
```

#### Depois:
```html
<button class="submenu-toggle w-full flex justify-between">
    <span>Júris</span>
    <svg class="submenu-icon"><!-- Seta --></svg>
</button>
<div class="submenu-content hidden max-h-0">
    <!-- Submenu oculto por padrão -->
</div>
```

---

### 2. JavaScript Adicionado

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const submenuContent = this.nextElementSibling;
            const submenuIcon = this.querySelector('.submenu-icon');
            
            if (submenuContent.classList.contains('hidden')) {
                // Expandir
                submenuContent.classList.remove('hidden', 'max-h-0');
                submenuContent.style.maxHeight = submenuContent.scrollHeight + 'px';
                submenuIcon.classList.add('rotate-180');
            } else {
                // Colapsar
                submenuContent.style.maxHeight = '0';
                setTimeout(() => {
                    submenuContent.classList.add('hidden', 'max-h-0');
                }, 200);
                submenuIcon.classList.remove('rotate-180');
            }
        });
    });
});
```

---

### 3. CSS/Tailwind Classes

| Classe | Função |
|--------|--------|
| `submenu-toggle` | Botão clicável para toggle |
| `submenu-content` | Container do submenu |
| `hidden` | Esconde o submenu |
| `max-h-0` | Altura máxima 0 (colapsado) |
| `overflow-hidden` | Esconde overflow durante animação |
| `transition-all duration-200` | Transição suave de 200ms |
| `rotate-180` | Rotaciona ícone (seta) 180° |

---

## 🎨 Comportamento Visual

### Estado Inicial (Página Ativa em Submenu)

```
Menu
├── Dashboard
├── Vagas
├── Júris ▼ (expandido automaticamente)
│   ├── Planeamento ← página ativa
│   └── Lista de Júris
├── Locais ▶ (colapsado)
└── Perfil
```

**Inteligente**: Se a página atual está em um submenu, ele inicia expandido!

---

### Clique para Colapsar

```
Menu
├── Dashboard
├── Vagas
├── Júris ▶ (colapsado agora)
├── Locais ▶ (colapsado)
└── Perfil
```

**Compacto**: Menu ocupa menos espaço

---

### Clique para Expandir Outro

```
Menu
├── Dashboard
├── Vagas
├── Júris ▶ (colapsado)
├── Locais ▼ (expandido)
│   ├── Vis por Local
│   ├── Dashboard
│   ├── Templates
│   └── Importar
└── Perfil
```

**Independente**: Cada submenu funciona de forma independente

---

## 🎭 Animações

### Expansão (0ms → 200ms)
- `max-height: 0` → `max-height: [altura real]`
- Ícone rotaciona 0° → 180°
- Transição suave

### Colapso (0ms → 200ms)
- `max-height: [altura real]` → `max-height: 0`
- Ícone rotaciona 180° → 0°
- Após 200ms: adiciona `hidden`

---

## 🧪 Como Testar

### 1. Recarregue a página
```
http://localhost:8000/juries/planning
```

### 2. Observe o menu lateral:
- ✅ "Júris" deve estar **expandido** (você está nessa seção)
- ✅ "Locais" deve estar **colapsado** (▶)

### 3. Clique em "Júris ▼":
- ✅ Submenu deve **colapsar** com animação
- ✅ Seta deve rotacionar para ▶

### 4. Clique novamente em "Júris ▶":
- ✅ Submenu deve **expandir** com animação
- ✅ Seta deve rotacionar para ▼

### 5. Clique em "Locais ▶":
- ✅ Submenu deve **expandir**
- ✅ "Júris" permanece como estava (independente)

---

## 🔍 Detalhes Técnicos

### Estado Inicial Inteligente

```php
<?= $active ? '' : 'hidden max-h-0' ?>
```

- Se `$active = true` (página atual no submenu): **expandido**
- Se `$active = false`: **colapsado**

### Ícone de Seta

```html
<svg class="submenu-icon ... <?= $active ? 'transform rotate-180' : '' ?>">
```

- Se expandido: seta para baixo (▼) - `rotate-180`
- Se colapsado: seta para direita (▶) - `rotate-0`

### Altura Dinâmica

```javascript
submenuContent.style.maxHeight = submenuContent.scrollHeight + 'px';
```

- Calcula altura real do conteúdo
- Permite transição suave

---

## ✨ Benefícios

### Antes (Ruim)
- ❌ Menu muito longo verticalmente
- ❌ Todos submenus sempre visíveis
- ❌ Scroll desnecessário
- ❌ Visual poluído

### Depois (Bom)
- ✅ Menu compacto
- ✅ Expande apenas o necessário
- ✅ Menos scroll no sidebar
- ✅ Visual limpo e organizado
- ✅ UX moderna (padrão de dashboards)

---

## 📊 Casos de Uso

### 1. Menu com Muitos Itens
- Vários submenus podem ser colapsados
- Menu principal sempre visível

### 2. Navegação Rápida
- Usuário vê apenas o que interessa
- Menos distração visual

### 3. Mobile Friendly
- Ocupa menos espaço em telas pequenas
- Mais fácil de navegar

---

## 🎯 Estrutura Atual do Menu

```
Menu
├── Dashboard (simples)
├── Vagas (simples)
├── Disponibilidade (simples)
├── Júris (accordion)
│   ├── Planeamento
│   └── Lista de Júris
├── Locais (accordion)
│   ├── Vis por Local
│   ├── Dashboard
│   ├── Templates
│   └── Importar
└── Perfil (simples)
```

**Total**: 2 itens com accordion, 4 itens simples

---

## 🐛 Troubleshooting

### Problema: Submenu não expande
**Solução**: Verifique se o JavaScript foi carregado (F12 → Console)

### Problema: Animação não funciona
**Solução**: Certifique-se que Tailwind está carregado

### Problema: Múltiplos submenus expandidos
**Comportamento normal**: Cada accordion é independente

### Problema: Seta não rotaciona
**Solução**: Verifique classe `submenu-icon` no SVG

---

## 🚀 Melhorias Futuras (Opcionais)

### 1. Fechar Outros ao Abrir Um
```javascript
// Fechar todos antes de abrir novo
document.querySelectorAll('.submenu-content').forEach(content => {
    if (content !== submenuContent) {
        content.classList.add('hidden', 'max-h-0');
    }
});
```

### 2. Salvar Estado no LocalStorage
```javascript
// Lembrar quais estavam abertos
localStorage.setItem('sidebar-state', JSON.stringify(state));
```

### 3. Animação de Slide
```css
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
```

---

## 📚 Referências

- **Accordion Pattern**: https://www.w3.org/WAI/ARIA/apg/patterns/accordion/
- **Tailwind Transitions**: https://tailwindcss.com/docs/transition-property
- **CSS Transforms**: https://developer.mozilla.org/en-US/docs/Web/CSS/transform

---

## ✅ Checklist de Verificação

- [x] Submenu expande ao clicar
- [x] Submenu colapsa ao clicar novamente
- [x] Animação suave (200ms)
- [x] Ícone rotaciona corretamente
- [x] Estado inicial inteligente (expandido se ativo)
- [x] Independência entre accordions
- [x] Acessível via teclado (botão)
- [x] Responsivo

---

## 🎉 Resultado Final

**Menu lateral com accordion moderno e funcional!**

```
Clique → Expande/Colapsa
Animação suave → UX profissional
Estado inteligente → Sempre mostra o relevante
Visual limpo → Menu organizado
```

**Navegação intuitiva e eficiente! ✨**

---

**Implementação concluída com sucesso! 🚀**
