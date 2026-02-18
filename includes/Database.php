<?php
/**
 * Database Connection Class - FIXED VERSION
 */

class Database
{
    private $driver = DB_DRIVER;
    private $host = DB_HOST;
    private $dbname = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $port = DB_PORT;

    private $conn;
    private $stmt;
    private $error;

    private static $instance = null;

    public function __construct()
    {
        if (self::$instance === null) {
            $this->connect();
            self::$instance = $this->conn;
        } else {
            $this->conn = self::$instance;
        }
    }

    private function connect()
    {
        if ($this->driver === 'pgsql') {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
        } else {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function query($sql)
    {
        try {
            $this->stmt = $this->conn->prepare($sql);
        } catch (PDOException $e) {
            throw new Exception("Query preparation failed: " . $e->getMessage());
        }
        return $this;
    }

    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        try {
            $this->stmt->bindValue($param, $value, $type);
        } catch (PDOException $e) {
            throw new Exception("Parameter binding failed: " . $e->getMessage());
        }

        return $this;
    }

    public function bindArray($params)
    {
        foreach ($params as $param => $value) {
            $this->bind(':' . ltrim($param, ':'), $value);
        }
        return $this;
    }

    public function execute()
    {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    public function fetch()
    {
        $this->execute();
        return $this->stmt->fetch();
    }

    public function fetchAll()
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->conn->lastInsertId();
    }

    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }

    public function commit()
    {
        return $this->conn->commit();
    }

    public function rollback()
    {
        return $this->conn->rollback();
    }
}
?>