<?php
declare(strict_types=1);

namespace App\Database;

use Exception;
use RuntimeException;

final class QueryBuilder
{
    private Database $db;
    private string $table;
    private array $wheres = [];
    private array $bindings = [];
    private ?string $orderBy = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private array $joins = [];
    private array $groups = [];
    private array $havings = [];
    private ?string $cursorColumn = null;
    private mixed $cursorValue = null;
    private string $cursorDirection = 'ASC';

    public function __construct(Database $db, string $table)
    {
        if (!preg_match('/^[a-zA-Z0-9_.]+$/', $table)) {
            throw new \InvalidArgumentException("Invalid table name: {$table}");
        }

        $this->db = $db;
        $this->table = $table;
    }

    public function where(string $column, string $operator, $value): self
    {
        if ($value instanceof Raw) {
            $this->wheres[] = sprintf('%s %s %s', $column, $operator, $value->value);
            return $this;
        }

        $placeholder = sprintf(':%s%d', str_replace('.', '_', $column), count($this->bindings));
        $this->wheres[] =  sprintf('%s %s %s', $column, $operator, $placeholder);
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = sprintf('%s %s', $column, $direction);
        return $this;
    }


    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = max(0, $offset);
        return $this;
    }

    public function cursor(string $column, mixed $value, string $direction = 'ASC'): self
    {
        $this->cursorColumn = $column;
        $this->cursorValue = $value;
        $this->cursorDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $this;
    }

    public function get(array $columns = ['*']): array
    {
        if ($this->cursorColumn !== null && $this->offset !== null) {
            throw new RuntimeException('Cannot use offset with cursor pagination');
        }

        $sanitizedCols = array_map(function (string $col): string {
            if ($col === '*') {
                return '*';
            }

            if (!preg_match('/^[a-zA-Z0-9_.]+$/', $col)) {
                throw new \InvalidArgumentException("Invalid column name: {$col}");
            }

            return implode('.', array_map(fn($part) => '"' . $part . '"', explode('.', $col)));
        }, $columns);


        $sql = sprintf('SELECT %s FROM %s', implode(', ', $sanitizedCols), $this->table);

        if ($this->joins) {
            $sql .=  sprintf(' %s', implode(' ', $this->joins));
        }

        if ($this->cursorColumn !== null) {
            $operator = $this->cursorDirection === 'ASC' ? '>' : '<';

            $this->wheres[] =  sprintf('%s %s ?', $this->cursorColumn, $operator);
            $this->bindings[] = $this->cursorValue;

            $this->orderBy = sprintf('%s %s', $this->cursorColumn, $this->cursorDirection);
        }

        if ($this->wheres) {
            $sql .= sprintf(' WHERE %s', implode(' AND ', $this->wheres));
        }

        if ($this->groups) {
            $sql .= sprintf(' GROUP BY %s', implode(', ', $this->groups));
        }

        if ($this->havings) {
            $sql .= sprintf(' HAVING %s', implode(' AND ', $this->havings));
        }

        if ($this->orderBy) {
            $sql .= sprintf(' ORDER BY %s', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= sprintf(' LIMIT %d', $this->limit);
        }

        if ($this->offset !== null) {
            $sql .= sprintf(' OFFSET %d', $this->offset);
        }

        return $this->db->select($sql, $this->bindings);
    }

    public function first(array $columns = ['*']): ?array
    {
        $this->limit(1);
        $results = $this->get($columns);
        return $results[0] ?? null;
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);

        $placeholders = array_map(fn($col) => sprintf(':%s', $col), $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(',', $columns),
            implode(',', $placeholders)
        );

        $bindings = [];
        foreach ($data as $col => $val) {
            $bindings[':' . $col] = $val;
        }

        return $this->db->insert($sql, $bindings);
    }

    public function update(array $data): int
    {
        if (!$this->wheres) {
            throw new Exception("Update without WHERE is not allowed!");
        }

        $set = [];
        $bindings = $this->bindings;

        foreach ($data as $col => $val) {
            $placeholder = sprintf(':%s_upd', $col);
            $set[] = sprintf('%s = %s', $col, $placeholder);
            $bindings[$placeholder] = $val;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->table,
            implode(', ', $set),
            implode(' AND ', $this->wheres)
        );
        return $this->db->update($sql, $bindings);
    }

    public function delete(): int
    {
        if (!$this->wheres) {
            throw new Exception("Delete without WHERE is not allowed!");
        }

        $sql = sprintf('DELETE FROM %s WHERE %s', $this->table, implode(' AND ', $this->wheres));
        return $this->db->delete($sql, $this->bindings);
    }

    public function reset(): self
    {
        $this->wheres = [];
        $this->bindings = [];
        $this->orderBy = null;
        $this->limit = null;
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = sprintf('%s JOIN %s ON %s %s %s', strtoupper($type), $table, $first, $operator, $second);
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function crossJoin(string $table): self
    {
        $this->joins[] = sprintf('CROSS JOIN %s', $table);
        return $this;
    }

    public function whereIn(string $column, QueryBuilder $sub): self
    {
        [$sql, $bindings] = $sub->toSubquery();

        $this->wheres[] = sprintf('%s IN (%s)', $column, $sql);
        $this->bindings = array_merge($this->bindings, $bindings);

        return $this;
    }

    public function whereExists(QueryBuilder $sub): self
    {
        [$sql, $bindings] = $sub->toSubquery();

        $this->wheres[] = sprintf('EXISTS (%s)', $sql);
        $this->bindings = array_merge($this->bindings, $bindings);

        return $this;
    }

    private function toSubquery(): array
    {
        $sql = sprintf('SELECT * FROM %s', $this->table);

        if ($this->wheres) {
            $sql .= sprintf(' WHERE %s', implode(' AND ', $this->wheres));
        }

        return [$sql, $this->bindings];
    }

    private function aggregate(string $function, string $column)
    {
        $sql = sprintf('SELECT %s(%s) as aggregate FROM %s', $function, $column, $this->table);

        if ($this->joins) {
            $sql .= sprintf(' %s', implode(' ', $this->joins));
        }

        if ($this->wheres) {
            $sql .=  sprintf(' WHERE %s', implode(' AND ', $this->wheres));
        }

        return $this->db->select($sql, $this->bindings)[0]['aggregate'];
    }

    public function count(string $column = '*'): int
    {
        return (int)$this->aggregate('COUNT', $column);
    }

    public function sum(string $column): float
    {
        return (float)$this->aggregate('SUM', $column);
    }

    public function avg(string $column): float
    {
        return (float)$this->aggregate('AVG', $column);
    }

    public function min(string $column)
    {
        return $this->aggregate('MIN', $column);
    }

    public function max(string $column)
    {
        return $this->aggregate('MAX', $column);
    }

    public function groupBy(string ...$columns): self
    {
        $this->groups = $columns;
        return $this;
    }

    public function having(string $column, string $operator, $value): self
    {
        $ph = sprintf(':having_%d', count($this->bindings));
        $this->havings[] = sprintf('%s %s %s', $column, $operator, $ph);
        $this->bindings[$ph] = $value;
        return $this;
    }
}
