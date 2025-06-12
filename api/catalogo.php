<?php
// api/catalogo.php
require '../includes/db.php'; // Ou seu include de conexão
require_once '../includes/verifica_sessao.php';

$stmt = $conn->prepare("SELECT id, titulo, autor, capa FROM livros WHERE disponibilidade = 1");
$stmt->execute();
$result = $stmt->get_result();
$livros = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($livros);
?>
