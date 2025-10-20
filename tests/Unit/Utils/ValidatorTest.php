<?php

namespace Tests\Unit\Utils;

use App\Utils\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Testes para a classe Validator
 * 
 * Executar: ./vendor/bin/phpunit tests/Unit/Utils/ValidatorTest.php
 */
class ValidatorTest extends TestCase
{
    private Validator $validator;
    
    protected function setUp(): void
    {
        $this->validator = new Validator();
    }
    
    // ========================================
    // Testes: Campo Obrigatório
    // ========================================
    
    public function test_validates_required_field_with_empty_string()
    {
        $data = ['name' => ''];
        $rules = ['name' => 'required'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
        $this->assertArrayHasKey('name', $this->validator->errors());
    }
    
    public function test_validates_required_field_with_null()
    {
        $data = ['name' => null];
        $rules = ['name' => 'required'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
    }
    
    public function test_validates_required_field_with_valid_value()
    {
        $data = ['name' => 'João Silva'];
        $rules = ['name' => 'required'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertTrue($isValid);
        $this->assertEmpty($this->validator->errors());
    }
    
    // ========================================
    // Testes: Email
    // ========================================
    
    public function test_validates_email_format_invalid()
    {
        $invalidEmails = [
            'invalid-email',
            'missing@domain',
            '@domain.com',
            'user@',
            'user space@domain.com'
        ];
        
        foreach ($invalidEmails as $email) {
            $data = ['email' => $email];
            $rules = ['email' => 'email'];
            
            $isValid = $this->validator->validate($data, $rules);
            
            $this->assertFalse($isValid, "Email '{$email}' deveria ser inválido");
            $this->assertArrayHasKey('email', $this->validator->errors());
        }
    }
    
    public function test_validates_email_format_valid()
    {
        $validEmails = [
            'user@example.com',
            'user.name@example.com',
            'user+tag@example.co.mz',
            'admin@unilicungo.ac.mz'
        ];
        
        foreach ($validEmails as $email) {
            $data = ['email' => $email];
            $rules = ['email' => 'email'];
            
            $isValid = $this->validator->validate($data, $rules);
            
            $this->assertTrue($isValid, "Email '{$email}' deveria ser válido");
        }
    }
    
    // ========================================
    // Testes: NUIT (Moçambique)
    // ========================================
    
    public function test_validates_nuit_mozambique_valid()
    {
        $data = ['nuit' => '123456789'];
        $rules = ['nuit' => 'nuit'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertTrue($isValid);
    }
    
    public function test_validates_nuit_mozambique_too_short()
    {
        $data = ['nuit' => '12345'];
        $rules = ['nuit' => 'nuit'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
        $this->assertArrayHasKey('nuit', $this->validator->errors());
    }
    
    public function test_validates_nuit_mozambique_too_long()
    {
        $data = ['nuit' => '1234567890'];
        $rules = ['nuit' => 'nuit'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
    }
    
    public function test_validates_nuit_mozambique_non_numeric()
    {
        $data = ['nuit' => '12345678A'];
        $rules = ['nuit' => 'nuit'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
    }
    
    // ========================================
    // Testes: NIB (Moçambique)
    // ========================================
    
    public function test_validates_nib_mozambique_valid()
    {
        $data = ['nib' => '12345678901234567890123'];
        $rules = ['nib' => 'nib'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertTrue($isValid);
    }
    
    public function test_validates_nib_mozambique_invalid_length()
    {
        $data = ['nib' => '123456789012'];
        $rules = ['nib' => 'nib'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
        $this->assertArrayHasKey('nib', $this->validator->errors());
    }
    
    // ========================================
    // Testes: Telefone (Moçambique)
    // ========================================
    
    public function test_validates_phone_mozambique_valid_formats()
    {
        $validPhones = [
            '+258841234567',
            '+258 84 123 4567',
            '+258821234567',
            '+258 82 123 4567',
            '+258871234567'
        ];
        
        foreach ($validPhones as $phone) {
            $data = ['phone' => $phone];
            $rules = ['phone' => 'phone_mz'];
            
            $isValid = $this->validator->validate($data, $rules);
            
            $this->assertTrue($isValid, "Telefone '{$phone}' deveria ser válido");
        }
    }
    
    public function test_validates_phone_mozambique_invalid_formats()
    {
        $invalidPhones = [
            '841234567',           // Sem código país
            '+258911234567',       // Operadora inválida (91)
            '+258 84 123',         // Muito curto
            '+258 88 123 4567',    // Operadora inválida (88)
            '258841234567',        // Sem +
            '+258 81 123 4567'     // Operadora inválida (81)
        ];
        
        foreach ($invalidPhones as $phone) {
            $data = ['phone' => $phone];
            $rules = ['phone' => 'phone_mz'];
            
            $isValid = $this->validator->validate($data, $rules);
            
            $this->assertFalse($isValid, "Telefone '{$phone}' deveria ser inválido");
            $this->assertArrayHasKey('phone', $this->validator->errors());
        }
    }
    
    // ========================================
    // Testes: Min/Max Length
    // ========================================
    
    public function test_validates_min_length()
    {
        $data = ['password' => '123'];
        $rules = ['password' => 'min:8'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
        $this->assertArrayHasKey('password', $this->validator->errors());
        
        // Válido
        $data = ['password' => '12345678'];
        $isValid = $this->validator->validate($data, $rules);
        $this->assertTrue($isValid);
    }
    
    public function test_validates_max_length()
    {
        $data = ['bio' => str_repeat('a', 300)];
        $rules = ['bio' => 'max:200'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
        
        // Válido
        $data = ['bio' => str_repeat('a', 200)];
        $isValid = $this->validator->validate($data, $rules);
        $this->assertTrue($isValid);
    }
    
    // ========================================
    // Testes: Numeric
    // ========================================
    
    public function test_validates_numeric()
    {
        $data = ['age' => 'twenty'];
        $rules = ['age' => 'numeric'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
        
        // Válidos
        $validNumbers = ['25', '25.5', 25, 25.5];
        foreach ($validNumbers as $num) {
            $data = ['age' => $num];
            $isValid = $this->validator->validate($data, $rules);
            $this->assertTrue($isValid, "'{$num}' deveria ser numérico válido");
        }
    }
    
    // ========================================
    // Testes: Date
    // ========================================
    
    public function test_validates_date()
    {
        $invalidDates = [
            'not-a-date',
            '2025-13-01',
            '2025-01-32',
            '32/01/2025'
        ];
        
        foreach ($invalidDates as $date) {
            $data = ['exam_date' => $date];
            $rules = ['exam_date' => 'date'];
            
            $isValid = $this->validator->validate($data, $rules);
            
            $this->assertFalse($isValid, "Data '{$date}' deveria ser inválida");
        }
        
        // Válidas
        $validDates = [
            '2025-01-15',
            '15/01/2025',
            '2025-12-31'
        ];
        
        foreach ($validDates as $date) {
            $data = ['exam_date' => $date];
            $rules = ['exam_date' => 'date'];
            
            $isValid = $this->validator->validate($data, $rules);
            
            $this->assertTrue($isValid, "Data '{$date}' deveria ser válida");
        }
    }
    
    // ========================================
    // Testes: Time
    // ========================================
    
    public function test_validates_time()
    {
        $invalidTimes = [
            '25:00',
            '12:60',
            'invalid',
            '1pm'
        ];
        
        foreach ($invalidTimes as $time) {
            $data = ['start_time' => $time];
            $rules = ['start_time' => 'time'];
            
            $isValid = $this->validator->validate($data, $rules);
            
            $this->assertFalse($isValid, "Hora '{$time}' deveria ser inválida");
        }
        
        // Válidas
        $validTimes = [
            '08:00',
            '14:30',
            '23:59',
            '00:00'
        ];
        
        foreach ($validTimes as $time) {
            $data = ['start_time' => $time];
            $rules = ['start_time' => 'time'];
            
            $isValid = $this->validator->validate($data, $rules);
            
            $this->assertTrue($isValid, "Hora '{$time}' deveria ser válida");
        }
    }
    
    // ========================================
    // Testes: In (Enum)
    // ========================================
    
    public function test_validates_in_list()
    {
        $data = ['role' => 'admin'];
        $rules = ['role' => 'in:coordenador,membro,vigilante'];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
        
        // Válido
        $data = ['role' => 'coordenador'];
        $isValid = $this->validator->validate($data, $rules);
        $this->assertTrue($isValid);
    }
    
    // ========================================
    // Testes: Múltiplas Regras
    // ========================================
    
    public function test_validates_multiple_rules()
    {
        $data = [
            'name' => 'João',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'nuit' => '123456789'
        ];
        
        $rules = [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'nuit' => 'required|nuit'
        ];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertTrue($isValid);
        $this->assertEmpty($this->validator->errors());
    }
    
    public function test_validates_multiple_fields_with_errors()
    {
        $data = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123'
        ];
        
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8'
        ];
        
        $isValid = $this->validator->validate($data, $rules);
        
        $this->assertFalse($isValid);
        $errors = $this->validator->errors();
        
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }
}
