<?php

/**
 * Exemplo de teste unitário para o Validator
 * 
 * Para executar:
 * 1. composer require --dev phpunit/phpunit
 * 2. ./vendor/bin/phpunit tests/ExampleValidatorTest.php
 */

use PHPUnit\Framework\TestCase;
use App\Utils\Validator;

class ExampleValidatorTest extends TestCase
{
    /**
     * Testa validação de NUIT (Número Único de Identificação Tributária)
     */
    public function test_validates_nuit_correctly(): void
    {
        // NUIT válido (9 dígitos)
        $this->assertTrue(Validator::nuit('100123456'));
        $this->assertTrue(Validator::nuit('999888777'));
        
        // NUIT inválido
        $this->assertFalse(Validator::nuit('abc'));
        $this->assertFalse(Validator::nuit('123')); // muito curto
        $this->assertFalse(Validator::nuit(''));
        $this->assertFalse(Validator::nuit(null));
    }
    
    /**
     * Testa validação de email
     */
    public function test_validates_email(): void
    {
        // Emails válidos
        $this->assertTrue(Validator::email('user@unilicungo.ac.mz'));
        $this->assertTrue(Validator::email('test.user@example.com'));
        $this->assertTrue(Validator::email('user+tag@domain.co.mz'));
        
        // Emails inválidos
        $this->assertFalse(Validator::email('invalid'));
        $this->assertFalse(Validator::email('@example.com'));
        $this->assertFalse(Validator::email('user@'));
        $this->assertFalse(Validator::email(''));
    }
    
    /**
     * Testa validação de campo obrigatório
     */
    public function test_required_validation(): void
    {
        // Valores válidos
        $this->assertTrue(Validator::required('value'));
        $this->assertTrue(Validator::required('0'));
        $this->assertTrue(Validator::required(123));
        
        // Valores inválidos
        $this->assertFalse(Validator::required(''));
        $this->assertFalse(Validator::required(null));
        $this->assertFalse(Validator::required('   ')); // apenas espaços
    }
    
    /**
     * Testa validação de tamanho mínimo
     */
    public function test_min_length_validation(): void
    {
        $this->assertTrue(Validator::min('hello', 3));
        $this->assertTrue(Validator::min('test', 4));
        
        $this->assertFalse(Validator::min('hi', 3));
        $this->assertFalse(Validator::min('', 1));
    }
    
    /**
     * Testa validação de tamanho máximo
     */
    public function test_max_length_validation(): void
    {
        $this->assertTrue(Validator::max('hi', 5));
        $this->assertTrue(Validator::max('test', 4));
        
        $this->assertFalse(Validator::max('toolong', 5));
    }
    
    /**
     * Testa validação de telefone moçambicano
     */
    public function test_validates_mozambican_phone(): void
    {
        // Telefones válidos
        $this->assertTrue(Validator::phone('840001234')); // 84
        $this->assertTrue(Validator::phone('850001234')); // 85
        $this->assertTrue(Validator::phone('860001234')); // 86
        $this->assertTrue(Validator::phone('870001234')); // 87
        
        // Telefones inválidos
        $this->assertFalse(Validator::phone('123456789'));
        $this->assertFalse(Validator::phone('8400012')); // muito curto
        $this->assertFalse(Validator::phone('abc'));
    }
    
    /**
     * Testa validação numérica
     */
    public function test_validates_numeric_values(): void
    {
        $this->assertTrue(Validator::numeric('123'));
        $this->assertTrue(Validator::numeric('0'));
        $this->assertTrue(Validator::numeric(456));
        
        $this->assertFalse(Validator::numeric('abc'));
        $this->assertFalse(Validator::numeric('12.34'));
    }
    
    /**
     * Testa validação de range (entre valores)
     */
    public function test_validates_value_between_range(): void
    {
        $this->assertTrue(Validator::between(5, 1, 10));
        $this->assertTrue(Validator::between(1, 1, 10)); // limite inferior
        $this->assertTrue(Validator::between(10, 1, 10)); // limite superior
        
        $this->assertFalse(Validator::between(0, 1, 10));
        $this->assertFalse(Validator::between(11, 1, 10));
    }
}
