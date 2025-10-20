<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function find(int $id): ?array
    {
        $columns = $this->getSelectColumns();
        $sql = "SELECT {$columns} FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function all(array $conditions = []): array
    {
        $columns = $this->getSelectColumns();
        $sql = "SELECT {$columns} FROM {$this->table}";
        if ($conditions) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "{$column} = :{$column}";
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        $assignments = [];
        foreach ($data as $column => $value) {
            $assignments[] = "{$column} = :{$column}";
        }
        if (!$assignments) {
            return false;
        }
        $data[$this->primaryKey] = $id;
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :%s',
            $this->table,
            implode(', ', $assignments),
            $this->primaryKey,
            $this->primaryKey
        );
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function firstWhere(string $column, $value): ?array
    {
        $columns = $this->getSelectColumns();
        $sql = "SELECT {$columns} FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['value' => $value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function paginate(int $page = 1, int $perPage = 15, array $conditions = []): array
    {
        $offset = ($page - 1) * $perPage;
        $params = $conditions;
        $whereClause = '';
        if ($conditions) {
            $whereParts = [];
            foreach ($conditions as $column => $value) {
                $whereParts[] = "{$column} = :{$column}";
            }
            $whereClause = ' WHERE ' . implode(' AND ', $whereParts);
        }
        $columns = $this->getSelectColumns();
        $sql = "SELECT SQL_CALC_FOUND_ROWS {$columns} FROM {$this->table}{$whereClause} ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        $total = (int) $this->db->query('SELECT FOUND_ROWS()')->fetchColumn();
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    protected function filterFillable(array $data): array
    {
        if (!$this->fillable) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function statement(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Obter instância do banco de dados
     */
    public function getDb(): PDO
    {
        return $this->db;
    }

    /**
     * Obter colunas para SELECT (override em cada Model para segurança)
     * 
     * @return string
     */
    protected function getSelectColumns(): string
    {
        if (isset($this->selectColumns) && !empty($this->selectColumns)) {
            return implode(', ', $this->selectColumns);
        }
        // Fallback para * mas idealmente cada Model deve definir selectColumns
        return '*';
    }
}
