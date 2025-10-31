<?php
/**
 * Database connection configuration for Inclusify
 * Uses PDO for secure database operations
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'inclusify';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            die("Database connection failed. Please check your configuration.");
        }

        return $this->conn;
    }
}

// Global database connection
$database = new Database();
$pdo = $database->getConnection();
?>
