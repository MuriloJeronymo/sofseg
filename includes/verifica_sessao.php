<?php
// includes/verifica_sessao.php

// Define o tempo de vida do garbage collector da sessão em segundos (1 hora = 3600 segundos)
ini_set('session.gc_maxlifetime', 3600);

// Define o tempo de vida do cookie de sessão no navegador (0 = até o navegador fechar)
ini_set('session.cookie_lifetime', 0);

// Define parâmetros de segurança para o cookie da sessão
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // Em branco para o domínio atual
    'secure' => false, // Para HTTP
    'httponly' => true, // Prevenir acesso via JavaScript -> previne roubo de sessão por possível XSS
    'samesite' => 'Lax' // Proteção contra ataques CSRF
]);

// Inicia ou resume a sessão
session_start();

// Define o tempo máximo de inatividade em segundos
$tempo_inativo = 3600; // 1 hora
//$tempo_inativo = 10; // 5 segundos, para testar

// Verifica se o usuário está logado
if (isset($_SESSION['usuario'])) {
    
    // Verifica o tempo de inatividade
    if (isset($_SESSION['ultima_atividade']) && (time() - $_SESSION['ultima_atividade'] > $tempo_inativo)) {
        // Se inativo por mais de 1 hora, destrói a sessão
        session_unset();
        session_destroy();
    } else {
        // Se estiver ativo, atualiza o tempo da última atividade
        $_SESSION['ultima_atividade'] = time();
    }
}
?>