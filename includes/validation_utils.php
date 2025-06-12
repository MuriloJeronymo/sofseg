<?php

function validatePasswordStrength($password) {
    if (strlen($password) < 8) {
        return "A senha deve ter no mínimo 8 caracteres e 1 número.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "A senha deve conter pelo menos um número.";
    }
    return true; // Senha válida
}

?>
