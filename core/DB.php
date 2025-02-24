<?php

require_once __DIR__ . '/../config/Database.php';

class DB {
    private $pdo;
    private $table;
    private $where = [];
    private $bindings = [];
    private $limit;  // Variable para almacenar el límite

    public function __construct($table) {
        $this->pdo = Database::getInstance();
        $this->table = $table;
    }

    public static function table($table) {
        return new self($table);
    }

    // Ejecutar consultas SQL personalizadas
    public static function select($query, $bindings = []) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare($query);
        $stmt->execute($bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar condición WHERE
    public function where($column, $operator = '=', $value = null) {
        // Si solo se pasan dos parámetros, asumimos que el operador es '='
        if ($value === null) {
            $value = $operator;
            $operator = '=';  // El operador por defecto es '='
        }
        $this->where[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    // WHERE IN
    public function whereIn($column, array $values) {
        if (empty($values)) {
            return $this;
        }

        $placeholders = implode(", ", array_fill(0, count($values), "?"));
        $this->where[] = "$column IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    // ORDER BY
    public function orderBy($column, $direction = 'ASC') {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        $this->orderBy = " ORDER BY $column $direction";
        return $this;
    }

    // LIMIT
    public function limit($limit) {
        $this->limit = (int) $limit;  // Aseguramos que sea un número entero
        return $this;
    }

    // Obtener un solo registro
    public function first() {
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }
        $sql .= " LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener todos los registros
    public function get() {
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }
        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";  // Añadir LIMIT si se ha especificado
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Insertar un nuevo registro
    public function insert($data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $values = array_values($data);

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    // Actualizar un registro
    public function update($data) {
        if (empty($this->where)) {
            die("Error: Debes especificar un WHERE en update()");
        }

        $set = implode(", ", array_map(fn($key) => "$key = ?", array_keys($data)));
        $values = array_values($data);
        $values = array_merge($values, $this->bindings);

        $sql = "UPDATE {$this->table} SET {$set} WHERE " . implode(" AND ", $this->where);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    // Eliminar un registro
    public function delete() {
        if (empty($this->where)) {
            die("Error: Debes especificar un WHERE en delete()");
        }

        $sql = "DELETE FROM {$this->table} WHERE " . implode(" AND ", $this->where);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($this->bindings);
    }
}
