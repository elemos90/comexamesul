<?php
/**
 * Calendário Visual de Júris
 * 
 * Visualização mensal/semanal de todos os júris com FullCalendar
 */

$title = 'Calendário de Júris';
$breadcrumbs = [
    ['label' => 'Júris', 'url' => url('/juries')],
    ['label' => 'Calendário']
];
$helpPage = 'juries-calendar';

// Dados passados pelo controller
$user = $user ?? null;
$vacancyId = $vacancyId ?? null;
$allVacancies = $allVacancies ?? [];
?>

<!-- FullCalendar CSS via CDN -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">

<style>
    .fc {
        --fc-border-color: #e5e7eb;
        --fc-button-bg-color: #4f46e5;
        --fc-button-border-color: #4f46e5;
        --fc-button-hover-bg-color: #4338ca;
        --fc-button-active-bg-color: #3730a3;
        --fc-event-bg-color: #4f46e5;
        --fc-today-bg-color: rgba(79, 70, 229, 0.1);
    }

    .fc-event {
        cursor: pointer;
        padding: 2px 4px;
        font-size: 0.75rem;
        border-radius: 4px;
    }

    .fc-event:hover {
        opacity: 0.9;
        transform: scale(1.02);
    }

    .fc-daygrid-event {
        white-space: normal !important;
    }

    /* Cores por status de alocação */
    .event-complete {
        background-color: #10b981 !important;
        border-color: #059669 !important;
    }

    .event-partial {
        background-color: #f59e0b !important;
        border-color: #d97706 !important;
    }

    .event-empty {
        background-color: #ef4444 !important;
        border-color: #dc2626 !important;
    }

    .event-no-supervisor {
        background-color: #8b5cf6 !important;
        border-color: #7c3aed !important;
    }

    /* Loading overlay */
    #calendar-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
    }

    /* Legenda */
    .calendar-legend {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 0.75rem;
        background: #f9fafb;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .legend-color {
        width: 1rem;
        height: 1rem;
        border-radius: 4px;
    }
</style>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">📅 Calendário de Júris</h1>
        <p class="text-gray-600">Visualização mensal e semanal dos exames agendados</p>
    </div>

    <div class="flex gap-2">
        <!-- Filtro de Vaga -->
        <select id="vacancy-filter" class="px-3 py-2 border rounded-lg bg-white text-sm">
            <option value="">Todas as Vagas</option>
            <?php foreach ($allVacancies as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $vacancyId == $v['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <a href="<?= url('/juries/planning') ?>"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
            ← Voltar ao Planejamento
        </a>
    </div>
</div>

<!-- Legenda -->
<div class="calendar-legend">
    <div class="legend-item">
        <div class="legend-color" style="background: #10b981;"></div>
        <span>Completo</span>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background: #f59e0b;"></div>
        <span>Parcial</span>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background: #ef4444;"></div>
        <span>Sem Vigilantes</span>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background: #8b5cf6;"></div>
        <span>Sem Supervisor</span>
    </div>
</div>

<!-- Calendário -->
<div class="bg-white rounded-xl shadow-sm p-4 relative" style="min-height: 600px;">
    <div id="calendar-loading" style="display: none;">
        <div class="flex flex-col items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mb-4"></div>
            <span class="text-gray-600">Carregando eventos...</span>
        </div>
    </div>
    <div id="calendar"></div>
</div>

<!-- Modal de Detalhes do Evento -->
<div id="event-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 id="modal-title" class="text-xl font-bold text-gray-800"></h3>
                <button onclick="closeEventModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="modal-content" class="space-y-4">
                <!-- Conteúdo dinâmico -->
            </div>
            <div class="mt-6 flex gap-2">
                <a id="modal-link-planning" href="#"
                    class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-center hover:bg-indigo-700">
                    Ver no Planejamento
                </a>
                <button onclick="closeEventModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar JS via CDN -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/pt-br.global.min.js"></script>

<script>
    const calendarBaseUrl = '<?= url('') ?>';
    const calendarCsrfToken = '<?= \App\Utils\Csrf::token() ?>';

    let calendar;

    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const loading = document.getElementById('calendar-loading');
        
        if (!calendarEl) return;

        calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'pt-br',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: {
                today: 'Hoje',
                month: 'Mês',
                week: 'Semana',
                list: 'Lista'
            },
            height: 'auto',
            navLinks: true,
            editable: false,
            dayMaxEvents: 3,
            moreLinkText: 'mais',

            loading: function (isLoading) {
                if (loading) loading.style.display = isLoading ? 'flex' : 'none';
            },

            events: function (info, successCallback, failureCallback) {
                const vacancyFilter = document.getElementById('vacancy-filter');
                const vacancyId = vacancyFilter ? vacancyFilter.value : '';
                const url = `${calendarBaseUrl}/api/juries/calendar-events?start=${info.startStr}&end=${info.endStr}${vacancyId ? '&vacancy_id=' + vacancyId : ''}`;

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            successCallback(data.events);
                        } else {
                            failureCallback(data.message || 'Erro ao carregar eventos');
                        }
                    })
                    .catch(error => {
                        failureCallback(error);
                    });
            },

            eventClick: function (info) {
                showEventModal(info.event);
            },

            eventDidMount: function (info) {
                info.el.title = `${info.event.title}\n${info.event.extendedProps.location || ''}\n${info.event.extendedProps.time || ''}`;
            }
        });

        calendar.render();

        // Filtro de vaga
        const vacancyFilter = document.getElementById('vacancy-filter');
        if (vacancyFilter) {
            vacancyFilter.addEventListener('change', function () {
                calendar.refetchEvents();
            });
        }
    });

    function showEventModal(event) {
        const modal = document.getElementById('event-modal');
        const title = document.getElementById('modal-title');
        const content = document.getElementById('modal-content');
        const planningLink = document.getElementById('modal-link-planning');

        title.textContent = event.title;

        const props = event.extendedProps;
        content.innerHTML = `
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">📅 Data:</span>
                <p class="font-medium">${props.date || '-'}</p>
            </div>
            <div>
                <span class="text-gray-500">⏰ Horário:</span>
                <p class="font-medium">${props.time || '-'}</p>
            </div>
            <div>
                <span class="text-gray-500">📍 Local:</span>
                <p class="font-medium">${props.location || '-'}</p>
            </div>
            <div>
                <span class="text-gray-500">🚪 Sala:</span>
                <p class="font-medium">${props.room || '-'}</p>
            </div>
            <div>
                <span class="text-gray-500">👥 Vigilantes:</span>
                <p class="font-medium">${props.vigilantes_count || 0} / ${props.vigilantes_required || 2}</p>
            </div>
            <div>
                <span class="text-gray-500">👔 Supervisor:</span>
                <p class="font-medium">${props.supervisor || 'Não definido'}</p>
            </div>
        </div>
        
        ${props.vigilantes_list && props.vigilantes_list.length > 0 ? `
            <div class="mt-4 pt-4 border-t">
                <span class="text-gray-500 text-sm">Vigilantes Alocados:</span>
                <ul class="mt-2 space-y-1">
                    ${props.vigilantes_list.map(v => `<li class="text-sm">• ${v}</li>`).join('')}
                </ul>
            </div>
        ` : ''}
    `;

        planningLink.href = `${calendarBaseUrl}/juries/planning?vacancy_id=${props.vacancy_id || ''}`;

        modal.classList.remove('hidden');
    }

    function closeEventModal() {
        document.getElementById('event-modal').classList.add('hidden');
    }

    // Fechar modal ao clicar fora
    document.getElementById('event-modal')?.addEventListener('click', function (e) {
        if (e.target === this) {
            closeEventModal();
        }
    });
</script>