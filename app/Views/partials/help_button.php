<?php
/**
 * Botão de Ajuda Contextual
 * 
 * Uso: <?php include __DIR__ . '/../partials/help_button.php'; ?>
 * Variável necessária: $helpPage (identificador da página)
 */

if (!isset($helpPage)) {
    $helpPage = 'default';
}
?>

<button type="button" 
        class="help-button" 
        onclick="openHelp('<?= $helpPage ?>')"
        title="Clique para ver ajuda sobre esta página"
        aria-label="Ajuda">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span class="help-button-text">Ajuda</span>
</button>

<style>
.help-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background-color: #3B82F6;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.help-button:hover {
    background-color: #2563EB;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.help-button:active {
    transform: translateY(0);
}

.help-button svg {
    width: 1.25rem;
    height: 1.25rem;
}

@media (max-width: 640px) {
    .help-button-text {
        display: none;
    }
    
    .help-button {
        padding: 0.5rem;
    }
}
</style>
