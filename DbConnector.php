<?php

    class DBConnector {
        private $host = 'localhost';
        private $db = 'gearsphere';
        private $user = 'root';
        private $pass = '';
        
        private $pdo;
        
        public function connect() {
            $dsn = "mysql:host=$this->host;dbname=$this->db;";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            try {
                $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
            
            return $this->pdo;
        }
    }
    