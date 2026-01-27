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

    // Nova Recuperação de Conta (Wizard)
    $router->get('/recover', 'AuthController@showRecoverStep1'); // Username
    $router->post('/recover/check', 'AuthController@checkUsername', ['CsrfMiddleware']);
    $router->get('/recover/method', 'AuthController@showRecoverStep2'); // Select Method
    $router->post('/recover/method', 'AuthController@selectMethod', ['CsrfMiddleware']);
    $router->get('/recover/verify', 'AuthController@showRecoverStep3'); // Input Credential
    $router->post('/recover/verify', 'AuthController@verifyCredential', ['CsrfMiddleware']);
    $router->get('/recover/reset', 'AuthController@showRecoverStep4'); // Set Password
    $router->post('/recover/reset', 'AuthController@finalizeRecovery', ['CsrfMiddleware']);

    // Fallback (antigo forgot-password, agora dentro do fluxo)
    $router->get('/recover/fallback', 'AuthController@showFallback');
    $router->post('/recover/fallback', 'AuthController@processFallback', ['CsrfMiddleware']);

    $router->get('/password/reset', 'AuthController@showResetPassword');
    $router->post('/password/reset', 'AuthController@resetPassword', ['CsrfMiddleware']);
    // Stats Routes
    $router->get('/stats', 'StatsController@index', ['AuthMiddleware', 'RoleMiddleware:admin,coordenador']);
    $router->get('/stats/generate', 'StatsController@generate', ['AuthMiddleware', 'RoleMiddleware:admin,coordenador']);
});

$router->post('/logout', 'AuthController@logout', ['AuthMiddleware', 'CsrfMiddleware']);

// Force password change (requires auth but NOT profile complete)
$router->get('/auth/force-password-change', 'AuthController@showForcePasswordChange', ['AuthMiddleware']);
$router->post('/auth/force-password-change', 'AuthController@forcePasswordChange', ['AuthMiddleware', 'CsrfMiddleware']);

$router->get('/dashboard', 'DashboardController@index', ['AuthMiddleware']);

// Profile Wizard (requires auth but NOT profile complete)
$router->get('/profile/wizard', 'ProfileWizardController@show', ['AuthMiddleware']);
$router->post('/profile/wizard', 'ProfileWizardController@save', ['AuthMiddleware', 'CsrfMiddleware']);

// Regular profile (requires auth)
$router->get('/profile', 'ProfileController@show', ['AuthMiddleware']);
$router->post('/profile', 'ProfileController@update', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/profile/password', 'ProfileController@updatePassword', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/profile/avatar', 'ProfileController@updateAvatar', ['AuthMiddleware', 'CsrfMiddleware']);

// Recuperação de Conta (Configuração)
$router->get('/profile/recovery', 'ProfileController@showRecovery', ['AuthMiddleware']);
$router->post('/profile/recovery/keyword', 'ProfileController@updateRecoveryKeyword', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/profile/recovery/pin', 'ProfileController@updateRecoveryPin', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/profile/recovery/questions', 'ProfileController@updateRecoveryQuestions', ['AuthMiddleware', 'CsrfMiddleware']);


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
$router->post('/payments/rates', 'PaymentController@storeRate', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->get('/payments/preview/{vacancyId}', 'PaymentController@preview', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/payments/generate/{vacancyId}', 'PaymentController@generate', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/payments/validate/{vacancyId}', 'PaymentController@validate', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->get('/payments/export/{vacancyId}', 'PaymentController@export', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// Meu Mapa de Pagamento (Individual - Vigilante/Supervisor)
$router->get('/payments/my-map', 'PaymentController@myMap', ['AuthMiddleware']);

// ==== NOTIFICAÇÕES ====
// User notifications
$router->get('/notifications', 'NotificationController@index', ['AuthMiddleware']);
$router->get('/notifications/unread-count', 'NotificationController@getUnreadCount', ['AuthMiddleware']);
$router->post('/notifications/{id}/read', 'NotificationController@markAsRead', ['AuthMiddleware']);

// Wizard (Coordenador only)
$router->get('/notifications/create', 'NotificationController@create', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/notifications/wizard/step2', 'NotificationController@wizardStep2', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->get('/notifications/create/step2', 'NotificationController@createStep2', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/notifications/wizard/step3', 'NotificationController@wizardStep3', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->get('/notifications/create/step3', 'NotificationController@createStep3', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/notifications/wizard/step4', 'NotificationController@wizardStep4', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->get('/notifications/create/step4', 'NotificationController@createStep4', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/notifications/wizard/step5', 'NotificationController@wizardStep5', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->get('/notifications/create/step5', 'NotificationController@createStep5', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/notifications/send', 'NotificationController@send', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);

// History (Coordenador only)
$router->get('/notifications/history', 'NotificationController@history', ['AuthMiddleware', 'RoleMiddleware:coordenador']);


// Planejamento com Drag-and-Drop (ANTES de /juries/{id})
$router->get('/juries/planning', 'JuryController@planning', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// Export Excel
$router->get('/juries/export/excel', 'JuryExportController@exportExcel', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro,supervisor,vigilante']);

// Calendário Visual de Júris (JuryCalendarController)
$router->get('/juries/calendar', 'JuryCalendarController@calendar', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro,vigilante']);
$router->get('/api/juries/calendar-events', 'JuryCalendarController@calendarEvents', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro,vigilante']);

// Planejamento por Vaga - Wizard (JuryWizardController)
$router->get('/juries/planning-by-vacancy', 'JuryWizardController@planningByVacancy', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/juries/vacancy/{id}/manage', 'JuryWizardController@manageVacancyJuries', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/juries/create-for-vacancy', 'JuryWizardController@createJuriesForVacancy', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/vacancy/auto-allocate', 'JuryWizardController@autoAllocateVacancy', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/vacancy/clear-allocations', 'JuryWizardController@clearVacancyAllocations', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/juries/vacancy/{id}/stats', 'JuryWizardController@getVacancyStats', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/juries/vacancy/{id}/validate', 'JuryWizardController@validateVacancyPlanning', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/juries/{id}/eligible-vigilantes', 'JuryWizardController@getEligibleForJury', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/juries/vacancy/{id}/approved-candidates', 'JuryWizardController@getVacancyApprovedCandidates', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// API para supervisores
$router->get('/api/users/supervisors', 'JuryController@getEligibleSupervisors', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// API para o Wizard de Criação de Júris
$router->get('/api/vigilantes/eligible', 'JuryResourceController@getEligibleVigilantesForWizard', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/supervisors/eligible', 'JuryResourceController@getEligibleSupervisorsForWizard', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/juries/bulk-assign-supervisor', 'JuryBulkController@bulkAssignSupervisor', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);

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
// Operações em Lote (JuryBulkController)
$router->post('/juries/create-batch', 'JuryBulkController@createBatch', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/create-location-batch', 'JuryBulkController@createLocationBatch', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/update-batch', 'JuryBulkController@updateBatch', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/create-bulk', 'JuryBulkController@createBulk', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);

// API para Edição e Remoção de Júris (NOVOS - Gestão de Alocações Melhorada)
$router->get('/juries/{id}/details', 'JuryController@getDetails', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/juries/{id}/update', 'JuryController@updateJury', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/{id}/delete', 'JuryController@deleteJury', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/vacancy/{vacancy_id}/update-discipline', 'JuryBulkController@updateDiscipline', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);

// Rotas de gestão de júris
$router->post('/juries/{id}/update-quick', 'JuryController@updateQuick', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/{id}/assign', 'JuryAllocationController@assign', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/{id}/unassign', 'JuryAllocationController@unassign', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/juries/{id}/set-supervisor', 'JuryAllocationController@setSupervisor', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/juries/sync-room-names', 'JuryBulkController@syncRoomNames', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);

// API de Alocação Inteligente
$router->post('/api/allocation/can-assign', 'JuryAllocationController@canAssign', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/api/allocation/auto-allocate-jury', 'JuryAllocationController@autoAllocateJury', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/api/allocation/auto-allocate-discipline', 'JuryAllocationController@autoAllocateDiscipline', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->post('/api/allocation/swap', 'JuryAllocationController@swapVigilantes', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro', 'CsrfMiddleware']);
$router->get('/api/allocation/stats', 'JuryMetricsController@getAllocationStats', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/metrics', 'JuryMetricsController@getMetrics', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/jury-slots/{id}', 'JuryResourceController@getJurySlots', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/eligible-vigilantes/{id}', 'JuryResourceController@getEligibleVigilantes', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/eligible-supervisors/{id}', 'JuryResourceController@getEligibleSupervisors', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/available-vigilantes', 'JuryResourceController@getAvailableVigilantes', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/api/allocation/available-supervisors', 'JuryResourceController@getAvailableSupervisors', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

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

// Feature Flags (Admin/Coordenador only)
$router->get('/admin/features', 'FeatureFlagController@index', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/admin/features/toggle', 'FeatureFlagController@toggle', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->get('/api/features', 'FeatureFlagController@getAll', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/admin/features/reset', 'FeatureFlagController@reset', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);

// ============================================
// Relatório Consolidado de Exames
// ============================================
$router->get('/reports/consolidated', 'ConsolidatedReportController@index', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->post('/reports/consolidated/generate', 'ConsolidatedReportController@generate', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/reports/consolidated/export/pdf', 'ConsolidatedReportController@exportPdf', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/reports/consolidated/export/excel', 'ConsolidatedReportController@exportExcel', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);
$router->get('/reports/consolidated/export/csv', 'ConsolidatedReportController@exportCsv', ['AuthMiddleware', 'RoleMiddleware:coordenador,membro']);

// ============================================
// ADMINISTRAÇÃO - Gestão de Utilizadores
// ============================================
$router->get('/admin/users', 'UserController@index', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->get('/admin/users/create', 'UserController@create', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/admin/users', 'UserController@store', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->get('/admin/users/{id}/edit', 'UserController@edit', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/admin/users/{id}', 'UserController@update', ['AuthMiddleware', 'RoleMiddleware:coordenador', 'CsrfMiddleware']);
$router->post('/admin/users/{id}/toggle-status', 'UserController@toggleStatus', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/admin/users/{id}/promote-supervisor', 'UserController@promoteToSupervisor', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/admin/users/{id}/promote-member', 'UserController@promoteToCommitteeMember', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->post('/admin/users/{id}/roles', 'UserController@updateRoles', ['AuthMiddleware', 'RoleMiddleware:coordenador']);
$router->get('/admin/users/{id}/audit', 'UserController@getAuditLog', ['AuthMiddleware', 'RoleMiddleware:coordenador']);

// Wizard de Reset de Senha (Admin)
$router->get('/admin/password-reset/{id}', 'UserController@showResolvePasswordReset', ['AuthMiddleware', 'RoleMiddleware:coordenador,admin']);
$router->post('/admin/password-reset/{id}', 'UserController@resolvePasswordReset', ['AuthMiddleware', 'RoleMiddleware:coordenador,admin', 'CsrfMiddleware']);