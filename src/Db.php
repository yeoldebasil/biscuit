<?php

declare (strict_types = 1);

namespace Biscuit;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class Db
{
    private static ?PDO $pdo = null;
    private static array $config;
    private bool $debug = false;
    private Closure $onerr;

    #$query = '';
    public array $bindings = [];

    public static function kick(...$args)
    {
        assert(! array_is_list($args));
        assert(isset($args['db_name']));

        if (isset(static::$pdo)) {
            throw new \RuntimeException(
                'Already connected to database server'
            );
        }

        if (! extension_loaded('pdo_mysql')) {
            throw new \ErrorException(
                'pdo_mysql extension is not loaded'
            );
        }

        $defaults = [
            'host'       => '127.0.0.1',
            'port'       => 3306,
            'charset'    => 'utf8mb4',
            'timeout'    => 5,
            'persistent' => false,
            'ssl'        => null,
            // 'log_file'   => null,
        ];

        $config     = array_merge($defaults, $args);
        $attributes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE {$config['charset']}_unicode_ci, sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'",
            PDO::ATTR_TIMEOUT    => $config['timeout'],
            PDO::ATTR_PERSISTENT => $config['persistent'],
        ];

        if (! blank($config['ssl'])) {
            $attributes[PDO::MYSQL_ATTR_SSL_KEY]  = $config['ssl']['key'] ?? null;
            $attributes[PDO::MYSQL_ATTR_SSL_CERT] = $config['ssl']['cert'] ?? null;
            $attributes[PDO::MYSQL_ATTR_SSL_CA]   = $config['ssl']['ca'] ?? null;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['db_name'],
            $config['charset']
        );

        self::$config = $config;

        try {
            self::$pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $attributes
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Unable connect to database server');
        }
    }

    /**
     * Выполнит запрос, вернет PDOStatement
     */
    public static function read($sql, array $binds = []): array
    {
        $pdo = self::$pdo;

        if ($statement = $pdo->prepare($sql)) {
            if (! blank($binds)) {
                foreach ($pdo->bindings as $name => $value) {
                    $statement->bindValue($name, $value);
                }
            }

            $statement->execute();
        }

        return $statement->fetch();
    }

    /**
     * Возвращает несколько колонок, либо пустой массив
     */
    public function readAll(array $values = []): array
    {
        $all = $this->exec($sql, $values)->fetchAll();

        if (! $all) {
            return [];
        }

        return $this->wrap_all($all);
    }

    /**
     * Возвращает одну колонку, либо пустой массив
     */
    public function readRow(SQL $sql, array $values = []): array
    {
        $row = $this->exec($sql, $values)->fetch();

        if (! $row) {
            return [];
        }

        return $this->wrap_row($row);
    }

    /**
     * Возвращает одно поле
     */
    public function readField(SQL $sql, array $values = []): mixed
    {
        $row = $this->row($sql, $values);

        if ($row === []) {
            return null;
        }

        $keys   = array_keys($row);
        $column = $keys[0];

        return $this->wrap_field($column, $row[$column]);
    }

    /**
     * (Обработчик для колонки)
     * Полностью убирает пустые ключи из массива
     */
    private function wrap_row(array $row): array
    {
        foreach ($row as $field => $value) {
            if (blank($value)) {
                unset($row[$field]);
            }

            $row[$field] = $this->wrap_field($field, $value);
        }

        return $row;
    }
    /**
     * (Обработчик для нескольких колонок)
     */
    private function wrap_all(array $all): array
    {
        foreach ($all as $row => $value) {
            $all[$row] = $this->wrap_row($value);
        }

        return $all;
    }

    /**
     * (Обработчик для поля)
     * Преобразовывает дату/время в виде строки в unixtime
     */
    private function wrap_field(string $name, mixed $value): mixed
    {
        if (ends_with('_at', $name)) {
            return strtotime($value);
        }

        return $value;
    }

    public function select(array | string $columns, string $table): self
    {
        if (is_array($columns)) {
            $columns = '`' . implode("`, `", $columns) . '`';
        }

        $this->query .= "SELECT {$columns} FROM {$table} ";
        return $this;
    }

    public function insert(array $columns, string $table): self
    {
        $bindings = [];

        foreach ($columns as $column) {
            $bindings[] = ":{$column}";
        }

        $columns  = '`' . implode("`, `", $columns) . '`';
        $bindings = implode(", ", $bindings);

        $this->query .= "INSERT INTO {$table} ({$columns}) VALUES ({$bindings})";
        return $this;
    }

    public function delete(string $table): self
    {
        $this->query .= "DELETE FROM {$table} ";
        return $this;
    }

    public function update(array $columns, string $table): self
    {
        $bindings = [];

        foreach ($columns as $column) {
            $bindings[] = "`{$column}` = :{$column}";
        }

        $bindings = implode(", ", $bindings);

        $this->query .= "UPDATE {$table} SET {$bindings} ";
        return $this;
    }

    public function where(string $condition): self
    {
        $this->query .= "WHERE {$condition} ";
        return $this;
    }

    public function  or (string $condition): self
    {
        $this->query .= "OR {$condition} ";
        return $this;
    }

    public function  and (string $condition): self
    {
        $this->query .= "AND {$condition} ";
        return $this;
    }

    public function limit(int $limit, int $offset = 0): self
    {
        $this->query .= "LIMIT {$limit} ";

        if ($offset > 0) {
            $this->query .= "OFFSET {$offset} ";
        }

        return $this;
    }

    public function orderBy(string $column): self
    {
        $this->query .= "ORDER BY {$column} ";
        return $this;
    }

    public function desc(): self
    {
        $this->query .= "DESC ";
        return $this;
    }

}
