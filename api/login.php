<?php
// api/login.php
require_once '../includes/verifica_sessao.php';
require_once '../includes/db.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit(0);
}

// ===================================================================
// --- FUNÇÃO DE DESCRIPTOGRAFIA ---
// ===================================================================
/**
 * Descriptografa dados que foram criptografados com CryptoJS AES.
 *
 * @param string|null $base64Data O dado criptografado em formato Base64.
 * @return string|false O dado descriptografado em texto plano, ou false em caso de falha.
 */
function decryptData($base64Data) {
    if (!$base64Data) {
        return false;
    }

    // A CHAVE (secretKey) E O VETOR DE INICIALIZAÇÃO (iv)
    // DEVEM SER IDÊNTICOS AOS USADOS NO SEU ARQUIVO JAVASCRIPT (criptografia.js).
    // A melhor prática é guardar estes valores em variáveis de ambiente no servidor, não diretamente no código.
    $secretKeyHex = '00112233445566778899aabbccddeeff00112233445566778899aabbccddeeff';
    $ivHex = 'fedcba9876543210fedcba9876543210';

    // Converte a chave e o IV de hexadecimal para binário
    $key = hex2bin($secretKeyHex);
    $iv = hex2bin($ivHex);

    // Decodifica o dado de Base64 para binário
    $cipherText = base64_decode($base64Data);
    if ($cipherText === false) {
        return false;
    }

    // Descriptografa os dados usando OpenSSL
    $decrypted = openssl_decrypt(
        $cipherText,
        'aes-256-cbc', // O mesmo método do CryptoJS
        $key,
        OPENSSL_RAW_DATA, // Importante para o padding correto (PKCS7)
        $iv
    );

    return $decrypted;
}
// --- FIM DA NOVA FUNÇÃO ---


// Check if in test environment to use mock db
if (defined("TEST_ENVIRONMENT") && TEST_ENVIRONMENT === true) {
    require_once __DIR__ . "/../includes/db_security_mock.php";
    $conn = new MockMySQLi_Security();
} else {
    require_once __DIR__ . "/../includes/db.php";
}

$response = ["status" => "erro", "mensagem" => "Ocorreu um erro desconhecido."];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $dados = json_decode(file_get_contents("php://input"));

    if (
        isset($dados->email) && !empty(trim($dados->email)) &&
        isset($dados->senha) && !empty($dados->senha)
    ) {

        // ===================================================================
        // --- MODIFICAÇÃO PRINCIPAL: DESCRIPTOGRAFAR DADOS ---
        // ===================================================================
        // Descriptografa o e-mail e a senha recebidos
        $email = decryptData(trim($dados->email));
        $senha = decryptData($dados->senha);

        // Se a descriptografia falhar para qualquer um dos campos, é uma requisição inválida.
        if ($email === false || $senha === false) {
            http_response_code(400); // Bad Request
            $response["status"] = "erro";
            $response["mensagem"] = "Falha ao processar os dados. A requisição pode estar malformada.";
            echo json_encode($response);
            exit;
        }
        // --- FIM DA MODIFICAÇÃO ---

        if (!isset($conn) || $conn->connect_error) {
            http_response_code(500);
            $response["mensagem"] = "Erro de conexão com o banco de dados: " . (isset($conn) ? $conn->connect_error : "Variável \$conn não definida");
            echo json_encode($response);
            exit;
        }

        // A partir daqui, o código continua o mesmo, pois as variáveis $email e $senha
        // agora contêm os valores em texto plano, prontos para serem usados.
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
            if ($usuario["ativado"] == 1) {
                // Conta está ativa, agora verificamos a senha (descriptografada) com o hash do banco
                if (password_verify($senha, $usuario["senha"])) {
                    // Login bem-sucedido!
                    http_response_code(200); // OK
                    $response["status"] = "sucesso";
                    $response["mensagem"] = "Login bem-sucedido!";

                    $userData = [
                        "id"    => $usuario["id"],
                        "nome"  => $usuario["nome"],
                        "email" => $usuario["email"]
                    ];

                    $response["usuario"] = $userData;
                    $_SESSION["usuario"] = $userData;
                    $_SESSION["ultima_atividade"] = time();

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
        $response["mensagem"] = "E-mail e senha (criptografados) são obrigatórios.";
    }
} else {
    http_response_code(405); // Method Not Allowed
    $response["status"] = "erro";
    $response["mensagem"] = "Método não permitido.";
}

echo json_encode($response);