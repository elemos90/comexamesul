-- Migração v2.6: Adicionar campo 'Universidade de Origem'
-- Data: 11/10/2025

-- Adicionar coluna origin_university na tabela users
ALTER TABLE users
ADD COLUMN origin_university VARCHAR(150) NULL AFTER gender;

-- Comentário: Universidade de Origem (onde se formou)
-- Diferente de 'university' que representa a universidade atual/afiliação
