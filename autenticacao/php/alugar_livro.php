<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: /ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html?erro_acesso=1");
    exit();
}

$id_usuario = $_SESSION['usuario']['id'];
$id_livro = intval($_POST['id_livro']);

// 1. Insere o aluguel
$sql_insert = "INSERT INTO alugueis (id_usuario, id_livro, devolvido) VALUES (?, ?, 0)";
$stmt_insert = mysqli_prepare($conn, $sql_insert);
mysqli_stmt_bind_param($stmt_insert, "ii", $id_usuario, $id_livro);
mysqli_stmt_execute($stmt_insert);

// 2. Atualiza disponibilidade do livro
$sql_update = "UPDATE livros SET disponibilidade = 0 WHERE id = ?";
$stmt_update = mysqli_prepare($conn, $sql_update);
mysqli_stmt_bind_param($stmt_update, "i", $id_livro);
mysqli_stmt_execute($stmt_update);

header("Location: dashboard.php");
exit();
?>
