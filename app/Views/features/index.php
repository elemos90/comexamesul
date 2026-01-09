<?php
/**
 * Feature Flags - Painel de Configuração
 * Apenas para Coordenadores
 */

use App\Utils\Auth;

$user = Auth::user();
?>

<div class="max-w-6xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Configurações de Funcionalidades</h1>
                <p class="text-gray-500 text-sm">Controle fino de acesso por perfil</p>
            </div>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-indigo-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm text-indigo-800">
                <strong>Como funciona:</strong> Desativar uma funcionalidade aqui irá:
            </p>
            <ul class="text-sm text-indigo-700 mt-1 ml-4 list-disc">
                <li>Ocultar botões e opções na interface</li>
                <li>Bloquear acesso direto às rotas</li>
                <li>Aplicar <strong>imediatamente</strong> a todos os utilizadores do perfil</li>
            </ul>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex gap-4">
            <?php foreach ($roleLabels as $role => $label): ?>
            <button type="button" 
                class="role-tab px-4 py-3 text-sm font-medium border-b-2 transition-colors <?= $role === 'membro' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>"
                data-role="<?= $role ?>">
                <?= $label ?>
                <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600" id="count-<?= $role ?>">
                    <?= count($flags[$role] ?? []) ?>
                </span>
            </button>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Content Panels -->
    <?php foreach ($flags as $role => $groups): ?>
    <div class="role-panel <?= $role !== 'membro' ? 'hidden' : '' ?>" data-role="<?= $role ?>">
        
        <?php foreach ($groups as $groupCode => $groupFlags): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-4 overflow-hidden">
            <!-- Group Header -->
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">
                    <?= $groupLabels[$groupCode] ?? ucfirst($groupCode) ?>
                </h3>
            </div>
            
            <!-- Flags -->
            <div class="divide-y divide-gray-100">
                <?php foreach ($groupFlags as $flag): ?>
                <div class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900"><?= htmlspecialchars($flag['feature_name']) ?></div>
                        <?php if (!empty($flag['feature_description'])): ?>
                        <div class="text-sm text-gray-500 mt-0.5"><?= htmlspecialchars($flag['feature_description']) ?></div>
                        <?php endif; ?>
                        <div class="text-xs text-gray-400 mt-1 font-mono"><?= htmlspecialchars($flag['feature_code']) ?></div>
                    </div>
                    <div class="ml-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                class="sr-only peer feature-toggle" 
                                data-role="<?= $role ?>"
                                data-code="<?= htmlspecialchars($flag['feature_code']) ?>"
                                <?= $flag['enabled'] ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($groups)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
            <div class="text-gray-400 mb-2">
                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-gray-500">Nenhuma funcionalidade configurável para este perfil</p>
        </div>
        <?php endif; ?>
        
    </div>
    <?php endforeach; ?>

    <!-- Actions Footer -->
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-500">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                Ativo
            </span>
            <span class="inline-flex items-center gap-1.5 ml-4">
                <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                Desativado
            </span>
        </div>
        <button type="button" id="btn-reset-defaults" 
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            Restaurar Padrões
        </button>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 transform translate-y-20 opacity-0 transition-all duration-300 z-50">
    <div class="bg-gray-900 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3">
        <span id="toast-icon"></span>
        <span id="toast-message"></span>
    </div>
</div>

<script>
const csrfToken = '<?= $csrfToken ?>';

// Tab switching
document.querySelectorAll('.role-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const role = this.dataset.role;
        
        // Update tabs
        document.querySelectorAll('.role-tab').forEach(t => {
            t.classList.remove('border-indigo-500', 'text-indigo-600');
            t.classList.add('border-transparent', 'text-gray-500');
        });
        this.classList.remove('border-transparent', 'text-gray-500');
        this.classList.add('border-indigo-500', 'text-indigo-600');
        
        // Update panels
        document.querySelectorAll('.role-panel').forEach(p => {
            p.classList.add('hidden');
        });
        document.querySelector(`.role-panel[data-role="${role}"]`).classList.remove('hidden');
    });
});

// Toggle handling
document.querySelectorAll('.feature-toggle').forEach(toggle => {
    toggle.addEventListener('change', async function() {
        const role = this.dataset.role;
        const featureCode = this.dataset.code;
        const enabled = this.checked;
        
        // Disable during request
        this.disabled = true;
        
        try {
            const response = await fetch('<?= url('/admin/features/toggle') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    csrf: csrfToken,
                    role: role,
                    feature_code: featureCode,
                    enabled: enabled ? '1' : '0'
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message, 'success');
            } else {
                // Revert toggle
                this.checked = !enabled;
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            this.checked = !enabled;
            showToast('Erro ao atualizar configuração', 'error');
        }
        
        this.disabled = false;
    });
});

// Reset defaults
document.getElementById('btn-reset-defaults').addEventListener('click', async function() {
    if (!confirm('Restaurar todas as configurações para os valores padrão?')) {
        return;
    }
    
    try {
        const response = await fetch('<?= url('/admin/features/reset') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ csrf: csrfToken })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Erro ao restaurar configurações', 'error');
    }
});

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toast-icon');
    const msg = document.getElementById('toast-message');
    
    msg.textContent = message;
    
    if (type === 'success') {
        icon.innerHTML = '<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
    } else if (type === 'error') {
        icon.innerHTML = '<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
    } else {
        icon.innerHTML = '<svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    }
    
    toast.classList.remove('translate-y-20', 'opacity-0');
    
    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3000);
}
</script>
