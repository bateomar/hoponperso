<?php
/**
 * Database Class
 * 
 * Handles database connection and operations
 */
class Database
{
    private static $instance = null;
    private $pdo;
    
    /**
     * Constructor - establishes database connection
     */
    private function __construct()
    {
        try {
            // Load configuration from environment variables or config file
            $host = getenv('PGHOST') ?: 'localhost';
            $dbname = getenv('PGDATABASE') ?: 'hopon';
            $username = getenv('PGUSER') ?: 'root';
            $password = getenv('PGPASSWORD') ?: '';
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            
            // Create PDO instance
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            echo "Successfully connected to MySQL database. Tables found: ";
            $stmt = $this->pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo implode(", ", $tables) . "\n";
            
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }
    
    /**
     * Get database instance (Singleton pattern)
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO instance
     *
     * @return PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }
    
    /**
     * Execute a query and return the statement
     *
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return PDOStatement
     */
    public function query($query, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Database Query Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Fetch a single row from a query
     *
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array|false Single row result or false if no result
     */
    public function fetchOne($query, $params = [])
    {
        $stmt = $this->query($query, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows from a query
     *
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array Array of result rows
     */
    public function fetchAll($query, $params = [])
    {
        $stmt = $this->query($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert data into a table
     *
     * @param string $table Table name
     * @param array $data Data to insert (column => value)
     * @return int|false Last insert ID or false on failure
     */
    public function insert($table, $data)
    {
        // Build column and placeholder lists
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        // Build query
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Database Insert Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update data in a table
     *
     * @param string $table Table name
     * @param array $data Data to update (column => value)
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function update($table, $data, $where, $params = [])
    {
        // Build SET clause
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
        }
        $setClause = implode(', ', $set);
        
        // Build query
        $query = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        // Combine data and where params
        $allParams = array_merge(array_values($data), $params);
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($allParams);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Database Update Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Delete data from a table
     *
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function delete($table, $where, $params = [])
    {
        // Build query
        $query = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Database Delete Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit a transaction
     */
    public function commit()
    {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public function rollback()
    {
        return $this->pdo->rollBack();
    }
}