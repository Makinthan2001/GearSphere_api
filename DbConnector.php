<?php

/**
 * Database Connection Class
 * 
 * This class handles the database connection for the GearSphere application.
 * It uses PDO (PHP Data Objects) for secure database operations.
 * @package GearSphere-BackEnd
 */
class DBConnector {
    // Database configuration constants
    private $host = 'localhost';        // Database server hostname
    private $db = 'gearsphere';         // Database name
    private $user = 'root';             // Database username
    private $pass = '';                 // Database password (empty for local development)
    
    // PDO instance for database operations
    private $pdo;
    
    /**
     * Establishes a connection to the MySQL database
     * 
     * This method creates a PDO connection with proper error handling
     * and security configurations.
     * 
     * @return PDO Returns the PDO database connection object
     * @throws PDOException If connection fails
     */
    public function connect() {
        // Data Source Name - defines the database connection string
        $dsn = "mysql:host=$this->host;dbname=$this->db;";
        
        // PDO connection options for security and error handling
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,    // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Return associative arrays
            PDO::ATTR_EMULATE_PREPARES => false,            // Use real prepared statements
        ];
        
        try {
            // Create the PDO connection with specified options
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Re-throw exception with original message and code
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
        
        // Return the established connection
        return $this->pdo;
    }
}
