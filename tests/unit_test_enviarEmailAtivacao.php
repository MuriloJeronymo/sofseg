<?php

echo "--- Test script started ---\n";

// Define TEST_ENVIRONMENT to ensure mock is used
define("TEST_ENVIRONMENT", true);
echo "TEST_ENVIRONMENT defined.\n";

// Define BASE_PATH relative to the project root
define("BASE_PATH", __DIR__ . "/../");
echo "BASE_PATH defined: " . BASE_PATH . "\n";

// Include the config.php which now conditionally loads PHPMailer_mock.php
echo "Attempting to include config.php...\n";
require_once BASE_PATH . "config.php";
echo "config.php included successfully.\n";

function test_enviarEmailAtivacao_success() {
    echo "\n--- Running test_enviarEmailAtivacao_success ---\n";
    // Simulate a successful email sending scenario
    $email = "test@example.com";
    $nome = "Test User";
    $token = "mock_token_123";

    // Call the function to be tested
    $result = enviarEmailAtivacao($email, $nome, $token);

    if ($result === true) {
        echo "test_enviarEmailAtivacao_success: PASSED\n";
    } else {
        echo "test_enviarEmailAtivacao_success: FAILED (Result: " . var_export($result, true) . ")\n";
    }
    echo "--- Finished test_enviarEmailAtivacao_success ---\n\n";
}

// Execute the tests
test_enviarEmailAtivacao_success();

?>