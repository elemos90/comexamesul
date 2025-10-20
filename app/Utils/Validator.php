<?php

namespace App\Utils;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleString) {
            $rulesList = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            foreach ($rulesList as $rule) {
                $rule = trim($rule);
                if ($rule === 'required') {
                    if ($value === null || $value === '') {
                        $this->addError($field, 'Este campo é obrigatório.');
                    }
                } elseif ($rule === 'email') {
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, 'Formato de email inválido.');
                    }
                } elseif (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if ($value !== null && mb_strlen((string) $value) > $max) {
                        $this->addError($field, "Máximo de {$max} caracteres excedido.");
                    }
                } elseif (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if ($value !== null && mb_strlen((string) $value) < $min) {
                        $this->addError($field, "Mínimo de {$min} caracteres não atingido.");
                    }
                } elseif (str_starts_with($rule, 'in:')) {
                    $options = explode(',', substr($rule, 3));
                    if ($value !== null && !in_array($value, $options, true)) {
                        $this->addError($field, 'Opção inválida.');
                    }
                } elseif ($rule === 'numeric') {
                    if ($value !== null && !is_numeric($value)) {
                        $this->addError($field, 'Deve ser numérico.');
                    }
                } elseif ($rule === 'nib') {
                    // NIB moçambicano: 23 dígitos
                    if ($value !== null && !preg_match('/^[0-9]{23}$/', $value)) {
                        $this->addError($field, 'NIB deve ter exatamente 23 dígitos.');
                    }
                } elseif ($rule === 'nuit') {
                    // NUIT moçambicano: 9 dígitos
                    if ($value !== null && !preg_match('/^[0-9]{9}$/', $value)) {
                        $this->addError($field, 'NUIT deve ter exatamente 9 dígitos.');
                    }
                } elseif ($rule === 'phone_mz') {
                    // Telefone moçambicano: +258 8X XXX XXXX (82-87)
                    // Aceita 9 a 11 dígitos após +258
                    // Exemplos: +258841234567, +258 84 123 4567, +258 84 123 4567890
                    if ($value !== null) {
                        $cleaned = preg_replace('/[\s\-]/', '', $value);
                        if (!preg_match('/^\+258[8][2-7]\d{7,9}$/', $cleaned)) {
                            $this->addError($field, 'Telefone inválido. Formato: +258 8X XXX XXXX (9-11 dígitos, 82-87)');
                        }
                    }
                } elseif ($rule === 'date') {
                    if ($value && !strtotime($value)) {
                        $this->addError($field, 'Data inválida.');
                    }
                } elseif ($rule === 'time') {
                    if ($value && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
                        $this->addError($field, 'Hora inválida.');
                    }
                }
            }
        }

        return empty($this->errors);
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
