<?php
//session_start();

$tempo_maximo = 2 * 60 * 60; // 2 horas
//$tempo_maximo = 60 * 10; // 10 minutos

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario'])) {
    $_SESSION['mensagem_erro'] = "Você precisa estar logado para acessar esta página.";
    header("Location: /ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html");
    exit();
}

// Verifica inatividade pela última atividade armazenada em cookie
if (isset($_COOKIE['ultima_atividade'])) {
    $inatividade = time() - $_COOKIE['ultima_atividade'];
    if ($inatividade > $tempo_maximo) {
        // Sessão expirada
        session_unset();
        session_destroy();
        setcookie('ultima_atividade', '', time() - 3600, "/"); // Expira cookie
        header("Location: /ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html?expirado=1");
        exit();
    }
}

// Atualiza o tempo de atividade
setcookie('ultima_atividade', time(), time() + $tempo_maximo, "/");
?>
