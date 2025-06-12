<?php

// Use statements must be at the top of the file.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Conditional loading of PHPMailer files based on TEST_ENVIRONMENT
// This block must be executed before the function definition if the classes are used within the function.
if (defined("TEST_ENVIRONMENT") && TEST_ENVIRONMENT === true) {
    // When in test environment, load the mock PHPMailer
    require_once __DIR__ . "/PHPMailer/src/PHPMailer_mock.php";
} else {
    // In production/normal environment, load the real PHPMailer files
    require_once __DIR__ . "/PHPMailer/src/PHPMailer.php";
    require_once __DIR__ . "/PHPMailer/src/SMTP.php";
    require_once __DIR__ . "/PHPMailer/src/Exception.php";
}

// Only declare enviarEmailAtivacao if it doesn\"t already exist
if (!function_exists("enviarEmailAtivacao")) {
    function enviarEmailAtivacao($email, $nome, $token)
    {
        // Instantiate the PHPMailer class from the correct namespace
        // The \use\ statements at the top ensure the correct class is used based on what was required_once
        $mail = new PHPMailer(true);

        try {
            // SMTP (Gmail ou outro)
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host       = "smtp.gmail.com";
            $mail->SMTPAuth   = true;
            $mail->Username   = "shellbooks3@gmail.com"; // Teu e-mail aqui
            $mail->Password   = "jdisctutnobvqata";       // Senha de app do Gmail
            $mail->SMTPSecure = "ssl"; // Ou "tls"
            $mail->Port       = 465;

            // E-mail de envio
            $mail->setFrom("shellbooks3@gmail.com", "ShellBooks");
            $mail->addAddress($email, $nome);

            $mail->isHTML(true);
            $mail->Subject = "Confirmacao de Cadastro";

            // link do laragon      http://SoftwareSeguro.test/ProjetoSoftwareSeguro/autenticacao/php/ativar.php

            $link = "http://SoftwareSeguro.test/ProjetoSoftwareSeguro/autenticacao/php/ativar.php?token=" . $token;  // adaptado para o Laragon
            $mail->Body = "
                <h3>Olá, $nome!</h3>
                <p>Confirme seu cadastro clicando no botão abaixo:</p>
                <a href=\"$link\" style=\"padding:10px 20px; background:#28a745; color:white; text-decoration:none;\">Ativar Conta</a>
                <p>Ou copie e cole no navegador: $link</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // echo "Erro no envio: " . $mail->ErrorInfo;
            return false;
        }
    }
}


