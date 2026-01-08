<?php

namespace Tests\Unit;

use App\Utils\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Testes Unitários para o Validator
 */
class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    /**
     * @test
     */
    public function testRequiredFieldPasses(): void
    {
        $data = ['name' => 'João'];
        $rules = ['name' => 'required'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
        $this->assertEmpty($this->validator->errors());
    }

    /**
     * @test
     */
    public function testRequiredFieldFails(): void
    {
        $data = ['name' => ''];
        $rules = ['name' => 'required'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
        $this->assertNotEmpty($this->validator->errors());
        $this->assertArrayHasKey('name', $this->validator->errors());
    }

    /**
     * @test
     */
    public function testEmailValidationPasses(): void
    {
        $data = ['email' => 'user@example.com'];
        $rules = ['email' => 'email'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testEmailValidationFails(): void
    {
        $data = ['email' => 'invalid-email'];
        $rules = ['email' => 'email'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
        $this->assertArrayHasKey('email', $this->validator->errors());
    }

    /**
     * @test
     */
    public function testMinLengthPasses(): void
    {
        $data = ['password' => '123456'];
        $rules = ['password' => 'min:6'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testMinLengthFails(): void
    {
        $data = ['password' => '12345'];
        $rules = ['password' => 'min:6'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testMaxLengthPasses(): void
    {
        $data = ['code' => '12345'];
        $rules = ['code' => 'max:10'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testMaxLengthFails(): void
    {
        $data = ['code' => '12345678901'];
        $rules = ['code' => 'max:10'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testNumericPasses(): void
    {
        $data = ['age' => '25'];
        $rules = ['age' => 'numeric'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testNumericFails(): void
    {
        $data = ['age' => 'twenty five'];
        $rules = ['age' => 'numeric'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testInValidationPasses(): void
    {
        $data = ['role' => 'vigilante'];
        $rules = ['role' => 'in:vigilante,membro,coordenador'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testInValidationFails(): void
    {
        $data = ['role' => 'admin'];
        $rules = ['role' => 'in:vigilante,membro,coordenador'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testNibValidationPasses(): void
    {
        // NIB moçambicano: 23 dígitos
        $data = ['nib' => '12345678901234567890123'];
        $rules = ['nib' => 'nib'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testNibValidationFails(): void
    {
        $data = ['nib' => '123456789'];
        $rules = ['nib' => 'nib'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testNuitValidationPasses(): void
    {
        // NUIT moçambicano: 9 dígitos
        $data = ['nuit' => '123456789'];
        $rules = ['nuit' => 'nuit'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testNuitValidationFails(): void
    {
        $data = ['nuit' => '12345'];
        $rules = ['nuit' => 'nuit'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testPhoneMzValidationPasses(): void
    {
        $data = ['phone' => '+258841234567'];
        $rules = ['phone' => 'phone_mz'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testPhoneMzValidationFails(): void
    {
        $data = ['phone' => '841234567'];
        $rules = ['phone' => 'phone_mz'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testDateValidationPasses(): void
    {
        $data = ['date' => '2026-01-15'];
        $rules = ['date' => 'date'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testTimeValidationPasses(): void
    {
        $data = ['time' => '08:30'];
        $rules = ['time' => 'time'];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testTimeValidationFails(): void
    {
        $data = ['time' => '25:00'];
        $rules = ['time' => 'time'];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testMultipleRulesPass(): void
    {
        $data = [
            'name' => 'João',
            'email' => 'joao@example.com',
            'age' => '25'
        ];
        $rules = [
            'name' => 'required|min:2|max:50',
            'email' => 'required|email',
            'age' => 'numeric'
        ];

        $result = $this->validator->validate($data, $rules);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testMultipleRulesFail(): void
    {
        $data = [
            'name' => '',
            'email' => 'invalid',
            'age' => 'not-a-number'
        ];
        $rules = [
            'name' => 'required',
            'email' => 'email',
            'age' => 'numeric'
        ];

        $result = $this->validator->validate($data, $rules);

        $this->assertFalse($result);
        $this->assertCount(3, $this->validator->errors());
    }
}
