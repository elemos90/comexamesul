<?php
$title = 'Portal da Comissão de Exames de Admissão';
$isPublic = true;
$currentMonth = date('F Y');
$currentYear = date('Y');

// Garante que as variáveis existem
$vacancies = $vacancies ?? [];
$juriesByLocation = $juriesByLocation ?? [];
?>

<!-- Hero Section Modernizado com Imagem Acadêmica -->
<section class="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 text-white relative overflow-hidden">
    <!-- Padrão de fundo sutil -->
    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    
    <!-- Decoração de círculos -->
    <div class="absolute top-10 right-10 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>
    <div class="absolute bottom-10 left-10 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl"></div>
    
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-24 relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Coluna Esquerda: Texto e CTA -->
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    <span>Processo de Exames <?= $currentYear ?> Ativo</span>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
                    Portal de Exames<br>de Admissão
                </h1>
                <p class="text-xl text-blue-100">
                    Centro oficial de informações, calendários e recursos para candidatos, vigilantes e membros da comissão.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="/register" class="px-6 py-3 bg-white text-blue-700 font-semibold rounded-lg hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl">
                        Candidatar-se Agora
                    </a>
                    <a href="/login" class="px-6 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-all">
                        Entrar no Sistema
                    </a>
                </div>
                
                <!-- Stats inline abaixo dos botões -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold"><?= count($vacancies ?? []) ?></div>
                        <div class="text-blue-100 text-xs mt-1">Vagas Abertas</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold"><?= count($juriesByLocation ?? []) ?></div>
                        <div class="text-blue-100 text-xs mt-1">Locais de Exame</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold">15</div>
                        <div class="text-blue-100 text-xs mt-1">Dias até exame</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold">24/7</div>
                        <div class="text-blue-100 text-xs mt-1">Suporte</div>
                    </div>
                </div>
            </div>
            
            <!-- Coluna Direita: Ilustração Vibrante com Alto Contraste -->
            <div class="hidden lg:block relative">
                <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl">
                    <!-- Fundo transparente para contraste com azul -->
                    <defs>
                        <linearGradient id="glowGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#FBBF24;stop-opacity:0.2" />
                            <stop offset="100%" style="stop-color:#F59E0B;stop-opacity:0.1" />
                        </linearGradient>
                        <filter id="glow">
                            <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
                            <feMerge>
                                <feMergeNode in="coloredBlur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    
                    <!-- Círculos de fundo brilhantes -->
                    <circle cx="250" cy="250" r="200" fill="url(#glowGradient)" opacity="0.3"/>
                    
                    <!-- Laptop/Computador Central -->
                    <g class="animate-float-device">
                        <!-- Base do laptop -->
                        <rect x="150" y="280" width="200" height="10" rx="5" fill="#1F2937" opacity="0.8"/>
                        
                        <!-- Tela do laptop -->
                        <rect x="165" y="150" width="170" height="130" rx="8" fill="#1F2937"/>
                        <rect x="175" y="160" width="150" height="110" rx="4" fill="#FFFFFF"/>
                        
                        <!-- Conteúdo da tela - Dashboard -->
                        <rect x="185" y="170" width="130" height="20" rx="3" fill="#3B82F6" opacity="0.9"/>
                        <text x="250" y="184" font-size="10" fill="#FFFFFF" text-anchor="middle" font-weight="bold">PORTAL DE EXAMES</text>
                        
                        <!-- Barras de progresso/estatísticas -->
                        <rect x="185" y="200" width="100" height="8" rx="4" fill="#E5E7EB"/>
                        <rect x="185" y="200" width="70" height="8" rx="4" fill="#10B981"/>
                        
                        <rect x="185" y="215" width="100" height="8" rx="4" fill="#E5E7EB"/>
                        <rect x="185" y="215" width="90" height="8" rx="4" fill="#3B82F6"/>
                        
                        <rect x="185" y="230" width="100" height="8" rx="4" fill="#E5E7EB"/>
                        <rect x="185" y="230" width="60" height="8" rx="4" fill="#F59E0B"/>
                        
                        <!-- Ícones na tela -->
                        <circle cx="195" cy="252" r="8" fill="#3B82F6" opacity="0.2"/>
                        <circle cx="215" cy="252" r="8" fill="#10B981" opacity="0.2"/>
                        <circle cx="235" cy="252" r="8" fill="#F59E0B" opacity="0.2"/>
                    </g>
                    
                    <!-- Troféu Dourado (Sucesso) -->
                    <g class="animate-bounce-trophy">
                        <circle cx="380" cy="180" r="45" fill="#FEF3C7" opacity="0.4"/>
                        <!-- Base do troféu -->
                        <rect x="365" y="210" width="30" height="8" rx="2" fill="#92400E"/>
                        <rect x="370" y="218" width="20" height="4" rx="1" fill="#92400E"/>
                        <!-- Taça -->
                        <path d="M 365 180 Q 365 160 380 160 Q 395 160 395 180 L 395 195 Q 395 205 380 205 Q 365 205 365 195 Z" fill="#FBBF24" stroke="#F59E0B" stroke-width="2"/>
                        <!-- Alças -->
                        <path d="M 365 175 Q 355 175 355 185 Q 355 190 360 190" stroke="#F59E0B" stroke-width="2" fill="none"/>
                        <path d="M 395 175 Q 405 175 405 185 Q 405 190 400 190" stroke="#F59E0B" stroke-width="2" fill="none"/>
                        <!-- Estrela no troféu -->
                        <polygon points="380,175 382,182 390,182 384,187 386,195 380,190 374,195 376,187 370,182 378,182" fill="#FEF3C7"/>
                    </g>
                    
                    <!-- Livro Aberto com Luz -->
                    <g class="animate-float-book">
                        <circle cx="120" cy="200" r="50" fill="#FEF3C7" opacity="0.3"/>
                        <!-- Livro -->
                        <path d="M 90 190 L 90 230 L 120 225 L 150 230 L 150 190 Z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
                        <line x1="120" y1="190" x2="120" y2="225" stroke="#CBD5E1" stroke-width="2"/>
                        <!-- Páginas -->
                        <line x1="95" y1="200" x2="115" y2="198" stroke="#94A3B8" stroke-width="1.5"/>
                        <line x1="95" y1="207" x2="112" y2="205" stroke="#94A3B8" stroke-width="1.5"/>
                        <line x1="95" y1="214" x2="110" y2="212" stroke="#94A3B8" stroke-width="1.5"/>
                        <line x1="125" y1="198" x2="145" y2="200" stroke="#94A3B8" stroke-width="1.5"/>
                        <line x1="128" y1="205" x2="145" y2="207" stroke="#94A3B8" stroke-width="1.5"/>
                        <line x1="130" y1="212" x2="145" y2="214" stroke="#94A3B8" stroke-width="1.5"/>
                        <!-- Raios de luz -->
                        <line x1="120" y1="190" x2="110" y2="175" stroke="#FBBF24" stroke-width="2" opacity="0.7"/>
                        <line x1="120" y1="190" x2="120" y2="170" stroke="#FBBF24" stroke-width="2" opacity="0.7"/>
                        <line x1="120" y1="190" x2="130" y2="175" stroke="#FBBF24" stroke-width="2" opacity="0.7"/>
                    </g>
                    
                    <!-- Certificado com Selo -->
                    <g class="animate-float-cert">
                        <circle cx="100" cy="380" r="50" fill="#DBEAFE" opacity="0.3"/>
                        <!-- Papel -->
                        <rect x="70" y="355" width="60" height="50" rx="3" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2"/>
                        <!-- Linhas -->
                        <line x1="78" y1="365" x2="122" y2="365" stroke="#CBD5E1" stroke-width="2"/>
                        <line x1="78" y1="373" x2="115" y2="373" stroke="#E5E7EB" stroke-width="1.5"/>
                        <line x1="78" y1="380" x2="118" y2="380" stroke="#E5E7EB" stroke-width="1.5"/>
                        <!-- Selo dourado -->
                        <circle cx="110" cy="395" r="10" fill="#FBBF24"/>
                        <circle cx="110" cy="395" r="7" fill="#F59E0B"/>
                        <text x="110" y="399" font-size="10" fill="#FFFFFF" text-anchor="middle" font-weight="bold">✓</text>
                        <!-- Fita -->
                        <rect x="108" y="405" width="4" height="12" fill="#EF4444"/>
                        <polygon points="108,417 110,420 112,417" fill="#EF4444"/>
                    </g>
                    
                    <!-- Relógio/Cronômetro -->
                    <g class="animate-rotate-clock">
                        <circle cx="400" cy="350" r="40" fill="#FFFFFF" stroke="#64748B" stroke-width="3"/>
                        <circle cx="400" cy="350" r="35" fill="#F8FAFC"/>
                        <!-- Ponteiros -->
                        <line x1="400" y1="350" x2="400" y2="325" stroke="#3B82F6" stroke-width="3" stroke-linecap="round"/>
                        <line x1="400" y1="350" x2="415" y2="350" stroke="#10B981" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="400" cy="350" r="4" fill="#1F2937"/>
                        <!-- Marcações -->
                        <circle cx="400" cy="320" r="2" fill="#64748B"/>
                        <circle cx="430" cy="350" r="2" fill="#64748B"/>
                        <circle cx="400" cy="380" r="2" fill="#64748B"/>
                        <circle cx="370" cy="350" r="2" fill="#64748B"/>
                    </g>
                    
                    <!-- Estrelas brilhantes -->
                    <g class="animate-twinkle">
                        <polygon points="80,80 82,88 90,90 82,92 80,100 78,92 70,90 78,88" fill="#FBBF24" opacity="0.9"/>
                        <polygon points="420,100 422,106 428,108 422,110 420,116 418,110 412,108 418,106" fill="#FEF3C7" opacity="0.8"/>
                        <polygon points="450,250 451,254 455,255 451,256 450,260 449,256 445,255 449,254" fill="#FBBF24" opacity="0.9"/>
                        <polygon points="60,320 61,323 64,324 61,325 60,328 59,325 56,324 59,323" fill="#FEF3C7" opacity="0.8"/>
                    </g>
                    
                    <!-- Partículas flutuantes -->
                    <circle cx="180" cy="120" r="4" fill="#FBBF24" opacity="0.6" class="animate-particle"/>
                    <circle cx="340" cy="280" r="3" fill="#3B82F6" opacity="0.6" class="animate-particle" style="animation-delay: 1s;"/>
                    <circle cx="450" cy="420" r="5" fill="#10B981" opacity="0.6" class="animate-particle" style="animation-delay: 2s;"/>
                </svg>
                
                <!-- CSS Animations inline -->
                <style>
                    @keyframes float-device {
                        0%, 100% { transform: translateY(0px); }
                        50% { transform: translateY(-8px); }
                    }
                    @keyframes bounce-trophy {
                        0%, 100% { transform: translateY(0px) scale(1); }
                        50% { transform: translateY(-12px) scale(1.05); }
                    }
                    @keyframes float-book {
                        0%, 100% { transform: translateY(0px) rotate(0deg); }
                        50% { transform: translateY(-6px) rotate(-2deg); }
                    }
                    @keyframes float-cert {
                        0%, 100% { transform: translateY(0px) rotate(0deg); }
                        50% { transform: translateY(-5px) rotate(2deg); }
                    }
                    @keyframes rotate-clock {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                    @keyframes twinkle {
                        0%, 100% { opacity: 1; transform: scale(1); }
                        50% { opacity: 0.4; transform: scale(0.8); }
                    }
                    @keyframes particle {
                        0%, 100% { transform: translateY(0px); opacity: 0.6; }
                        50% { transform: translateY(-20px); opacity: 1; }
                    }
                    .animate-float-device { animation: float-device 3s ease-in-out infinite; }
                    .animate-bounce-trophy { animation: bounce-trophy 2s ease-in-out infinite; }
                    .animate-float-book { animation: float-book 4s ease-in-out infinite; }
                    .animate-float-cert { animation: float-cert 3.5s ease-in-out infinite; }
                    .animate-rotate-clock { animation: rotate-clock 60s linear infinite; transform-origin: center; }
                    .animate-twinkle { animation: twinkle 2s ease-in-out infinite; }
                    .animate-particle { animation: particle 4s ease-in-out infinite; }
                </style>
            </div>
        </div>
    </div>
</section>

<!-- Notificações e Atualizações -->
<section class="py-12 bg-gradient-to-r from-amber-50 to-orange-50 border-b-4 border-amber-400">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-amber-500 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-2">📢 Atualizações Recentes</h3>
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-gray-700">
                        <span class="text-xs bg-amber-200 px-2 py-1 rounded font-semibold"><?= date('d M') ?></span>
                        <span>Calendário de exames atualizado para <?= $currentMonth ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-700">
                        <span class="text-xs bg-amber-200 px-2 py-1 rounded font-semibold"><?= date('d M', strtotime('-2 days')) ?></span>
                        <span>Novas vagas para vigilantes disponíveis</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-700">
                        <span class="text-xs bg-amber-200 px-2 py-1 rounded font-semibold"><?= date('d M', strtotime('-5 days')) ?></span>
                        <span>Guia do candidato <?= $currentYear ?> disponível para download</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content Grid: Calendário, Exames, Vídeos -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- COLUNA 1: Calendário e Datas Importantes -->
            <div class="space-y-6">
                <!-- Mini Calendário -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">📅 <?= date('F Y') ?></h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-7 gap-2 text-center text-sm">
                            <div class="font-semibold text-gray-600 text-xs">Dom</div>
                            <div class="font-semibold text-gray-600 text-xs">Seg</div>
                            <div class="font-semibold text-gray-600 text-xs">Ter</div>
                            <div class="font-semibold text-gray-600 text-xs">Qua</div>
                            <div class="font-semibold text-gray-600 text-xs">Qui</div>
                            <div class="font-semibold text-gray-600 text-xs">Sex</div>
                            <div class="font-semibold text-gray-600 text-xs">Sáb</div>
                            
                            <?php
                            $hoje = (int)date('d');
                            $primeiroDia = (int)date('w', strtotime(date('Y-m-01')));
                            $diasNoMes = (int)date('t');
                            
                            // Espaços vazios antes do primeiro dia
                            for($i = 0; $i < $primeiroDia; $i++): ?>
                                <div></div>
                            <?php endfor;
                            
                            // Dias do mês
                            for($dia = 1; $dia <= $diasNoMes; $dia++):
                                $isHoje = ($dia == $hoje);
                                $isExame = in_array($dia, [20, 22, 25, 27]); // Dias com exames
                                $classes = 'p-2 rounded-lg ';
                                if($isHoje) $classes .= 'bg-blue-600 text-white font-bold';
                                elseif($isExame) $classes .= 'bg-red-100 text-red-700 font-semibold';
                                else $classes .= 'text-gray-700 hover:bg-gray-100';
                            ?>
                                <div class="<?= $classes ?>"><?= $dia ?></div>
                            <?php endfor; ?>
                        </div>
                        <div class="mt-4 flex items-center gap-4 text-xs">
                            <div class="flex items-center gap-1">
                                <div class="w-3 h-3 bg-blue-600 rounded"></div>
                                <span class="text-gray-600">Hoje</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <div class="w-3 h-3 bg-red-100 border border-red-300 rounded"></div>
                                <span class="text-gray-600">Exame</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Próximas Datas Importantes -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Próximos Eventos
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-600">
                            <div class="text-center bg-blue-600 text-white rounded-lg px-3 py-1 text-sm font-bold min-w-[60px]">
                                <div class="text-lg">20</div>
                                <div class="text-xs">OUT</div>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900 text-sm">Inscrições Encerram</div>
                                <div class="text-xs text-gray-600">Último dia para candidaturas</div>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg border-l-4 border-green-600">
                            <div class="text-center bg-green-600 text-white rounded-lg px-3 py-1 text-sm font-bold min-w-[60px]">
                                <div class="text-lg">22</div>
                                <div class="text-xs">OUT</div>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900 text-sm">Exame de Matemática</div>
                                <div class="text-xs text-gray-600">09:00 - 12:00 · Campus Beira</div>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 p-3 bg-purple-50 rounded-lg border-l-4 border-purple-600">
                            <div class="text-center bg-purple-600 text-white rounded-lg px-3 py-1 text-sm font-bold min-w-[60px]">
                                <div class="text-lg">25</div>
                                <div class="text-xs">OUT</div>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900 text-sm">Exame de Física</div>
                                <div class="text-xs text-gray-600">09:00 - 11:30 · Campus Beira</div>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 p-3 bg-orange-50 rounded-lg border-l-4 border-orange-600">
                            <div class="text-center bg-orange-600 text-white rounded-lg px-3 py-1 text-sm font-bold min-w-[60px]">
                                <div class="text-lg">27</div>
                                <div class="text-xs">OUT</div>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900 text-sm">Publicação de Resultados</div>
                                <div class="text-xs text-gray-600">Portal online</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- COLUNA 2 e 3: Calendário de Exames e Vídeos -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Calendário de Exames Completo -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">📋 Calendário de Exames Completo</h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b-2 border-gray-200">
                                        <th class="text-left py-3 px-3 font-semibold text-gray-700">Data</th>
                                        <th class="text-left py-3 px-3 font-semibold text-gray-700">Disciplina</th>
                                        <th class="text-left py-3 px-3 font-semibold text-gray-700">Horário</th>
                                        <th class="text-left py-3 px-3 font-semibold text-gray-700">Local</th>
                                        <th class="text-center py-3 px-3 font-semibold text-gray-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-3 font-medium">20/10/2025</td>
                                        <td class="py-4 px-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                                <span class="font-medium">Matemática</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-3 text-gray-600">09:00 - 12:00</td>
                                        <td class="py-4 px-3 text-gray-600">Campus Beira</td>
                                        <td class="py-4 px-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                                ⏰ Em breve
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-3 font-medium">22/10/2025</td>
                                        <td class="py-4 px-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 bg-green-600 rounded-full"></div>
                                                <span class="font-medium">Português</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-3 text-gray-600">14:00 - 17:00</td>
                                        <td class="py-4 px-3 text-gray-600">Campus Beira</td>
                                        <td class="py-4 px-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                                📅 Agendado
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-3 font-medium">25/10/2025</td>
                                        <td class="py-4 px-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 bg-purple-600 rounded-full"></div>
                                                <span class="font-medium">Física</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-3 text-gray-600">09:00 - 11:30</td>
                                        <td class="py-4 px-3 text-gray-600">Campus Beira</td>
                                        <td class="py-4 px-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                                📅 Agendado
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-3 font-medium">27/10/2025</td>
                                        <td class="py-4 px-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 bg-orange-600 rounded-full"></div>
                                                <span class="font-medium">Química</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-3 text-gray-600">14:00 - 16:30</td>
                                        <td class="py-4 px-3 text-gray-600">Campus Beira</td>
                                        <td class="py-4 px-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                                📅 Agendado
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-3 font-medium">30/10/2025</td>
                                        <td class="py-4 px-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                                                <span class="font-medium">Biologia</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-3 text-gray-600">09:00 - 11:30</td>
                                        <td class="py-4 px-3 text-gray-600">Campus Beira</td>
                                        <td class="py-4 px-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                                📅 Agendado
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6 flex items-center justify-between">
                            <p class="text-sm text-gray-600">Mostrando 5 de 12 exames agendados</p>
                            <a href="/guides/indice" class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center gap-1">
                                Ver calendário completo
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Vídeos de Ajuda e Tutoriais -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white">🎥 Vídeos de Ajuda e Dicas</h3>
                        <span class="text-xs bg-white/20 px-3 py-1 rounded-full text-white">Novo!</span>
                    </div>
                    <div class="p-6">
                        <div class="grid md:grid-cols-2 gap-4">
                            <!-- Vídeo 1 -->
                            <div class="bg-gray-50 rounded-xl overflow-hidden hover:shadow-lg transition-all group">
                                <div class="aspect-video bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center relative cursor-pointer">
                                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors"></div>
                                    <div class="relative z-10 text-center">
                                        <div class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                            <svg class="w-8 h-8 text-red-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900 mb-1">Como se Candidatar</h4>
                                    <p class="text-sm text-gray-600 mb-2">Passo a passo completo para candidaturas</p>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            5:30 min
                                        </span>
                                        <span>•</span>
                                        <span>1.2k views</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vídeo 2 -->
                            <div class="bg-gray-50 rounded-xl overflow-hidden hover:shadow-lg transition-all group">
                                <div class="aspect-video bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center relative cursor-pointer">
                                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors"></div>
                                    <div class="relative z-10 text-center">
                                        <div class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                            <svg class="w-8 h-8 text-red-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900 mb-1">Dicas para Vigilantes</h4>
                                    <p class="text-sm text-gray-600 mb-2">O que você precisa saber antes de começar</p>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            8:15 min
                                        </span>
                                        <span>•</span>
                                        <span>856 views</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vídeo 3 -->
                            <div class="bg-gray-50 rounded-xl overflow-hidden hover:shadow-lg transition-all group">
                                <div class="aspect-video bg-gradient-to-br from-orange-500 to-pink-600 flex items-center justify-center relative cursor-pointer">
                                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors"></div>
                                    <div class="relative z-10 text-center">
                                        <div class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                            <svg class="w-8 h-8 text-red-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900 mb-1">Tour pelo Sistema</h4>
                                    <p class="text-sm text-gray-600 mb-2">Navegue pelo portal com facilidade</p>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            6:45 min
                                        </span>
                                        <span>•</span>
                                        <span>2.1k views</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vídeo 4 -->
                            <div class="bg-gray-50 rounded-xl overflow-hidden hover:shadow-lg transition-all group">
                                <div class="aspect-video bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center relative cursor-pointer">
                                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors"></div>
                                    <div class="relative z-10 text-center">
                                        <div class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                            <svg class="w-8 h-8 text-red-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900 mb-1">FAQs Respondidas</h4>
                                    <p class="text-sm text-gray-600 mb-2">Perguntas mais frequentes esclarecidas</p>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            12:30 min
                                        </span>
                                        <span>•</span>
                                        <span>3.4k views</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 text-center">
                            <button class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-md hover:shadow-lg flex items-center gap-2 mx-auto">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                                </svg>
                                Ver Todos os Vídeos
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Próximos Júris (do código original, mantido) -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-3">📍 Próximos Júris por Local</h2>
            <p class="text-gray-600">Consulte os júris agendados para os próximos dias</p>
        </div>
        
        <div class="bg-gradient-to-br from-blue-50 to-purple-50 p-8 rounded-xl shadow-sm">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">Calendário de Júris</h2>
            
            <?php if (empty($juriesByLocation)): ?>
                <p class="text-sm text-primary-50">Sem júris registados.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($juriesByLocation as $location => $disciplines): ?>
                        <!-- Grupo por Local -->
                        <div class="bg-white/10 rounded-lg overflow-hidden">
                            <button 
                                type="button" 
                                class="w-full px-4 py-3 flex items-center justify-between hover:bg-white/10 transition-colors toggle-location"
                                data-location="<?= htmlspecialchars(str_replace(' ', '_', $location)) ?>"
                            >
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="font-semibold text-left"><?= htmlspecialchars($location) ?></span>
                                    <span class="text-xs bg-white/20 px-2 py-1 rounded"><?= count($disciplines) ?> disciplina(s)</span>
                                </div>
                                <svg class="w-5 h-5 transition-transform chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <div class="location-content hidden px-4 pb-3 space-y-2">
                                <?php foreach ($disciplines as $discipline): ?>
                                    <!-- Grupo por Disciplina -->
                                    <div class="bg-white/10 rounded overflow-hidden">
                                        <button 
                                            type="button" 
                                            class="w-full px-3 py-2 flex items-center justify-between hover:bg-white/10 transition-colors toggle-discipline"
                                        >
                                            <div class="text-left">
                                                <p class="font-medium text-sm"><?= htmlspecialchars($discipline['subject']) ?></p>
                                                <p class="text-xs text-primary-100">
                                                    <?= htmlspecialchars(date('d/m/Y', strtotime($discipline['exam_date']))) ?> · 
                                                    <?= htmlspecialchars(substr($discipline['start_time'], 0, 5)) ?> - 
                                                    <?= htmlspecialchars(substr($discipline['end_time'], 0, 5)) ?>
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs bg-white/20 px-2 py-1 rounded"><?= count($discipline['rooms']) ?> sala(s)</span>
                                                <svg class="w-4 h-4 transition-transform chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </button>
                                        
                                        <div class="discipline-content hidden px-3 pb-2">
                                            <div class="space-y-1">
                                                <?php foreach ($discipline['rooms'] as $room): ?>
                                                    <div class="bg-white/10 rounded px-3 py-2 text-xs">
                                                        <span class="font-medium">Sala <?= htmlspecialchars($room['room']) ?></span>
                                                        <span class="text-primary-100"> · <?= htmlspecialchars($room['candidates_quota']) ?> candidatos</span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="vagas" class="relative bg-gray-50 py-20 scroll-mt-20">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Vagas Abertas para Vigilância</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Candidate-se para participar como vigilante nos exames de admissão</p>
        </div>
    
    <?php if ($vacancies): ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($vacancies as $vacancy): ?>
                <?php
                $deadline = new DateTime($vacancy['deadline_at']);
                $now = new DateTime();
                $diff = $now->diff($deadline);
                $daysLeft = $diff->days;
                $isExpiringSoon = $daysLeft <= 3;
                ?>
                <div class="bg-white rounded-lg shadow-lg border-2 <?= $isExpiringSoon ? 'border-amber-400' : 'border-gray-100' ?> p-6 hover:shadow-xl transition-shadow">
                    <?php if ($isExpiringSoon): ?>
                        <div class="mb-3 inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-100 px-3 py-1 rounded-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Termina em <?= $daysLeft ?> dia(s)
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="font-bold text-xl text-gray-800 mb-3"><?= htmlspecialchars($vacancy['title']) ?></h3>
                    <p class="text-sm text-gray-600 leading-relaxed mb-4"><?= htmlspecialchars(mb_strimwidth($vacancy['description'], 0, 150, '...')) ?></p>
                    
                    <div class="mb-5 flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Até <strong class="text-gray-700"><?= htmlspecialchars(date('d/m/Y', strtotime($vacancy['deadline_at']))) ?></strong></span>
                    </div>
                    <div class="mb-5 flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Horário: <strong class="text-gray-700"><?= htmlspecialchars(date('H:i', strtotime($vacancy['deadline_at']))) ?></strong></span>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-100">
                        <?php if (\App\Utils\Auth::check()): ?>
                            <a href="/vacancies/<?= $vacancy['id'] ?>" class="block w-full text-center px-5 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-500 transition-colors shadow-sm">
                                Ver Detalhes
                            </a>
                        <?php else: ?>
                            <a href="/login" class="block w-full text-center px-5 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-500 transition-colors shadow-sm">
                                Entre para Candidatar-se
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-10 text-center">
            <a href="/register" class="inline-flex items-center gap-2 text-primary-600 hover:text-primary-500 font-medium text-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Ainda não tem conta? Registre-se agora
            </a>
        </div>
    <?php else: ?>
        <div class="text-center py-20 bg-white rounded-xl shadow-sm">
            <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">Sem vagas disponíveis no momento</h3>
            <p class="text-gray-600 mb-8 text-lg">Novas oportunidades serão publicadas em breve.</p>
            <a href="/register" class="inline-flex items-center gap-2 px-8 py-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-500 transition-colors shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Cadastre-se para receber notificações
            </a>
        </div>
    <?php endif; ?>
    </div>
</section>

<!-- Recursos e Documentos + FAQs -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-8">
            
            <!-- Recursos e Documentos -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-lg p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    📚 Recursos e Documentos
                </h3>
                
                <div class="space-y-3">
                    <a href="/guides/indice" class="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-all group">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 group-hover:text-blue-700">Guia do Utilizador 2025</div>
                            <div class="text-sm text-gray-600">Documentação completa do sistema</div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    
                    <a href="#" class="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-all group">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 group-hover:text-green-700">Manual do Candidato</div>
                            <div class="text-sm text-gray-600">PDF · 2.5 MB</div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    
                    <a href="#" class="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-all group">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 group-hover:text-purple-700">Regulamento de Exames</div>
                            <div class="text-sm text-gray-600">Normas e procedimentos oficiais</div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    
                    <a href="#" class="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-all group">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 group-hover:text-orange-700">Central de Ajuda (FAQs)</div>
                            <div class="text-sm text-gray-600">Perguntas frequentes</div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-orange-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- FAQs Rápidas -->
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl shadow-lg p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    ❓ Perguntas Frequentes
                </h3>
                
                <div class="space-y-4">
                    <details class="bg-white rounded-lg overflow-hidden group">
                        <summary class="px-4 py-3 font-semibold text-gray-900 cursor-pointer hover:bg-gray-50 transition-colors flex items-center justify-between">
                            <span>Como me candidatar a vigilante?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="px-4 pb-4 text-sm text-gray-600">
                            Primeiro, crie uma conta no portal. Depois, complete seu perfil com todas as informações necessárias (telefone, NUIT, NIB, banco). Por fim, acesse a página de "Vagas" e candidate-se às vagas disponíveis.
                        </div>
                    </details>
                    
                    <details class="bg-white rounded-lg overflow-hidden group">
                        <summary class="px-4 py-3 font-semibold text-gray-900 cursor-pointer hover:bg-gray-50 transition-colors flex items-center justify-between">
                            <span>Quanto tempo leva para aprovação?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="px-4 pb-4 text-sm text-gray-600">
                            A análise das candidaturas geralmente leva de 24 a 48 horas. Você receberá uma notificação por email assim que sua candidatura for avaliada.
                        </div>
                    </details>
                    
                    <details class="bg-white rounded-lg overflow-hidden group">
                        <summary class="px-4 py-3 font-semibold text-gray-900 cursor-pointer hover:bg-gray-50 transition-colors flex items-center justify-between">
                            <span>Quais documentos são necessários?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="px-4 pb-4 text-sm text-gray-600">
                            Você precisa fornecer: número de telefone válido, NUIT, NIB (número de conta bancária) e nome do banco. Todos esses dados podem ser cadastrados diretamente no seu perfil.
                        </div>
                    </details>
                    
                    <details class="bg-white rounded-lg overflow-hidden group">
                        <summary class="px-4 py-3 font-semibold text-gray-900 cursor-pointer hover:bg-gray-50 transition-colors flex items-center justify-between">
                            <span>Posso cancelar minha candidatura?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="px-4 pb-4 text-sm text-gray-600">
                            Sim, você pode cancelar sua candidatura até 48 horas antes da data do exame. Acesse "Minhas Candidaturas" e clique em "Cancelar" na vaga desejada.
                        </div>
                    </details>
                    
                    <details class="bg-white rounded-lg overflow-hidden group">
                        <summary class="px-4 py-3 font-semibold text-gray-900 cursor-pointer hover:bg-gray-50 transition-colors flex items-center justify-between">
                            <span>Como recebo o pagamento?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="px-4 pb-4 text-sm text-gray-600">
                            O pagamento é feito via transferência bancária para o NIB cadastrado no seu perfil, geralmente processado em até 5 dias úteis após a conclusão do júri.
                        </div>
                    </details>
                </div>
                
                <div class="mt-6 text-center">
                    <a href="/guides/parte3" class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-800 font-medium">
                        Ver todas as perguntas
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Final -->
<section class="py-16 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Pronto para Começar?</h2>
        <p class="text-xl text-blue-100 mb-8">Junte-se a centenas de vigilantes e faça parte do processo de exames de admissão da UniLicungo</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="/register" class="px-8 py-4 bg-white text-blue-700 font-bold rounded-lg hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                Criar Conta Agora
            </a>
            <a href="/login" class="px-8 py-4 border-2 border-white text-white font-bold rounded-lg hover:bg-white/10 transition-all">
                Já Tenho Conta
            </a>
        </div>
    </div>
</section>

<script>
(function() {
    // Toggle de locais
    document.querySelectorAll('.toggle-location').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            var content = button.nextElementSibling;
            var chevron = button.querySelector('.chevron');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
        });
    });
    
    // Toggle de disciplinas
    document.querySelectorAll('.toggle-discipline').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            var content = button.nextElementSibling;
            var chevron = button.querySelector('.chevron');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
        });
    });
})();
</script>
