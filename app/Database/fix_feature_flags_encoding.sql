-- Fix encoding for feature_flags table
UPDATE feature_flags SET 
    feature_name = 'Alocação Manual',
    feature_description = 'Alocar vigilantes/supervisores manualmente'
WHERE feature_code = 'commission.manual_allocation';

UPDATE feature_flags SET 
    feature_name = 'Auto-distribuir Supervisores',
    feature_description = 'Distribuição automática de supervisores'
WHERE feature_code = 'commission.auto_supervisors';

UPDATE feature_flags SET 
    feature_name = 'Auto-distribuir Vigilantes',
    feature_description = 'Distribuição automática de vigilantes'
WHERE feature_code = 'commission.auto_vigilantes';

UPDATE feature_flags SET 
    feature_name = 'Exportar Relatórios',
    feature_description = 'Exportar dados em PDF/Excel'
WHERE feature_code = 'commission.export_reports';

UPDATE feature_flags SET 
    feature_name = 'Criar Júris',
    feature_description = 'Permite criar novos júris para vagas'
WHERE feature_code = 'commission.create_jury';

UPDATE feature_flags SET 
    feature_name = 'Editar Júris',
    feature_description = 'Permite modificar júris existentes'
WHERE feature_code = 'commission.edit_jury';

UPDATE feature_flags SET 
    feature_name = 'Eliminar Júris',
    feature_description = 'Permite eliminar júris'
WHERE feature_code = 'commission.delete_jury';

UPDATE feature_flags SET 
    feature_name = 'Submeter Relatório Pós-Exame',
    feature_description = 'Submeter relatórios após exames'
WHERE feature_code = 'commission.post_exam';

UPDATE feature_flags SET 
    feature_name = 'Ver Mapa de Pagamentos',
    feature_description = 'Acesso ao mapa geral de pagamentos'
WHERE feature_code = 'commission.view_payments';

-- Vigilantes
UPDATE feature_flags SET 
    feature_name = 'Ver Júris Alocados',
    feature_description = 'Ver os júris onde está alocado'
WHERE feature_code = 'guard.view_juries';

UPDATE feature_flags SET 
    feature_name = 'Ver Calendário',
    feature_description = 'Acesso ao calendário de exames'
WHERE feature_code = 'guard.view_calendar';

UPDATE feature_flags SET 
    feature_name = 'Submeter Relatório Pós-Exame',
    feature_description = 'Submeter relatórios após exames'
WHERE feature_code = 'guard.post_exam';

UPDATE feature_flags SET 
    feature_name = 'Editar Relatório',
    feature_description = 'Editar relatório antes da validação'
WHERE feature_code = 'guard.edit_post_exam';

UPDATE feature_flags SET 
    feature_name = 'Ver Meu Pagamento',
    feature_description = 'Ver mapa individual de pagamento'
WHERE feature_code = 'guard.view_own_payment';

UPDATE feature_flags SET 
    feature_name = 'Exportar PDF de Pagamento',
    feature_description = 'Exportar comprovativo individual'
WHERE feature_code = 'guard.export_payment_pdf';

SELECT 'Feature flags encoding fixed!' as status;
