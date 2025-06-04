<?php
session_start();

$etapa = $_POST['etapa'] ?? 'codigo';

if ($etapa === 'codigo') {
    $codigoDigitado = $_POST['codigo'] ?? null;
    $codigoCorreto  = $_SESSION['codigo_recuperacao'] ?? null;

    if (!$codigoDigitado || !$codigoCorreto) {
        echo "<script>alert('Código não informado ou sessão expirada.'); window.location.href='http://softwareseguro.test/ProjetoSoftwareSeguro/autenticacao/html/recuperarSenha.html';</script>";
        exit;
    }

    if ($codigoDigitado == $codigoCorreto) {
        // Código correto → mostra campo para redefinir senha
        ?>
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <title>Nova Senha | BookShell</title>
            <link rel="stylesheet" type="text/css" href="/autenticacao/css/main.css">
            <link rel="stylesheet" type="text/css" href="/autenticacao/css/util.css">
        </head>
        <body class="bg-dark text-light">
            <div class="container-login100">
                <div class="wrap-login100">
                    <form action="verificarTrocaDeSenha.php" method="POST" class="login100-form validate-form">
                        <span class="login100-form-title py-3">Nova Senha</span>
                        <input type="hidden" name="etapa" value="senha">
                        <div class="wrap-input100">
                            <input class="input100" type="password" name="nova_senha" placeholder="Digite sua nova senha" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                        </div>
                        <div class="container-login100-form-btn">
                            <button class="login100-form-btn">Salvar nova senha</button>
                        </div>
                    </form>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "<script>alert('Código inválido.'); window.location.href='http://softwareseguro.test/ProjetoSoftwareSeguro/autenticacao/html/verificarTrocaDeSenha.html';</script>";
        exit;
    }
} elseif ($etapa === 'senha') {
    $novaSenha = $_POST['nova_senha'] ?? null;

    if (!$novaSenha) {
        echo "<script>alert('Senha não informada.'); window.location.href='http://softwareseguro.test/ProjetoSoftwareSeguro/autenticacao/html/recuperarSenha.html';</script>";
        exit;
    }

    // Simula salvar a nova senha
    echo "<script>alert('Senha redefinida com sucesso!'); window.location.href='http://softwareseguro.test/ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html';</script>";
}
