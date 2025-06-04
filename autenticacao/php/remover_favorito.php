<?php
session_start();
include_once '../../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: /ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html?erro_acesso=1");
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$id_livro = $_POST['id_livro'] ?? null;

if ($id_livro) {
    $stmt = $conn->prepare("DELETE FROM favoritos WHERE id_usuario = ? AND id_livro = ?");
    $stmt->bind_param("ii", $usuario_id, $id_livro);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../php/dashboard.php");
exit;
?>
