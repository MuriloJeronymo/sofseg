<?php

// Temporarily set a global variable to indicate we are in a test environment
define("TEST_ENVIRONMENT", true);

// Include the security mock for the database connection
require_once __DIR__ . "/../includes/db_security_mock.php";

// Override the global $conn with our mock object
$conn = new MockMySQLi_Security();

function test_xss_in_registration() {
    global $conn; // Access the mock connection

    // Simulate an XSS attempt in the name field
    $malicious_name = "<script>alert(\'XSS\')</script>";
    $email = "xss_test@example.com";
    $telefone = "11987654321";
    $senha = "SenhaSegura123";

    $data = json_encode([
        "nome" => $malicious_name,
        "email" => $email,
        "telefone" => $telefone,
        "senha" => $senha
    ]);

    $_SERVER["REQUEST_METHOD"] = "POST";
    file_put_contents("php://input", $data);

    ob_start(); // Start output buffering
    // Include the registrar.php file, which will now use our mocked $conn
    include __DIR__ . "/../api/registrar.php";
    $output = ob_get_clean(); // Get the output and clean the buffer

    $response = json_decode($output, true);

    // Check if the malicious script is reflected in the response message
    // A real XSS vulnerability would involve this output being rendered in a browser
    // For this test, we check if the raw script is present in the JSON response
    if (isset($response["mensagem"]) && strpos($response["mensagem"], $malicious_name) !== false) {
        echo "test_xss_in_registration: FAILED (XSS Vulnerability Detected! Malicious script reflected in response.)\n";
    } else {
        echo "test_xss_in_registration: PASSED (Application appears resistant to XSS for this payload in the response.)\n";
    }
}

// Execute the test
test_xss_in_registration();

?>