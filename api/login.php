<?php
// api/login.php
session_start();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

require_once '../includes/db.php';

$response = ["status" => "erro", "mensagem" => "Ocorreu um erro desconhecido."];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = json_decode(file_get_contents("php://input"));

    if (
        isset($dados->email) && !empty(trim($dados->email)) &&
        isset($dados->senha) && !empty($dados->senha)
    ) {

        $email = trim($dados->email);
        $senha = $dados->senha;

        if (!isset($conn) || $conn->connect_error) {
            http_response_code(500);
            $response["mensagem"] = "Erro de conexão com o banco de dados: " . (isset($conn) ? $conn->connect_error : 'Variável $conn não definida');
            echo json_encode($response);
            exit;
        }

        // Consulta ao banco, buscando também o campo 'ativado'
        $query = "SELECT id, nome, email, senha, ativado FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt === false) {
            http_response_code(500);
            $response["mensagem"] = "Erro ao preparar a consulta: " . mysqli_error($conn);
            echo json_encode($response);
            exit;
        }

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($usuario = mysqli_fetch_assoc($result)) {
            // Usuário encontrado, agora verificamos se está ATIVADO
            if ($usuario['ativado'] == 1) {
                // Conta está ativa, agora verificamos a senha
                if (password_verify($senha, $usuario['senha'])) {
                    // Login bem-sucedido!
                    http_response_code(200); // OK
                    $response["status"] = "sucesso";
                    $response["mensagem"] = "Login bem-sucedido!";

                    $userData = [
                        'id'    => $usuario['id'],
                        'nome'  => $usuario['nome'],
                        'email' => $usuario['email']
                    ];

                    $response["usuario"] = $userData;
                    $_SESSION['usuario'] = $userData;
                } else {
                    // Senha incorreta
                    http_response_code(401); // Unauthorized
                    $response["status"] = "erro";
                    $response["mensagem"] = "E-mail ou senha incorretos.";
                }
            } else {
                // Usuário encontrado, mas a conta NÃO está ativada
                http_response_code(403); // Forbidden
                $response["status"] = "erro";
                $response["mensagem"] = "Conta ainda não ativada. Verifique seu e-mail.";
            }
        } else {
            // Usuário não encontrado com este e-mail
            http_response_code(401); // Unauthorized (ou 404)
            $response["status"] = "erro";
            $response["mensagem"] = "E-mail ou senha incorretos.";
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    } else {
        http_response_code(400); // Bad Request
        $response["status"] = "erro";
        $response["mensagem"] = "E-mail e senha são obrigatórios.";
    }
} else {
    http_response_code(405); // Method Not Allowed
    $response["status"] = "erro";
    $response["mensagem"] = "Método não permitido.";
}

echo json_encode($response);
