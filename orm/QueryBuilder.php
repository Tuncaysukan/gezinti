<?php

declare(strict_types=1);

namespace MiniORM;

use PDO;
use PDOStatement;

/**
 * Fluent SQL Query Builder
 * Zincirleme metodlarla SQL sorguları oluşturur
 */
class QueryBuilder
{
    protected PDO $pdo;
    protected string $table = '';
    protected array $select = ['*'];
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $joins = [];
    protected ?string $orderByColumn = null;
    protected string $orderByDirection = 'ASC';
    protected ?int $limitValue = null;
    protected ?int $offsetValue = null;
    protected array $with = []; // Eager loading için

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * Tablo adını belirle
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * SELECT kolonlarını belirle
     */
    public function select(array $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    // WHERE - 2 veya 3 parametre alabilir
    public function where(string $column, string $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'where',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];

        return $this;
    }

    /**
     * OR WHERE koşulu ekle
     */
    public function orWhere(string $column, string $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'where',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];

        return $this;
    }

    /**
     * JOIN ekle
     */
    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'INNER',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    /**
     * LEFT JOIN ekle
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'LEFT',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    /**
     * ORDER BY ekle
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderByColumn = $column;
        $this->orderByDirection = strtoupper($direction);
        return $this;
    }

    /**
     * LIMIT ekle
     */
    public function limit(int $limit): self
    {
        $this->limitValue = $limit;
        return $this;
    }

    /**
     * OFFSET ekle
     */
    public function offset(int $offset): self
    {
        $this->offsetValue = $offset;
        return $this;
    }

    /**
     * Eager loading için ilişki belirle
     */
    public function with(string|array $relations): self
    {
        $this->with = is_array($relations) ? $relations : [$relations];
        return $this;
    }

    // Tüm sonuçları getir
    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $stmt = $this->executeQuery($sql, $this->bindings);
        return $stmt->fetchAll();
    }

    // İlk sonucu getir
    public function first(): ?array
    {
        $this->limitValue = 1;
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }

    // ID ile bul
    public function find(int $id): ?array
    {
        return $this->where('id', '=', $id)->first();
    }

    /**
     * Kayıt sayısını al
     */
    public function count(): int
    {
        $originalSelect = $this->select;
        $this->select = ['COUNT(*) as count'];
        
        $sql = $this->buildSelectQuery();
        $stmt = $this->executeQuery($sql, $this->bindings);
        $result = $stmt->fetch();
        
        $this->select = $originalSelect;
        
        return (int)($result['count'] ?? 0);
    }

    /**
     * Kayıt var mı kontrol et
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * INSERT sorgusu çalıştır
     */
    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->executeQuery($sql, array_values($data));
        
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * UPDATE sorgusu çalıştır
     */
    public function update(array $data): int
    {
        $setParts = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "$column = ?";
            $values[] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s%s',
            $this->table,
            implode(', ', $setParts),
            $this->buildWhereClause()
        );

        // WHERE değerlerini ekle
        $values = array_merge($values, $this->bindings);

        $stmt = $this->executeQuery($sql, $values);
        return $stmt->rowCount();
    }

    /**
     * DELETE sorgusu çalıştır
     */
    public function delete(): int
    {
        $sql = sprintf(
            'DELETE FROM %s%s',
            $this->table,
            $this->buildWhereClause()
        );

        $stmt = $this->executeQuery($sql, $this->bindings);
        return $stmt->rowCount();
    }

    // SELECT sorgusunu oluştur
    protected function buildSelectQuery(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->select) . ' FROM ' . $this->table;

        // JOIN varsa ekle
        foreach ($this->joins as $join) {
            $sql .= ' ' . $join['type'] . ' JOIN ' . $join['table'] . 
                    ' ON ' . $join['first'] . ' ' . $join['operator'] . ' ' . $join['second'];
        }

        // WHERE
        $sql .= $this->buildWhereClause();

        // ORDER BY
        if ($this->orderByColumn) {
            $sql .= ' ORDER BY ' . $this->orderByColumn . ' ' . $this->orderByDirection;
        }

        // LIMIT
        if ($this->limitValue) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }

        // OFFSET
        if ($this->offsetValue) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }

        return $sql;
    }

    /**
     * WHERE clause oluştur
     */
    protected function buildWhereClause(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $whereParts = [];
        
        foreach ($this->wheres as $where) {
            $placeholder = '?';
            $this->bindings[] = $where['value'];
            
            $wherePart = sprintf(
                '%s %s %s',
                $where['column'],
                $where['operator'],
                $placeholder
            );

            if (empty($whereParts)) {
                $whereParts[] = $wherePart;
            } else {
                $whereParts[] = $where['boolean'] . ' ' . $wherePart;
            }
        }

        return ' WHERE ' . implode(' ', $whereParts);
    }

    /**
     * Sorguyu çalıştır (prepared statement ile)
     */
    protected function executeQuery(string $sql, array $bindings = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            return $stmt;
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                "SQL Hatası: " . $e->getMessage() . "\nQuery: " . $sql
            );
        }
    }

    /**
     * Raw SQL sorgusu çalıştır
     */
    public function raw(string $sql, array $bindings = []): PDOStatement
    {
        return $this->executeQuery($sql, $bindings);
    }

    /**
     * QueryBuilder'ı sıfırla
     */
    public function reset(): self
    {
        $this->select = ['*'];
        $this->wheres = [];
        $this->bindings = [];
        $this->joins = [];
        $this->orderByColumn = null;
        $this->orderByDirection = 'ASC';
        $this->limitValue = null;
        $this->offsetValue = null;
        $this->with = [];
        
        return $this;
    }

    /**
     * Eager loading için ilişkileri al
     */
    public function getWith(): array
    {
        return $this->with;
    }
}

