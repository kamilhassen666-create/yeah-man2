<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'goba_hospital');

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $conn;
    
    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->dbname,
                $this->user,
                $this->pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn = null;
    }
}

// Create database instance
$database = new Database();
$db = $database->connect();

// Function to execute queries safely
function executeQuery($query, $params = []) {
    global $db;
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
    }
}

// Function to get single row
function getRow($query, $params = []) {
    $stmt = executeQuery($query, $params);
    return $stmt ? $stmt->fetch() : false;
}

// Function to get multiple rows
function getRows($query, $params = []) {
    $stmt = executeQuery($query, $params);
    return $stmt ? $stmt->fetchAll() : false;
}

// Function to get row count
function getRowCount($query, $params = []) {
    $stmt = executeQuery($query, $params);
    return $stmt ? $stmt->rowCount() : 0;
}

// Function to get last insert ID
function getLastInsertId() {
    global $db;
    return $db->lastInsertId();
}
?>