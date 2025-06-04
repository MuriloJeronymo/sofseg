<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: /ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html?erro_acesso=1");
    exit();
}

$aluguel_id = intval($_POST['aluguel_id']);

// Atualiza status do aluguel e data de devolução
$sql = "UPDATE alugueis SET devolvido = 1, data_devolucao = NOW() WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $aluguel_id);
mysqli_stmt_execute($stmt);

// Recupera o id do livro alugado
$query = "SELECT id_livro FROM alugueis WHERE id = ?";
$stmt_livro = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt_livro, "i", $aluguel_id);
mysqli_stmt_execute($stmt_livro);
$result = mysqli_stmt_get_result($stmt_livro);
$row = mysqli_fetch_assoc($result);
$id_livro = $row['id_livro'] ?? null;

// Torna o livro disponível novamente
if ($id_livro) {
    $sql_update_livro = "UPDATE livros SET disponibilidade = 1 WHERE id = ?";
    $stmt_livro_update = mysqli_prepare($conn, $sql_update_livro);
    mysqli_stmt_bind_param($stmt_livro_update, "i", $id_livro);
    mysqli_stmt_execute($stmt_livro_update);
}

header("Location: dashboard.php");
exit();
?>
