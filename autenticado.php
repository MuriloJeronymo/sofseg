<?php
session_start();

// Verifica se o usuário está logado corretamente
if (!isset($_SESSION['usuario'])) {
    //$_SESSION['mensagem_erro'] = "Você precisa estar logado para acessar esta página.";
    header("Location: /ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html?erro_acesso=1");
    exit;
}

//Verifica tempo de inatividade
include './includes/verifica_sessao.php';

// Pegando dados do usuário
$nome = $_SESSION['usuario']['nome'];
$email = $_SESSION['usuario']['email'];
?>

<!-- http://softwareseguro.test/ProjetoSoftwareSeguro/autenticado.php -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Área do Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title">Bem-vindo, <?= htmlspecialchars($nome) ?>!</h2>
                <p class="card-text">Você está autenticado com o e-mail: <?= htmlspecialchars($email) ?></p>
                <a href="./autenticacao/php/dashboard.php" class="btn btn-primary mt-3">Ir para o Dashboard</a>
                <a href="./autenticacao/php/logout.php" class="btn btn-danger mt-3">Sair</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>

</html>