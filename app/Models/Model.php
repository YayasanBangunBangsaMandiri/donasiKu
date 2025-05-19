<?php
namespace App\Models;

/**
 * Model dasar yang akan diwarisi oleh semua model
 */
abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    
    public function __construct() {
        $this->db = \Database::getInstance();
    }
    
    /**
     * Mendapatkan semua data
     * 
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $order Arah pengurutan (ASC/DESC)
     * @return array
     */
    public function all($orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Mendapatkan data berdasarkan ID
     * 
     * @param int $id ID data
     * @return array|false
     */
    public function find($id) {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Mendapatkan data berdasarkan kondisi
     * 
     * @param string $column Nama kolom
     * @param mixed $value Nilai
     * @return array|false
     */
    public function findBy($column, $value) {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
    }
    
    /**
     * Mendapatkan semua data berdasarkan kondisi
     * 
     * @param string $column Nama kolom
     * @param mixed $value Nilai
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $order Arah pengurutan (ASC/DESC)
     * @return array
     */
    public function findAllBy($column, $value, $orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        return $this->db->fetchAll($sql, [$value]);
    }
    
    /**
     * Menyimpan data baru
     * 
     * @param array $data Data yang akan disimpan
     * @return int|false ID data baru atau false jika gagal
     */
    public function create($data) {
        // Filter data berdasarkan fillable
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        if (empty($filteredData)) {
            return false;
        }
        
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = implode(', ', array_fill(0, count($filteredData), '?'));
        
        $this->db->query(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
            array_values($filteredData)
        );
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Memperbarui data
     * 
     * @param int $id ID data
     * @param array $data Data yang akan diperbarui
     * @return bool
     */
    public function update($id, $data) {
        // Filter data berdasarkan fillable
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        if (empty($filteredData)) {
            return false;
        }
        
        $sets = [];
        foreach (array_keys($filteredData) as $column) {
            $sets[] = "{$column} = ?";
        }
        
        $setClause = implode(', ', $sets);
        $values = array_values($filteredData);
        $values[] = $id;
        
        $this->db->query(
            "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?",
            $values
        );
        
        return true;
    }
    
    /**
     * Menghapus data
     * 
     * @param int $id ID data
     * @return bool
     */
    public function delete($id) {
        $this->db->query(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
        
        return true;
    }
    
    /**
     * Menghitung jumlah data
     * 
     * @param string $column Kolom untuk kondisi (opsional)
     * @param mixed $value Nilai untuk kondisi (opsional)
     * @return int
     */
    public function count($column = null, $value = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        
        if ($column) {
            $sql .= " WHERE {$column} = ?";
            return $this->db->fetchColumn($sql, [$value]);
        }
        
        return $this->db->fetchColumn($sql);
    }
    
    /**
     * Mendapatkan data dengan pagination
     * 
     * @param int $page Halaman saat ini
     * @param int $perPage Jumlah data per halaman
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $order Arah pengurutan (ASC/DESC)
     * @return array
     */
    public function paginate($page = 1, $perPage = 10, $orderBy = null, $order = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $data = $this->db->fetchAll($sql);
        $total = $this->count();
        $lastPage = ceil($total / $perPage);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage
        ];
    }
} 