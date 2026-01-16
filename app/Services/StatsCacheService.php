<?php

namespace App\Services;

/**
 * Serviço de Cache para Estatísticas
 * 
 * Cacheia resultados de queries pesadas para melhorar performance
 * do dashboard e páginas de estatísticas.
 * 
 * Recursos:
 * - Cache baseado em arquivo JSON
 * - Suporte a tags para invalidação em grupo
 * - Métricas de hit/miss
 * - TTL configurável por chave
 * 
 * @package App\Services
 */
class StatsCacheService
{
    private const CACHE_DIR = BASE_PATH . '/storage/cache/stats';
    private const TAGS_FILE = BASE_PATH . '/storage/cache/stats/_tags.json';
    private const METRICS_FILE = BASE_PATH . '/storage/cache/stats/_metrics.json';
    private const CACHE_TTL = 300; // 5 minutos

    // TTLs predefinidos para diferentes tipos de cache
    public const TTL_SHORT = 60;      // 1 minuto - dados muito dinâmicos
    public const TTL_MEDIUM = 300;    // 5 minutos - padrão
    public const TTL_LONG = 900;      // 15 minutos - dados menos dinâmicos
    public const TTL_EXTENDED = 3600; // 1 hora - dados raramente alterados

    private array $metrics = [];
    private array $tags = [];

    public function __construct()
    {
        $this->ensureCacheDirectoryExists();
        $this->loadMetrics();
        $this->loadTags();
    }

    /**
     * Obter valor do cache ou executar callback e cachear resultado
     * 
     * @param string $key Chave única do cache
     * @param callable $callback Função para calcular valor se não existir em cache
     * @param int|null $ttl Time to live em segundos (opcional)
     * @param array $tags Tags para agrupar cache (para invalidação em lote)
     * @return mixed Valor cacheado ou calculado
     */
    public function remember(string $key, callable $callback, ?int $ttl = null, array $tags = []): mixed
    {
        $ttl = $ttl ?? self::CACHE_TTL;
        $cacheFile = $this->getCacheFilePath($key);

        // Verificar se cache existe e é válido
        if ($this->isCacheValid($cacheFile, $ttl)) {
            $this->recordHit($key);
            return $this->readCache($cacheFile);
        }

        $this->recordMiss($key);

        // Calcular novo valor
        $value = $callback();

        // Salvar em cache
        $this->writeCache($cacheFile, $value);

        // Associar tags
        if (!empty($tags)) {
            $this->tagKey($key, $tags);
        }

        return $value;
    }

    /**
     * Cache contextual para júris por vaga
     */
    public function rememberForVacancy(int $vacancyId, string $suffix, callable $callback, ?int $ttl = null): mixed
    {
        $key = "vacancy_{$vacancyId}_{$suffix}";
        return $this->remember($key, $callback, $ttl, ["vacancy_{$vacancyId}", "juries"]);
    }

    /**
     * Cache contextual para usuários
     */
    public function rememberForUser(int $userId, string $suffix, callable $callback, ?int $ttl = null): mixed
    {
        $key = "user_{$userId}_{$suffix}";
        return $this->remember($key, $callback, $ttl, ["user_{$userId}"]);
    }

    /**
     * Obter valor do cache
     * 
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key): mixed
    {
        $cacheFile = $this->getCacheFilePath($key);

        if (!file_exists($cacheFile)) {
            $this->recordMiss($key);
            return null;
        }

        $this->recordHit($key);
        return $this->readCache($cacheFile);
    }

    /**
     * Salvar valor em cache
     * 
     * @param string $key
     * @param mixed $value
     * @param array $tags
     * @return bool
     */
    public function set(string $key, mixed $value, array $tags = []): bool
    {
        $cacheFile = $this->getCacheFilePath($key);
        $result = $this->writeCache($cacheFile, $value);

        if ($result && !empty($tags)) {
            $this->tagKey($key, $tags);
        }

        return $result;
    }

    /**
     * Remover item específico do cache
     * 
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        $cacheFile = $this->getCacheFilePath($key);

        // Remover das tags
        $this->untagKey($key);

        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }

        return true;
    }

    /**
     * Remover múltiplos itens do cache
     * 
     * @param array $keys
     * @return void
     */
    public function forgetMany(array $keys): void
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Invalidar cache por tag
     * 
     * @param string $tag
     * @return int Número de itens removidos
     */
    public function forgetByTag(string $tag): int
    {
        if (!isset($this->tags[$tag])) {
            return 0;
        }

        $count = 0;
        foreach ($this->tags[$tag] as $key) {
            if ($this->forget($key)) {
                $count++;
            }
        }

        unset($this->tags[$tag]);
        $this->saveTags();

        return $count;
    }

    /**
     * Invalidar todo cache de uma vaga
     */
    public function invalidateVacancy(int $vacancyId): int
    {
        return $this->forgetByTag("vacancy_{$vacancyId}");
    }

    /**
     * Invalidar todo cache de júris
     */
    public function invalidateJuries(): int
    {
        return $this->forgetByTag("juries");
    }

    /**
     * Invalidar cache de um usuário
     */
    public function invalidateUser(int $userId): int
    {
        return $this->forgetByTag("user_{$userId}");
    }

    /**
     * Limpar todo o cache
     * 
     * @return int Número de arquivos removidos
     */
    public function flush(): int
    {
        $files = glob(self::CACHE_DIR . '/*.json');

        if (!$files) {
            return 0;
        }

        $count = 0;
        foreach ($files as $file) {
            // Não remover arquivos de sistema
            $basename = basename($file);
            if ($basename === '_tags.json' || $basename === '_metrics.json') {
                continue;
            }

            if (unlink($file)) {
                $count++;
            }
        }

        // Limpar tags
        $this->tags = [];
        $this->saveTags();

        return $count;
    }

    /**
     * Limpar cache expirado
     * 
     * @param int $ttl
     * @return int Número de arquivos removidos
     */
    public function clearExpired(int $ttl = self::CACHE_TTL): int
    {
        $files = glob(self::CACHE_DIR . '/*.json');

        if (!$files) {
            return 0;
        }

        $count = 0;
        $now = time();

        foreach ($files as $file) {
            $basename = basename($file);
            if ($basename === '_tags.json' || $basename === '_metrics.json') {
                continue;
            }

            if (($now - filemtime($file)) > $ttl) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Obter métricas do cache
     * 
     * @return array
     */
    public function getMetrics(): array
    {
        $hits = $this->metrics['hits'] ?? 0;
        $misses = $this->metrics['misses'] ?? 0;
        $total = $hits + $misses;

        return [
            'hits' => $hits,
            'misses' => $misses,
            'total_requests' => $total,
            'hit_rate' => $total > 0 ? round(($hits / $total) * 100, 2) : 0,
            'miss_rate' => $total > 0 ? round(($misses / $total) * 100, 2) : 0,
            'last_hit' => $this->metrics['last_hit'] ?? null,
            'last_miss' => $this->metrics['last_miss'] ?? null,
        ];
    }

    /**
     * Resetar métricas
     */
    public function resetMetrics(): void
    {
        $this->metrics = [
            'hits' => 0,
            'misses' => 0,
            'last_hit' => null,
            'last_miss' => null,
        ];
        $this->saveMetrics();
    }

    /**
     * Obter informações sobre o cache
     * 
     * @return array
     */
    public function info(): array
    {
        $files = glob(self::CACHE_DIR . '/*.json');
        $totalSize = 0;
        $oldestFile = null;
        $newestFile = null;
        $cacheFiles = 0;

        if ($files) {
            foreach ($files as $file) {
                $basename = basename($file);
                if ($basename === '_tags.json' || $basename === '_metrics.json') {
                    continue;
                }

                $cacheFiles++;
                $totalSize += filesize($file);
                $mtime = filemtime($file);

                if ($oldestFile === null || $mtime < filemtime($oldestFile)) {
                    $oldestFile = $file;
                }

                if ($newestFile === null || $mtime > filemtime($newestFile)) {
                    $newestFile = $file;
                }
            }
        }

        return [
            'total_files' => $cacheFiles,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'oldest_file' => $oldestFile ? basename($oldestFile) : null,
            'newest_file' => $newestFile ? basename($newestFile) : null,
            'cache_directory' => self::CACHE_DIR,
            'default_ttl' => self::CACHE_TTL,
            'total_tags' => count($this->tags),
            'metrics' => $this->getMetrics(),
        ];
    }

    // ========================================
    // Métodos Privados - Cache
    // ========================================

    private function ensureCacheDirectoryExists(): void
    {
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }
    }

    private function getCacheFilePath(string $key): string
    {
        $hash = md5($key);
        return self::CACHE_DIR . '/' . $hash . '.json';
    }

    private function isCacheValid(string $cacheFile, int $ttl): bool
    {
        if (!file_exists($cacheFile)) {
            return false;
        }

        $fileAge = time() - filemtime($cacheFile);
        return $fileAge < $ttl;
    }

    private function readCache(string $cacheFile): mixed
    {
        $content = file_get_contents($cacheFile);
        return json_decode($content, true);
    }

    private function writeCache(string $cacheFile, mixed $value): bool
    {
        $content = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($cacheFile, $content) !== false;
    }

    // ========================================
    // Métodos Privados - Tags
    // ========================================

    private function loadTags(): void
    {
        if (file_exists(self::TAGS_FILE)) {
            $content = file_get_contents(self::TAGS_FILE);
            $this->tags = json_decode($content, true) ?? [];
        } else {
            $this->tags = [];
        }
    }

    private function saveTags(): void
    {
        $content = json_encode($this->tags, JSON_PRETTY_PRINT);
        file_put_contents(self::TAGS_FILE, $content);
    }

    private function tagKey(string $key, array $tags): void
    {
        foreach ($tags as $tag) {
            if (!isset($this->tags[$tag])) {
                $this->tags[$tag] = [];
            }
            if (!in_array($key, $this->tags[$tag])) {
                $this->tags[$tag][] = $key;
            }
        }
        $this->saveTags();
    }

    private function untagKey(string $key): void
    {
        foreach ($this->tags as $tag => &$keys) {
            $pos = array_search($key, $keys);
            if ($pos !== false) {
                unset($keys[$pos]);
                $keys = array_values($keys);
            }
        }
        $this->saveTags();
    }

    // ========================================
    // Métodos Privados - Métricas
    // ========================================

    private function loadMetrics(): void
    {
        if (file_exists(self::METRICS_FILE)) {
            $content = file_get_contents(self::METRICS_FILE);
            $this->metrics = json_decode($content, true) ?? [];
        } else {
            $this->metrics = ['hits' => 0, 'misses' => 0];
        }
    }

    private function saveMetrics(): void
    {
        $content = json_encode($this->metrics, JSON_PRETTY_PRINT);
        file_put_contents(self::METRICS_FILE, $content);
    }

    private function recordHit(string $key): void
    {
        $this->metrics['hits'] = ($this->metrics['hits'] ?? 0) + 1;
        $this->metrics['last_hit'] = $key;
        $this->saveMetrics();
    }

    private function recordMiss(string $key): void
    {
        $this->metrics['misses'] = ($this->metrics['misses'] ?? 0) + 1;
        $this->metrics['last_miss'] = $key;
        $this->saveMetrics();
    }
}
