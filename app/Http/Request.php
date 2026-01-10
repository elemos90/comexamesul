<?php

namespace App\Http;

class Request
{
    private array $server;
    private array $query;
    private array $body;
    private array $files;
    private array $params = [];

    public function __construct(array $server, array $query = [], array $body = [], array $files = [])
    {
        $this->server = $server;
        $this->query = $query;
        $this->body = $body;
        $this->files = $files;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $uri = strtok($uri, '?');

        // Detectar e remover base path se estiver em subdiretório
        $scriptName = $this->server['SCRIPT_NAME'] ?? '';
        $basePath = str_replace('\\', '/', dirname($scriptName));

        if ($basePath !== '/' && $basePath !== '.' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        return '/' . ltrim($uri, '/');
    }

    public function query(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function input(string $key = null, $default = null)
    {
        $data = $this->all();
        if ($key === null) {
            return $data;
        }
        return $data[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function only(array $keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->body)) {
                $data[$key] = $this->body[$key];
            }
        }
        return $data;
    }

    public function files(string $key = null)
    {
        if ($key === null) {
            return $this->files;
        }
        return $this->files[$key] ?? null;
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    public function params(): array
    {
        return $this->params;
    }

    public function isAjax(): bool
    {
        return strtolower($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    /**
     * Parse JSON body from request
     * 
     * @return array Parsed JSON data
     */
    public function json(): array
    {
        $rawBody = file_get_contents('php://input');
        if (empty($rawBody)) {
            return [];
        }
        $data = json_decode($rawBody, true);
        return is_array($data) ? $data : [];
    }
}

