<?php
// api/registrar.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Check if in test environment to use mock db
if (defined("TEST_ENVIRONMENT") && TEST_ENVIRONMENT === true) {
    require_once __DIR__ . "/../includes/db_security_mock.php";
    $conn = new MockMySQLi_Security();
} else {
    require_once __DIR__ . "/../includes/db.php";
}

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../includes/validation_utils.php"; // Inclui o novo arquivo de utilitários

$response = ["status" => "erro", "mensagem" => "Ocorreu um erro desconhecido."];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $dados = json_decode(file_get_contents("php://input"));

    if (
        isset($dados->nome) && !empty(trim($dados->nome)) &&
        isset($dados->email) && filter_var($dados->email, FILTER_VALIDATE_EMAIL) &&
        isset($dados->telefone) && !empty(trim($dados->telefone)) &&
        isset($dados->senha) && !empty($dados->senha)
    ) {

        $nome = trim($dados->nome);
        $email = trim($dados->email);
        $telefone = trim($dados->telefone);
        $senha_raw = $dados->senha;

        // Validação da força da senha usando a nova função
        $password_validation_result = validatePasswordStrength($senha_raw);
        if ($password_validation_result !== true) {
            http_response_code(400); // Bad Request
            $response["mensagem"] = $password_validation_result;
            echo json_encode($response);
            exit; // Interrompe a execução do script
        }

        $senha_hash = password_hash($senha_raw, PASSWORD_DEFAULT);
        $token = md5(uniqid(rand(), true));

        if (!isset($conn) || $conn->connect_error) {
            http_response_code(500);
            $response["mensagem"] = "Erro de conexão com o banco de dados.";
            echo json_encode($response);
            exit;
        }

        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            http_response_code(409);
            $response["mensagem"] = "Este e-mail já está cadastrado.";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO usuarios (email, telefone, nome, senha, token, ativado) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt_insert->bind_param("sssss", $email, $telefone, $nome, $senha_hash, $token);

            if ($stmt_insert->execute()) {
                if (function_exists("enviarEmailAtivacao")) {
                    if (enviarEmailAtivacao($email, $nome, $token)) {
                        http_response_code(201);
                        $response = ["status" => "sucesso", "mensagem" => "Usuário cadastrado com sucesso! Verifique seu e-mail para ativar a conta."];
                    } else {
                        http_response_code(207);
                        $response = ["status" => "parcial", "mensagem" => "Usuário cadastrado, mas houve um erro ao enviar o e-mail de ativação."];
                    }
                } else {
                    http_response_code(201);
                    $response = ["status" => "sucesso", "mensagem" => "Usuário cadastrado com sucesso!"];
                }
            } else {
                http_response_code(500);
                $response["mensagem"] = "Erro ao cadastrar usuário: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
        $conn->close();
    } else {
        http_response_code(400);
        $response["mensagem"] = "Dados inválidos ou incompletos.";
    }
} else {
    http_response_code(405);
    $response["mensagem"] = "Método não permitido.";
}

echo json_encode($response);
