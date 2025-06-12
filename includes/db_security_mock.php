<?php

// Mock db.php for testing purposes

class MockMySQLi_Security {
    public $connect_error = null;
    public $mock_result_check = [];
    public $mock_stmt_execute_success = true;

    public function prepare($query) {
        $mock_stmt = new MockMySQLi_Stmt_Security();
        // Simulate SQL Injection vulnerability if a specific pattern is found
        if (strpos($query, "' OR 1=1--") !== false) {
            $mock_stmt->is_vulnerable = true;
        }
        return $mock_stmt;
    }
    public function close() { return true; }
}

class MockMySQLi_Stmt_Security {
    public $error = null;
    public $is_vulnerable = false;
    public $bind_params = [];

    public function bind_param($types, ...$params) {
        $this->bind_params = $params;
        return true;
    }

    public function execute() {
        // Simulate successful execution for prepared statements unless explicitly vulnerable
        if ($this->is_vulnerable) {
            return false; // Simulate failure for SQL injection attempt
        }
        return true;
    }

    public function get_result() {
        $mock_result = new MockMySQLi_Result_Security();
        // Simulate a successful login if the email is 'admin@example.com' and password is 'password'
        // This is a simplified mock for testing the login logic, not the password_verify itself
        if (isset($this->bind_params[0]) && $this->bind_params[0] === 'admin@example.com') {
            $mock_result->num_rows = 1;
            $mock_result->data = ['id' => 1, 'nome' => 'Admin', 'email' => 'admin@example.com', 'senha' => password_hash('password', PASSWORD_DEFAULT), 'ativado' => 1];
        }
        return $mock_result;
    }
    public function close() { return true; }
}

class MockMySQLi_Result_Security {
    public $num_rows = 0;
    public $data = null;

    public function fetch_assoc() {
        if ($this->num_rows > 0 && $this->data) {
            $temp_data = $this->data;
            $this->data = null; // Ensure it's only returned once
            return $temp_data;
        }
        return null;
    }
}

// Mock the enviarEmailAtivacao function if it exists in config.php
// Only declare if it doesn't already exist to avoid redeclaration errors
if (!function_exists('enviarEmailAtivacao')) {
    function enviarEmailAtivacao($email, $nome, $token) {
        return true; // Simulate successful email sending for tests
    }
}

?>
