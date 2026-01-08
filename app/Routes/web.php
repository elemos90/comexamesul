<?php

/** @var \App\Routes\Router $router */

$router->get('/', 'HomeController@index');
$router->get('/email/verify', 'AuthController@verifyEmail');

// Guias do Utilizador (públicos)
$router->get('/guides/{slug}', 'GuideController@show');

$router->group(['middleware' => ['GuestMiddleware']], function ($router) {
    $router->get('/login', 'AuthController@showLogin');
    $router->post('/login', 'AuthController@login', ['CsrfMiddleware']);
    $router->get('/register', 'AuthController@showRegister');
    $router->post('/register', 'AuthController@register', ['CsrfMiddleware']);
    $router->get('/password/forgot', 'AuthController@showForgotPassword');
    $router->post('/password/forgot', 'AuthController@forgotPassword', ['CsrfMiddleware']);
    $router->get('/password/reset', 'AuthController@showResetPassword');
    $router->post('/password/reset', 'AuthController@resetPassword', ['CsrfMiddleware']);
    // Stats Routes
    $router->get('/stats', 'StatsController@index', ['AuthMiddleware', 'RoleMiddleware:admin,coordenador']);
    $router->get('/stats/generate', 'StatsController@generate', ['AuthMiddleware', 'RoleMiddleware:admin,coordenador']);
});

$router->post('/logout', 'AuthController@logout', ['AuthMiddleware', 'CsrfMiddleware']);

$router->get('/dashboard', 'DashboardController@index', ['AuthMiddleware']);

$router->get('/profile', 'ProfileController@show', ['AuthMiddleware']);
$router->post('/profile', 'ProfileController@update', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/profile/password', 'ProfileController@updatePassword', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/profile/avatar', 'ProfileController@updateAvatar', ['AuthMiddleware', 'CsrfMiddleware']);

$router->get('/vacancies', 'VacancyController@index', ['AuthMiddleware']);
$router->get('/vacancies/{id}', 'VacancyController@show', ['AuthMiddleware']);
$router->post('/vacancies', 'VacancyController@store', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/vacancies/{id}/update', 'VacancyController@update', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/vacancies/{id}/close', 'VacancyController@close', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/vacancies/{id}/finalize', 'VacancyController@finalize', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/vacancies/{id}/delete', 'VacancyController@delete', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->get('/availability', 'AvailabilityController@show', ['AuthMiddleware', 'RoleMiddleware:vigilante']);
$router->post('/vacancies/{id}/apply', 'AvailabilityController@apply', ['AuthMiddleware', 'RoleMiddleware:vigilante', 'CsrfMiddleware']);
$router->get('/availability/{id}/cancel', 'AvailabilityController@requestCancel', ['AuthMiddleware', 'RoleMiddleware:vigilante']);
$router->post('/availability/{id}/cancel/submit', 'AvailabilityController@submitCancelRequest', ['AuthMiddleware', 'RoleMiddleware:vigilante', 'CsrfMiddleware']);
$router->post('/applications/{id}/cancel-direct', 'AvailabilityController@cancelDirect', ['AuthMiddleware', 'RoleMiddleware:vigilante', 'CsrfMiddleware']);
$router->post('/applications/{id}/reapply', 'AvailabilityController@reapply', ['AuthMiddleware', 'RoleMiddleware:vigilante', 'CsrfMiddleware']);

// DEPRECATED: Sistema simplificado usa apenas candidaturas, não disponibilidade geral
// $router->get('/availability/change/{status}', 'AvailabilityController@requestAvailabilityChange', ['AuthMiddleware', 'RoleMiddleware:vigilante']);
// $router->post('/availability/change/submit', 'AvailabilityController@submitAvailabilityChange', ['AuthMiddleware', 'RoleMiddleware:vigilante', 'CsrfMiddleware']);

$router->get('/applications', 'ApplicationReviewController@index', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/applications/{id}/history', 'ApplicationReviewController@history', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/applications/{id}/approve', 'ApplicationReviewController@approve', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/applications/{id}/reject', 'ApplicationReviewController@reject', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/applications/{id}/revert', 'ApplicationReviewController@revert', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/applications/{id}/toggle-supervisor-eligible', 'ApplicationReviewController@toggleSupervisorEligible', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/applications/approve-all', 'ApplicationReviewController@approveAll', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/applications/reject-all', 'ApplicationReviewController@rejectAll', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/api/applications/stats', 'ApplicationReviewController@getStats', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// Dashboard de Candidaturas (v2.5)
$router->get('/applications/dashboard', 'ApplicationDashboardController@index', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/applications/export', 'ApplicationDashboardController@export', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);


$router->get('/juries', 'JuryController@index', ['AuthMiddleware']);

// Módulo de Pagamentos
$router->get('/payments', 'PaymentController@index', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/payments/rates', 'PaymentController@rates', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/payments/rates', 'PaymentController@storeRate', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/payments/preview/{vacancyId}', 'PaymentController@preview', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/payments/generate/{vacancyId}', 'PaymentController@generate', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/payments/validate/{vacancyId}', 'PaymentController@validate', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/payments/export/{vacancyId}', 'PaymentController@export', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// Meu Mapa de Pagamento (Individual - Vigilante/Supervisor)
$router->get('/payments/my-map', 'PaymentController@myMap', ['AuthMiddleware']);


// Planejamento com Drag-and-Drop (ANTES de /juries/{id})
$router->get('/juries/planning', 'JuryController@planning', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// Calendário Visual de Júris
$router->get('/juries/calendar', 'JuryController@calendar', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro,vigilante']);
$router->get('/api/juries/calendar-events', 'JuryController@calendarEvents', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro,vigilante']);

// Novo: Planejamento por Vaga (Smart Allocation)
$router->get('/juries/planning-by-vacancy', 'JuryController@planningByVacancy', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/juries/vacancy/{id}/manage', 'JuryController@manageVacancyJuries', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/juries/create-for-vacancy', 'JuryController@createJuriesForVacancy', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/vacancy/auto-allocate', 'JuryController@autoAllocateVacancy', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/vacancy/clear-allocations', 'JuryController@clearVacancyAllocations', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/juries/vacancy/{id}/stats', 'JuryController@getVacancyStats', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/juries/{id}/eligible-vigilantes', 'JuryController@getEligibleForJury', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/juries/vacancy/{id}/approved-candidates', 'JuryController@getVacancyApprovedCandidates', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// API para supervisores
$router->get('/api/users/supervisors', 'JuryController@getEligibleSupervisors', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/juries/bulk-assign-supervisor', 'JuryController@bulkAssignSupervisor', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);

// API para alocação de supervisores por blocos (v2.7)
$router->post('/juries/supervisors/auto-allocate', 'JuryAllocationController@autoAllocateSupervisors', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/{id}/supervisor/single', 'JuryAllocationController@setSupervisorSingle', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/{id}/supervisor/remove', 'JuryAllocationController@removeSupervisorFromJury', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/juries/supervisors/stats/{vacancyId}', 'JuryAllocationController@getSupervisorStats', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/juries/supervisors/load/{supervisorId}', 'JuryAllocationController@getSupervisorLoad', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// API para alocação de vigilantes (v2.8)
$router->get('/juries/vigilantes/status/{id}', 'JuryAllocationController@getVigilanteStatus', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/juries/vigilantes/auto-distribute', 'JuryAllocationController@autoDistributeVigilantes', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);

$router->get('/juries/{id}', 'JuryController@show', ['AuthMiddleware']);
$router->post('/juries', 'JuryController@store', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/create-batch', 'JuryController@createBatch', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/create-location-batch', 'JuryController@createLocationBatch', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/update-batch', 'JuryController@updateBatch', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);

// Nova API: Criação em Lote de Júris (múltiplas salas para mesmo exame)
$router->post('/juries/create-bulk', 'JuryController@createBulk', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);

// API para Edição e Remoção de Júris (NOVOS - Gestão de Alocações Melhorada)
$router->get('/juries/{id}/details', 'JuryController@getDetails', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/juries/{id}/update', 'JuryController@updateJury', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/{id}/delete', 'JuryController@deleteJury', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/vacancy/{vacancy_id}/update-discipline', 'JuryController@updateDiscipline', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);

// Rotas de gestão de júris
$router->post('/juries/{id}/update-quick', 'JuryController@updateQuick', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/{id}/assign', 'JuryController@assign', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/{id}/unassign', 'JuryController@unassign', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/{id}/set-supervisor', 'JuryController@setSupervisor', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/juries/sync-room-names', 'JuryController@syncRoomNames', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);

// API de Alocação Inteligente
$router->post('/api/allocation/can-assign', 'JuryController@canAssign', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/api/allocation/auto-allocate-jury', 'JuryController@autoAllocateJury', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/api/allocation/auto-allocate-discipline', 'JuryController@autoAllocateDiscipline', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/api/allocation/swap', 'JuryController@swapVigilantes', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/api/allocation/stats', 'JuryController@getAllocationStats', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/metrics', 'JuryController@getMetrics', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/jury-slots/{id}', 'JuryController@getJurySlots', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/eligible-vigilantes/{id}', 'JuryController@getEligibleVigilantes', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/eligible-supervisors/{id}', 'JuryController@getEligibleSupervisors', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/available-vigilantes', 'JuryController@getAvailableVigilantes', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/available-supervisors', 'JuryController@getAvailableSupervisors', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// API de Sugestões Inteligentes "Top-3"
$router->get('/api/suggest-top3', 'SuggestController@top3', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/api/suggest-apply', 'SuggestController@apply', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);

$router->get('/juries/{id}/report', 'ReportController@show', ['AuthMiddleware']);
$router->post('/juries/{id}/report', 'ReportController@store', ['AuthMiddleware', 'CsrfMiddleware']);
$router->get('/exports/vigilantes.pdf', 'ExportController@vigilantesPdf', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/exports/supervisores.xls', 'ExportController@supervisoresXls', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/exports/supervisores.pdf', 'ExportController@supervisoresPdf', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/exports/vigias.xls', 'ExportController@vigiasXls', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/exports/vigias.pdf', 'ExportController@vigiasPdf', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// Locations: Visualização e Dashboard
$router->get('/locations', 'LocationController@index', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/locations/dashboard', 'LocationController@dashboard', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// Master Data: Gestão de Disciplinas, Locais e Salas
$router->get('/master-data/disciplines', 'MasterDataController@disciplines', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/master-data/disciplines', 'MasterDataController@storeDiscipline', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/master-data/disciplines/{id}/update', 'MasterDataController@updateDiscipline', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/master-data/disciplines/{id}/toggle', 'MasterDataController@toggleDiscipline', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/master-data/disciplines/{id}/delete', 'MasterDataController@deleteDiscipline', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);

$router->get('/master-data/locations', 'MasterDataController@locations', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/master-data/locations', 'MasterDataController@storeLocation', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/master-data/locations/{id}/update', 'MasterDataController@updateLocation', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/master-data/locations/{id}/toggle', 'MasterDataController@toggleLocation', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/master-data/locations/{id}/delete', 'MasterDataController@deleteLocation', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);

$router->get('/master-data/rooms', 'MasterDataController@rooms', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/master-data/rooms', 'MasterDataController@storeRoom', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/master-data/rooms/{id}/update', 'MasterDataController@updateRoom', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/master-data/rooms/{id}/toggle', 'MasterDataController@toggleRoom', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/master-data/rooms/{id}/delete', 'MasterDataController@deleteRoom', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);

// API: Buscar salas por local
$router->get('/api/locations/{id}/rooms', 'MasterDataController@getRoomsByLocation', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// API: Dados mestre para criação de júris
$router->get('/api/master-data/locations-rooms', 'JuryController@getMasterDataLocationsRooms', ['AuthMiddleware']);
$router->get('/api/vacancies/{id}/subjects', 'JuryController@getVacancySubjects', ['AuthMiddleware']);

// Instalação de Dados Mestres (sem autenticação para facilitar setup inicial)
$router->get('/install/master-data', 'InstallController@masterData');
$router->post('/install/master-data/execute', 'InstallController@executeMasterData');

// Locations: Templates
$router->get('/locations/templates', 'LocationController@templates', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/locations/templates', 'LocationController@storeTemplate', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/locations/templates/{id}/load', 'LocationController@loadTemplate', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/locations/templates/{id}/toggle', 'LocationController@toggleTemplate', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/locations/templates/{id}/delete', 'LocationController@deleteTemplate', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);

// Locations: Import/Export
$router->get('/locations/import', 'LocationController@showImport', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/locations/import', 'LocationController@processImport', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/locations/export/template', 'LocationController@exportTemplate', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// Settings: Logo da Instituição
$router->post('/settings/upload-logo', 'SettingsController@uploadLogo', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);