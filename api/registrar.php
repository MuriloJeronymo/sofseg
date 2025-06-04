<?php
// api/registrar.php

// Define o tipo de conteúdo da resposta como JSON
header("Content-Type: application/json; charset=UTF-8");
// Permite requisições de qualquer origem (para desenvolvimento - ajuste em produção)
header("Access-Control-Allow-Origin: *");
// Define os métodos HTTP permitidos
header("Access-Control-Allow-Methods: POST");
// Define os cabeçalhos permitidos
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../includes/db.php';
require_once '../config.php';

// Resposta padrão
$response = ["status" => "erro", "mensagem" => "Ocorreu um erro desconhecido."];

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega os dados enviados no corpo da requisição (JSON)
    $dados = json_decode(file_get_contents("php://input"));

    // Validação básica dos dados recebidos
    if (
        isset($dados->nome) && !empty(trim($dados->nome)) &&
        isset($dados->email) && filter_var($dados->email, FILTER_VALIDATE_EMAIL) &&
        isset($dados->telefone) && !empty(trim($dados->telefone)) &&
        isset($dados->senha) && !empty($dados->senha)
    ) {

        $nome = trim($dados->nome);
        $email = trim($dados->email);
        $telefone = trim($dados->telefone);
        $senha = password_hash($dados->senha, PASSWORD_DEFAULT);
        $token = md5(uniqid(rand(), true)); // token de ativação

        // Verificar se o e-mail já existe
        // Usando $conn que deve vir do seu db.php
        if (!isset($conn) || $conn->connect_error) {
            http_response_code(500); // Internal Server Error
            $response["mensagem"] = "Erro de conexão com o banco de dados.";
            echo json_encode($response);
            exit;
        }

        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        if ($stmt_check === false) {
            http_response_code(500);
            $response["mensagem"] = "Erro ao preparar a consulta de verificação: " . $conn->error;
            echo json_encode($response);
            exit;
        }
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            http_response_code(409); // Conflict
            $response["status"] = "erro";
            $response["mensagem"] = "Este e-mail já está cadastrado.";
        } else {
            // Inserir novo usuário
            $stmt_insert = $conn->prepare("INSERT INTO usuarios (email, telefone, nome, senha, token, ativado) VALUES (?, ?, ?, ?, ?, 0)");
            if ($stmt_insert === false) {
                http_response_code(500);
                $response["mensagem"] = "Erro ao preparar a consulta de inserção: " . $conn->error;
                echo json_encode($response);
                exit;
            }
            $stmt_insert->bind_param("sssss", $email, $telefone, $nome, $senha, $token);

            if ($stmt_insert->execute()) {

                if (function_exists('enviarEmailAtivacao')) {
                    if (enviarEmailAtivacao($email, $nome, $token)) {
                        http_response_code(201); // Created
                        $response["status"] = "sucesso";
                        $response["mensagem"] = "Usuário cadastrado com sucesso! Verifique seu e-mail para ativar a conta.";
                    } else {
                        http_response_code(207); // Multi-Status (usuário criado, mas email falhou)
                        $response["status"] = "parcial";
                        $response["mensagem"] = "Usuário cadastrado, mas houve um erro ao enviar o e-mail de ativação. Contate o suporte.";
                        // Você pode querer logar esse erro internamente
                    }
                } else {
                    http_response_code(201); // Created (se a função de email não existir, considera sucesso no cadastro)
                    $response["status"] = "sucesso";
                    $response["mensagem"] = "Usuário cadastrado com sucesso! (Função de envio de email não configurada)";
                }
            } else {
                http_response_code(500); // Internal Server Error
                $response["status"] = "erro";
                $response["mensagem"] = "Erro ao cadastrar usuário: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
        $conn->close();
    } else {
        http_response_code(400); // Bad Request
        $response["status"] = "erro";
        $response["mensagem"] = "Dados inválidos ou incompletos. Verifique os campos nome, email, telefone e senha.";
    }
} else {
    http_response_code(405); // Method Not Allowed
    $response["status"] = "erro";
    $response["mensagem"] = "Método não permitido. Use POST.";
}

echo json_encode($response);
