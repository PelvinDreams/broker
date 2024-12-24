<?php

class Sql {
    private $sqlConn;
    private $users;
    private $packages;
    private $transactions;
    private $active_plans;
    private $config;

    public function __construct($conn) {
        $this->sqlConn = $conn;
        $this->users = "users";
        $this->packages = "packages";
        $this->transactions = "transactions";
        $this->active_plans = "active_plans";
        $this->config = "config";
    }

    public function usersTable() {
        $query = "CREATE TABLE IF NOT EXISTS $this->users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            firstname VARCHAR(100) NOT NULL,
            lastname VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            status VARCHAR(100) DEFAULT 'Active',
            username VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(100) NULL,
            customerid VARCHAR(100),
            balance DECIMAL(10, 0) DEFAULT 0.0,
            token VARCHAR(100),
            sessionid VARCHAR(255),
            country VARCHAR(255) NULL
        )";

        if ($this->sqlConn->query($query) === TRUE) {
            return true;
        } else {
            echo "Error creating users table: " . $this->sqlConn->error;
            return false;
        }
    }

    public function configTable() {
        $query = "CREATE TABLE IF NOT EXISTS $this->config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            wallet VARCHAR(100) NULL,
            wallet_network VARCHAR(100) NULL,
        )";

        if ($this->sqlConn->query($query) === TRUE) {
            return true;
        } else {
            echo "Error creating config table: " . $this->sqlConn->error;
            return false;
        }
    }

    public function packagesTable() {
        $query = "CREATE TABLE IF NOT EXISTS $this->packages (
            package_id INT AUTO_INCREMENT PRIMARY KEY,
            percentage INT,
            name VARCHAR(100) NOT NULL,
            minimum INT DEFAULT 0,
            maximum INT DEFAULT 0,
            duration INT
        )";

        if ($this->sqlConn->query($query) === TRUE) {
            return true;
        } else {
            echo "Error creating packages table: " . $this->sqlConn->error;
            return false;
        }
    }

    public function transactionsTable() {
        $query = "CREATE TABLE IF NOT EXISTS $this->transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userid INT,
            FOREIGN KEY (userid) REFERENCES $this->users(id) ON DELETE CASCADE,
            amount VARCHAR(100) NOT NULL,
            type VARCHAR(100) NOT NULL,
            network VARCHAR(100) NULL,
            wallet VARCHAR(100) NULL,
            status VARCHAR(100) NOT NULL,
            date DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

        if ($this->sqlConn->query($query) === TRUE) {
            return true;
        } else {
            echo "Error creating transactions table: " . $this->sqlConn->error;
            return false;
        }
    }

    public function servicesTable() {
        $query = "CREATE TABLE IF NOT EXISTS $this->active_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userid INT,
            name VARCHAR(100) NULL,
            FOREIGN KEY (userid) REFERENCES $this->users(id)  ON DELETE CASCADE,
            transaction_id INT,
            FOREIGN KEY (transaction_id) REFERENCES $this->transactions(id),
            invested_amount INT,
            due_amount INT DEFAULT 0,
            percentage INT DEFAULT 0,
            purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            due_date DATETIME NOT NULL,
            status VARCHAR(100) NULL
        )";

        if ($this->sqlConn->query($query) === TRUE) {
            return true;
        } else {
            echo "Error creating services table: " . $this->sqlConn->error;
            return false;
        }
    }
}
