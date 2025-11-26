<?php
namespace App\Core;

use PDO;
use InvalidArgumentException;

class QueryBuilder
{
    protected PDO $pdo;

    protected string $table = '';
    protected array $columns = ['*'];
    protected array $wheres = [];     // each: [type, sql, bindings[]]
    protected array $orders = [];     // each: [column, direction]
    protected string $groupBy = '';   // <--- MỚI: Chuỗi GROUP BY
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $joins = [];      // each: [type, table, first, operator, second]
    protected array $bindings = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ---------- FROM / TABLE ---------- */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Alias cho table(), giúp code đọc tự nhiên hơn khi query: ->from('booking')
     */
    public function from(string $table): self
    {
        return $this->table($table);
    }

    /* ---------- SELECT ---------- */
    public function select(string ...$columns): self
    {
        if ($columns) $this->columns = $columns;
        return $this;
    }

    /* ---------- JOINS ---------- */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = [$type, $table, $first, $operator, $second];
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

    /* ---------- WHERE ---------- */
    public function where(string $column, $operatorOrValue, $value = null): self
    {
        [$op, $val] = $this->normalizeWhereArgs($operatorOrValue, $value);
        $this->wheres[] = ['AND', "{$this->quoteIdent($column)} {$op} ?", [$val]];
        return $this;
    }

    public function orWhere(string $column, $operatorOrValue, $value = null): self
    {
        [$op, $val] = $this->normalizeWhereArgs($operatorOrValue, $value);
        $this->wheres[] = ['OR', "{$this->quoteIdent($column)} {$op} ?", [$val]];
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            // always false
            $this->wheres[] = ['AND', '1=0', []];
            return $this;
        }
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = ['AND', "{$this->quoteIdent($column)} IN ($placeholders)", array_values($values)];
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = ['AND', "{$this->quoteIdent($column)} IS NULL", []];
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = ['AND', "{$this->quoteIdent($column)} IS NOT NULL", []];
        return $this;
    }

    private function normalizeWhereArgs($operatorOrValue, $value): array
    {
        if ($value === null) {
            // where('id', 5)
            return ['=', $operatorOrValue];
        }
        $op = strtoupper(trim((string)$operatorOrValue));
        $valid = ['=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'];
        if (!in_array($op, $valid, true)) {
            throw new InvalidArgumentException("Invalid operator: {$op}");
        }
        return [$op, $value];
    }

    /* ---------- GROUP BY ---------- */
    
    /**
     * Thêm mệnh đề GROUP BY
     */
    public function groupBy(string ...$columns): self
    {
        // Quote từng cột để đảm bảo an toàn
        $cols = array_map([$this, 'quoteIdent'], $columns);
        $this->groupBy = ' GROUP BY ' . implode(', ', $cols);
        return $this;
    }

    /* ---------- ORDER / LIMIT ---------- */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = [$this->quoteIdent($column), $dir];
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = max(0, $limit);
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = max(0, $offset);
        return $this;
    }

    /* ---------- READ ---------- */
    public function get(): array
    {
        [$sql, $bindings] = $this->compileSelect();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $rows = $this->get();
        return $rows[0] ?? null;
    }

    public function count(): int
    {
        [$sql, $bindings] = $this->compileSelect(true);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return (int)$stmt->fetchColumn();
    }

    /* ---------- WRITE ---------- */
    public function insert(array $data): int
    {
        if (!$this->table) throw new InvalidArgumentException('No table selected');
        if (empty($data)) throw new InvalidArgumentException('Empty insert data');

        $cols = array_keys($data);
        $colSql = implode(', ', array_map([$this, 'quoteIdent'], $cols));
        $placeholders = implode(', ', array_fill(0, count($cols), '?'));

        $sql = "INSERT INTO {$this->quoteIdent($this->table)} ($colSql) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return (int)$this->pdo->lastInsertId();
    }

    public function update(array $data): int
    {
        if (!$this->table) throw new InvalidArgumentException('No table selected');
        if (empty($data)) return 0;

        $setParts = [];
        $setBindings = [];
        foreach ($data as $col => $val) {
            $setParts[] = $this->quoteIdent($col) . ' = ?';
            $setBindings[] = $val;
        }

        [$whereSql, $whereBindings] = $this->compileWhere();
        $sql = "UPDATE {$this->quoteIdent($this->table)} SET " . implode(', ', $setParts)
            . ($whereSql ? " WHERE $whereSql" : '');

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($setBindings, $whereBindings));
        return $stmt->rowCount();
    }

    public function delete(): int
    {
        if (!$this->table) throw new InvalidArgumentException('No table selected');

        [$whereSql, $whereBindings] = $this->compileWhere();
        $sql = "DELETE FROM {$this->quoteIdent($this->table)}" . ($whereSql ? " WHERE $whereSql" : '');
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($whereBindings);
        return $stmt->rowCount();
    }

    /* ---------- COMPILERS ---------- */
    protected function compileSelect(bool $isCount = false): array
    {
        if (!$this->table) throw new InvalidArgumentException('No table selected');

        $select = $isCount ? 'COUNT(*)' : implode(', ', array_map([$this, 'quoteAnyIdent'], $this->columns));
        $sql = "SELECT {$select} FROM {$this->quoteIdent($this->table)}";

        foreach ($this->joins as [$type, $table, $first, $operator, $second]) {
            $sql .= " {$type} JOIN {$this->quoteIdent($table)} ON {$this->quoteAnyIdent($first)} {$operator} {$this->quoteAnyIdent($second)}";
        }

        [$whereSql, $whereBindings] = $this->compileWhere();
        if ($whereSql) $sql .= " WHERE {$whereSql}";

        // --- GROUP BY (Sau WHERE, trước ORDER BY) ---
        if (!$isCount && $this->groupBy) {
            $sql .= $this->groupBy;
        }

        if (!$isCount && $this->orders) {
            $orderParts = array_map(fn($o) => $o[0] . ' ' . $o[1], $this->orders);
            $sql .= ' ORDER BY ' . implode(', ', $orderParts);
        }

        if (!$isCount && $this->limit !== null) $sql .= " LIMIT {$this->limit}";
        if (!$isCount && $this->offset !== null) $sql .= " OFFSET {$this->offset}";

        return [$sql, $whereBindings];
    }

    protected function compileWhere(): array
    {
        if (empty($this->wheres)) return ['', []];

        $sqlParts = [];
        $bindings = [];
        $first = true;

        foreach ($this->wheres as [$bool, $fragment, $binds]) {
            $prefix = $first ? '' : " {$bool} ";
            $sqlParts[] = $prefix . $fragment;
            $bindings = array_merge($bindings, $binds);
            $first = false;
        }

        return [implode('', $sqlParts), $bindings];
    }

    /* ---------- IDENT QUOTES ---------- */
    private function quoteIdent(string $ident): string
    {
        // simple protection for identifiers (table/column)
        // support "table.column"
        $parts = explode('.', $ident);
        $parts = array_map(function ($p) {
            $p = trim($p, "` \t\n\r\0\x0B");
            if ($p === '*') return '*';
            return '`' . str_replace('`', '``', $p) . '`';
        }, $parts);
        return implode('.', $parts);
    }

    private function quoteAnyIdent(string $expr): string
    {
        // allow raw functions like COUNT(*), or aliases "col AS alias"
        // keep it simple: if contains parentheses or space, return as-is
        if (preg_match('/[\(\)\s]/', $expr)) return $expr;
        return $this->quoteIdent($expr);
    }
}