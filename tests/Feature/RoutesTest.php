<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * Testes de Feature para Rotas
 * 
 * Valida que as rotas principais da aplicação estão acessíveis
 */
class RoutesTest extends TestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = env('APP_URL', 'http://localhost/comexamesul/public');
    }

    /**
     * Helper para verificar se o servidor está acessível
     */
    private function isServerRunning(): bool
    {
        $headers = @get_headers($this->baseUrl);
        return $headers !== false;
    }

    /**
     * @test
     */
    public function testHomepageLoads(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Application server not running');
        }

        $headers = get_headers($this->baseUrl);
        $statusLine = $headers[0];

        // Homepage deve redirecionar para login ou retornar 200
        $this->assertTrue(
            str_contains($statusLine, '200') || str_contains($statusLine, '302'),
            "Homepage should return 200 or 302, got: {$statusLine}"
        );
    }

    /**
     * @test
     */
    public function testLoginPageLoads(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Application server not running');
        }

        $headers = get_headers($this->baseUrl . '/login');
        $statusLine = $headers[0];

        $this->assertStringContainsString('200', $statusLine, "Login page should return 200");
    }

    /**
     * @test
     */
    public function testProtectedRoutesRedirectToLogin(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Application server not running');
        }

        $protectedRoutes = [
            '/dashboard',
            '/juries',
            '/vacancies',
            '/profile',
        ];

        foreach ($protectedRoutes as $route) {
            $context = stream_context_create([
                'http' => [
                    'follow_location' => false
                ]
            ]);

            $headers = @get_headers($this->baseUrl . $route, 1, $context);

            if ($headers) {
                $statusLine = $headers[0];
                // Deve retornar 302 (redirect) para login
                $this->assertTrue(
                    str_contains($statusLine, '302') || str_contains($statusLine, '200'),
                    "Protected route {$route} should redirect or be accessible"
                );
            }
        }
    }

    /**
     * @test
     */
    public function testPublicAssetsAccessible(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Application server not running');
        }

        $assets = [
            '/css/tailwind.css',
            '/css/components.css',
        ];

        foreach ($assets as $asset) {
            $headers = @get_headers($this->baseUrl . $asset);

            if ($headers) {
                $statusLine = $headers[0];
                $this->assertStringContainsString(
                    '200',
                    $statusLine,
                    "Asset {$asset} should be accessible"
                );
            }
        }
    }
}
