-- SCRIPT DE RESET DE DADOS OPERACIONAIS
-- Este script remove candidatos, vagas, júris e alocações.
-- MANTÉM: Usuários, Locais, Salas e Disciplinas.

USE comexamesul;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tabelas dependentes (Filhas) - Limpar primeiro!
DELETE FROM application_status_history;
ALTER TABLE application_status_history AUTO_INCREMENT = 1;

DELETE FROM jury_vigilantes;
ALTER TABLE jury_vigilantes AUTO_INCREMENT = 1;

-- 2. Tabelas principais (Pais)
DELETE FROM vacancy_applications;
ALTER TABLE vacancy_applications AUTO_INCREMENT = 1;

DELETE FROM juries;
ALTER TABLE juries AUTO_INCREMENT = 1;

DELETE FROM exam_vacancies;
ALTER TABLE exam_vacancies AUTO_INCREMENT = 1;

DELETE FROM notifications;
ALTER TABLE notifications AUTO_INCREMENT = 1; 

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'DADOS OPERACIONAIS REMOVIDOS COM SUCESSO' as status;
