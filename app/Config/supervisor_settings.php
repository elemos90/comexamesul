<?php
/**
 * Configurações do Sistema de Alocação de Supervisores e Vigilantes
 * 
 * Este arquivo define os parâmetros para a distribuição equilibrada
 * de supervisores e vigilantes pelos júris/salas de exame.
 */

return [
    // ========================================
    // REGRAS DE SUPERVISORES
    // ========================================

    /**
     * Número máximo de júris que um supervisor pode supervisionar
     * simultaneamente num mesmo bloco horário.
     * 
     * Quando uma disciplina tem mais júris do que este limite,
     * o sistema distribuirá automaticamente entre múltiplos supervisores.
     * 
     * Exemplos com MAX = 10:
     * - 20 júris → 2 supervisores (10 + 10)
     * - 21 júris → 3 supervisores (7 + 7 + 7)
     * - 18 júris → 2 supervisores (9 + 9)
     */
    'max_juries_per_supervisor' => 10,

    /**
     * Número mínimo de supervisores por disciplina/bloco horário.
     * Mesmo que haja poucos júris, este é o mínimo garantido.
     */
    'min_supervisors_per_discipline' => 1,

    // ========================================
    // REGRAS DE VIGILANTES (OBRIGATÓRIAS)
    // ========================================

    /**
     * Número de candidatos por vigilante.
     * 
     * Fórmula: nr_vigilantes_minimo = ceil(nr_candidatos / candidates_per_vigilante)
     * 
     * Exemplos com 30 candidatos por vigilante:
     * - 30 candidatos → 1 vigilante
     * - 31 candidatos → 2 vigilantes
     * - 60 candidatos → 2 vigilantes
     * - 61 candidatos → 3 vigilantes
     * - 90 candidatos → 3 vigilantes
     */
    'candidates_per_vigilante' => 30,

    /**
     * Número mínimo de vigilantes por júri/sala.
     * Mesmo com poucos candidatos, este é o mínimo garantido.
     */
    'min_vigilantes_per_jury' => 1,

    /**
     * Número máximo de vigilantes por júri/sala.
     * Limite de segurança para evitar sobre-alocação.
     */
    'max_vigilantes_per_jury' => 5,

    // ========================================
    // COMPORTAMENTO DO SISTEMA
    // ========================================

    /**
     * Permitir ajustes manuais após auto-alocação.
     * Se true, coordenadores podem arrastar júris entre supervisores.
     */
    'allow_manual_override' => true,

    /**
     * Ordenar supervisores/vigilantes elegíveis por carga de trabalho.
     * Prioriza quem tem menos alocações para equilibrar
     * a distribuição global ao longo de toda a vaga.
     */
    'balance_by_global_load' => true,

    /**
     * Registar actividade de alocação automática para auditoria.
     */
    'log_auto_allocation' => true,

    // ========================================
    // VALIDAÇÕES (NÃO NEGOCIÁVEIS)
    // ========================================

    /**
     * Impedir confirmar júri com vigilantes abaixo do mínimo.
     * Se true, sistema não permite fechar alocação incompleta.
     */
    'enforce_min_vigilantes' => true,

    /**
     * Impedir júri sem supervisor atribuído.
     * Se true, sistema alertará antes de confirmar.
     */
    'require_supervisor' => true,

    /**
     * Alertar quando supervisor excede o limite recomendado.
     * Se true, mostra aviso visual na interface.
     */
    'warn_supervisor_overload' => true,
];
