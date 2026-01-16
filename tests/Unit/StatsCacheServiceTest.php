<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Testes Unitários para StatsCacheService
 * 
 * Valida funcionalidades de cache, tags e métricas
 */
class StatsCacheServiceTest extends TestCase
{
    private \App\Services\StatsCacheService $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new \App\Services\StatsCacheService();
        // Limpar cache antes de cada teste
        $this->cache->flush();
        $this->cache->resetMetrics();
    }

    protected function tearDown(): void
    {
        // Limpar cache após cada teste
        $this->cache->flush();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testCacheServiceExists(): void
    {
        $this->assertTrue(
            class_exists(\App\Services\StatsCacheService::class),
            'StatsCacheService class should exist'
        );
    }

    /**
     * @test
     */
    public function testRememberStoresAndRetrievesValue(): void
    {
        $key = 'test_key_' . time();
        $value = ['data' => 'test_value', 'number' => 42];

        $result = $this->cache->remember($key, function () use ($value) {
            return $value;
        });

        $this->assertEquals($value, $result);

        // Segunda chamada deve retornar do cache
        $callbackCalled = false;
        $result2 = $this->cache->remember($key, function () use (&$callbackCalled) {
            $callbackCalled = true;
            return 'should_not_be_returned';
        });

        $this->assertEquals($value, $result2);
        $this->assertFalse($callbackCalled, 'Callback should not be called on cache hit');
    }

    /**
     * @test
     */
    public function testGetReturnsNullForMissingKey(): void
    {
        $result = $this->cache->get('non_existent_key_' . time());
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function testSetStoresValue(): void
    {
        $key = 'set_test_' . time();
        $value = ['name' => 'test'];

        $result = $this->cache->set($key, $value);
        $this->assertTrue($result);

        $retrieved = $this->cache->get($key);
        $this->assertEquals($value, $retrieved);
    }

    /**
     * @test
     */
    public function testForgetRemovesValue(): void
    {
        $key = 'forget_test_' . time();
        $this->cache->set($key, 'value');

        $this->cache->forget($key);

        $result = $this->cache->get($key);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function testFlushClearsAllCache(): void
    {
        // Adicionar alguns itens
        $this->cache->set('flush_test_1', 'value1');
        $this->cache->set('flush_test_2', 'value2');

        $count = $this->cache->flush();

        $this->assertGreaterThanOrEqual(2, $count);
        $this->assertNull($this->cache->get('flush_test_1'));
        $this->assertNull($this->cache->get('flush_test_2'));
    }

    /**
     * @test
     */
    public function testMetricsTrackHitsAndMisses(): void
    {
        $key = 'metrics_test_' . time();

        // Miss
        $this->cache->get('non_existent_' . time());

        // Set + Hit
        $this->cache->set($key, 'value');
        $this->cache->get($key);

        $metrics = $this->cache->getMetrics();

        $this->assertArrayHasKey('hits', $metrics);
        $this->assertArrayHasKey('misses', $metrics);
        $this->assertArrayHasKey('hit_rate', $metrics);
        $this->assertGreaterThanOrEqual(1, $metrics['hits']);
        $this->assertGreaterThanOrEqual(1, $metrics['misses']);
    }

    /**
     * @test
     */
    public function testTagsAllowGroupInvalidation(): void
    {
        $key1 = 'tag_test_1_' . time();
        $key2 = 'tag_test_2_' . time();
        $key3 = 'tag_test_3_' . time();

        // Set com tags
        $this->cache->set($key1, 'value1', ['group_a']);
        $this->cache->set($key2, 'value2', ['group_a']);
        $this->cache->set($key3, 'value3', ['group_b']);

        // Invalidar grupo A
        $count = $this->cache->forgetByTag('group_a');

        $this->assertEquals(2, $count);
        $this->assertNull($this->cache->get($key1));
        $this->assertNull($this->cache->get($key2));
        $this->assertEquals('value3', $this->cache->get($key3));
    }

    /**
     * @test
     */
    public function testTTLConstantsExist(): void
    {
        $this->assertEquals(60, \App\Services\StatsCacheService::TTL_SHORT);
        $this->assertEquals(300, \App\Services\StatsCacheService::TTL_MEDIUM);
        $this->assertEquals(900, \App\Services\StatsCacheService::TTL_LONG);
        $this->assertEquals(3600, \App\Services\StatsCacheService::TTL_EXTENDED);
    }

    /**
     * @test
     */
    public function testContextualCacheMethods(): void
    {
        $this->assertTrue(
            method_exists(\App\Services\StatsCacheService::class, 'rememberForVacancy'),
            'rememberForVacancy method should exist'
        );

        $this->assertTrue(
            method_exists(\App\Services\StatsCacheService::class, 'rememberForUser'),
            'rememberForUser method should exist'
        );

        $this->assertTrue(
            method_exists(\App\Services\StatsCacheService::class, 'invalidateVacancy'),
            'invalidateVacancy method should exist'
        );

        $this->assertTrue(
            method_exists(\App\Services\StatsCacheService::class, 'invalidateJuries'),
            'invalidateJuries method should exist'
        );
    }

    /**
     * @test
     */
    public function testInfoReturnsValidStructure(): void
    {
        $info = $this->cache->info();

        $this->assertArrayHasKey('total_files', $info);
        $this->assertArrayHasKey('total_size_bytes', $info);
        $this->assertArrayHasKey('cache_directory', $info);
        $this->assertArrayHasKey('default_ttl', $info);
        $this->assertArrayHasKey('total_tags', $info);
        $this->assertArrayHasKey('metrics', $info);
    }
}
