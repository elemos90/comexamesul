<?php

namespace App\Services;

/**
 * Serviço de Cache para Estatísticas
 * 
 * Cacheia resultados de queries pesadas para melhorar performance
 * do dashboard e páginas de estatísticas.
 * 
 * @package App\Services
 */
class StatsCacheService
{
    private const CACHE_DIR = BASE_PATH . '/storage/cache/stats';
    private const CACHE_TTL = 300; // 5 minutos
    
    public function __construct()
    {
        $this->ensureCacheDirectoryExists();
    }
    
    /**
     * Obter valor do cache ou executar callback e cachear resultado
     * 
     * @param string $key Chave única do cache
     * @param callable $callback Função para calcular valor se não existir em cache
     * @param int|null $ttl Time to live em segundos (opcional)
     * @return mixed Valor cacheado ou calculado
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? self::CACHE_TTL;
        $cacheFile = $this->getCacheFilePath($key);
        
        // Verificar se cache existe e é válido
        if ($this->isCacheValid($cacheFile, $ttl)) {
            return $this->readCache($cacheFile);
        }
        
        // Calcular novo valor
        $value = $callback();
        
        // Salvar em cache
        $this->writeCache($cacheFile, $value);
        
        return $value;
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
            return null;
        }
        
        return $this->readCache($cacheFile);
    }
    
    /**
     * Salvar valor em cache
     * 
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set(string $key, mixed $value): bool
    {
        $cacheFile = $this->getCacheFilePath($key);
        return $this->writeCache($cacheFile, $value);
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
            if (unlink($file)) {
                $count++;
            }
        }
        
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
            if (($now - filemtime($file)) > $ttl) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }
        
        return $count;
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
        
        if ($files) {
            foreach ($files as $file) {
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
            'total_files' => count($files),
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'oldest_file' => $oldestFile ? basename($oldestFile) : null,
            'newest_file' => $newestFile ? basename($newestFile) : null,
            'cache_directory' => self::CACHE_DIR,
            'default_ttl' => self::CACHE_TTL,
        ];
    }
    
    // ========================================
    // Métodos Privados
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
}
