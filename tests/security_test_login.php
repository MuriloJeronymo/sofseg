<?php

// Temporarily set a global variable to indicate we are in a test environment
define("TEST_ENVIRONMENT", true);

// Include the security mock for the database connection
require_once __DIR__ . "/../includes/db_security_mock.php";

// Override the global $conn with our mock object
$conn = new MockMySQLi_Security();

function test_sql_injection_login() {
    global $conn; // Access the mock connection

    // Simulate a SQL Injection attempt
    $malicious_email = "admin@example.com' OR 1=1-- ";
    $dummy_password = "anypassword";

    $data = json_encode([
        "email" => $malicious_email,
        "senha" => $dummy_password
    ]);

    $_SERVER["REQUEST_METHOD"] = "POST";
    file_put_contents("php://input", $data);

    ob_start(); // Start output buffering
    // Include the login.php file, which will now use our mocked $conn
    include __DIR__ . "/../api/login.php";
    $output = ob_get_clean(); // Get the output and clean the buffer

    $response = json_decode($output, true);

    // Check if the login was successful, which indicates a vulnerability
    if (isset($response["status"]) && $response["status"] === "sucesso") {
        echo "test_sql_injection_login: FAILED (SQL Injection Vulnerability Detected!)\n";
    } else {
        echo "test_sql_injection_login: PASSED (Application appears resistant to SQL Injection for this payload.)\n";
    }
}

// Execute the test
test_sql_injection_login();

?>

