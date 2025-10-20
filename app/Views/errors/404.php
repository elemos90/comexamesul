<?php
use App\Utils\Auth;

$title = 'Página não encontrada';
if (!isset($isPublic)) { $isPublic = Auth::check() ? false : true; }
?>
<section class="py-24">
    <div class="max-w-xl mx-auto text-center space-y-4">
        <h1 class="text-5xl font-bold text-primary-600">404</h1>
        <p class="text-lg text-gray-700">A página solicitada não existe ou foi movida.</p>
        <a href="<?= Auth::check() ? '/dashboard' : '/' ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-500">Voltar</a>
    </div>
</section>

