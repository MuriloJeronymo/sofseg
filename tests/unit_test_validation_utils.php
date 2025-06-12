<?php

require_once __DIR__ . "/../includes/validation_utils.php";

function test_validatePasswordStrength_valid() {
    $result = validatePasswordStrength("SenhaSegura123");
    if ($result === true) {
        echo "test_validatePasswordStrength_valid: PASSED\n";
    } else {
        echo "test_validatePasswordStrength_valid: FAILED (Result: " . $result . ")\n";
    }
}

function test_validatePasswordStrength_too_short() {
    $result = validatePasswordStrength("curta");
    if ($result === "A senha deve ter no mínimo 8 caracteres.") {
        echo "test_validatePasswordStrength_too_short: PASSED\n";
    } else {
        echo "test_validatePasswordStrength_too_short: FAILED (Result: " . $result . ")\n";
    }
}

function test_validatePasswordStrength_no_number() {
    $result = validatePasswordStrength("Senhasemnumero");
    if ($result === "A senha deve conter pelo menos um número.") {
        echo "test_validatePasswordStrength_no_number: PASSED\n";
    } else {
        echo "test_validatePasswordStrength_no_number: FAILED (Result: " . $result . ")\n";
    }
}

// Executa os testes
test_validatePasswordStrength_valid();
test_validatePasswordStrength_too_short();
test_validatePasswordStrength_no_number();

?>