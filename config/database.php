<?php
/**
 * UbugeniPalace - Database Configuration
 * Database connection and helper functions
 * Supports MySQL and PostgreSQL (Supabase)
 */

// Database configuration constants
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'ubumenyi_bwubugeni');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_PORT', getenv('DB_PORT') ?: '5432');

// Create database connection
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $port = DB_PORT;
    private $conn;
    private static $error_shown = false;

    // Get database connection
    public function getConnection() {
        $this->conn = null;
        
        try {
            // Determine driver based on host (Supabase usually contains 'supabase' or 'postgres')
            $is_postgres = (strpos($this->host, 'supabase.co') !== false || $this->port == '5432' || $this->port == '6543');
            
            if ($is_postgres) {
                // For PostgreSQL, ensure we use the correct driver
                $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            } else {
                $dsn = "mysql:host=" . $this->host . ";port=" . ($this->port ?: '3306') . ";dbname=" . $this->db_name;
            }
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Set attributes
            if (!$is_postgres) {
                $this->conn->exec("set names utf8");
            }
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            // In development, you might want to see the error, but only once
            if (getenv('ENVIRONMENT') === 'development' && !self::$error_shown) {
                echo "<div style='background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 1rem; margin: 1rem; border-radius: 0.375rem;'>";
                echo "<strong>Database Connection Error:</strong> " . htmlspecialchars($exception->getMessage());
                echo "<br><small>Tip: If you are seeing 'Network is unreachable', check if your Docker container has IPv6 enabled or try using the Supabase connection pooler host (port 6543).</small>";
                echo "</div>";
                self::$error_shown = true;
            }
        }
        
        return $this->conn;
    }
}

// Global database connection function
function getDBConnection() {
    $database = new Database();
    return $database->getConnection();
}

// Test database connection
function testConnection() {
    try {
        $conn = getDBConnection();
        if ($conn) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// Helper functions for common database operations
class DatabaseHelper {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    // Execute a prepared statement
    public function execute($query, $params = []) {
        try {
            if (!$this->conn) $this->conn = getDBConnection();
            if (!$this->conn) return false;
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    
    // Fetch all results
    public function fetchAll($query, $params = []) {
        try {
            if (!$this->conn) $this->conn = getDBConnection();
            if (!$this->conn) return [];
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }
    
    // Fetch single result
    public function fetchOne($query, $params = []) {
        try {
            if (!$this->conn) $this->conn = getDBConnection();
            if (!$this->conn) return false;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get last inserted ID
    public function lastInsertId() {
        if (!$this->conn) return false;
        return $this->conn->lastInsertId();
    }
    
    // Get row count
    public function rowCount($query, $params = []) {
        try {
            if (!$this->conn) $this->conn = getDBConnection();
            if (!$this->conn) return 0;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount(); // More reliable than fetchColumn for counts
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return 0;
        }
    }
}

// Create global database helper instance
$db = new DatabaseHelper();
?>
