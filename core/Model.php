<?php
/**
 * Base Model Class
 * 
 * All models extend from this base class
 */
class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    /**
     * Constructor - initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find all records
     *
     * @param string $orderBy Column to order by
     * @param string $direction Order direction (ASC or DESC)
     * @return array Array of records
     */
    public function findAll($orderBy = null, $direction = 'ASC')
    {
        $query = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $query .= " ORDER BY {$orderBy} {$direction}";
        }
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Find a record by ID
     *
     * @param int $id Record ID
     * @return array|false Record data or false if not found
     */
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetchOne($query, [$id]);
    }
    
    /**
     * Find records by a specific column value
     *
     * @param string $column Column name
     * @param mixed $value Column value
     * @return array Records matching the criteria
     */
    public function findBy($column, $value)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return $this->db->fetchAll($query, [$value]);
    }
    
    /**
     * Find one record by a specific column value
     *
     * @param string $column Column name
     * @param mixed $value Column value
     * @return array|false Record data or false if not found
     */
    public function findOneBy($column, $value)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return $this->db->fetchOne($query, [$value]);
    }
    
    /**
     * Create a new record
     *
     * @param array $data Record data
     * @return int|false The ID of the newly created record or false on failure
     */
    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update a record
     *
     * @param int $id Record ID
     * @param array $data Record data
     * @return int Number of affected rows
     */
    public function update($id, $data)
    {
        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Delete a record
     *
     * @param int $id Record ID
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Count all records
     *
     * @return int Number of records
     */
    public function count()
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->fetchOne($query);
        return $result['count'];
    }
    
    /**
     * Custom query with parameters
     *
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array Results
     */
    public function query($query, $params = [])
    {
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Paginate results
     *
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @param string $orderBy Column to order by
     * @param string $direction Order direction (ASC or DESC)
     * @return array Array with 'data' and 'pagination' info
     */
    public function paginate($page = 1, $perPage = 10, $orderBy = null, $direction = 'ASC')
    {
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $query = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $query .= " ORDER BY {$orderBy} {$direction}";
        }
        
        $query .= " LIMIT {$perPage} OFFSET {$offset}";
        
        // Get data for current page
        $data = $this->db->fetchAll($query);
        
        // Get total count
        $totalCount = $this->count();
        
        // Calculate pagination info
        $totalPages = ceil($totalCount / $perPage);
        
        return [
            'data' => $data,
            'pagination' => [
                'total' => $totalCount,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $totalPages,
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $totalCount)
            ]
        ];
    }
}