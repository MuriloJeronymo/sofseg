<?php
// api/checar-sessao.php
require_once '../includes/verifica_sessao.php';

if (!isset($_SESSION['usuario'])) {
    http_response_code(401); // Não Autorizado
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit();
}
?>