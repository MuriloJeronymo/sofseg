<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: /ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html?erro_acesso=1");
    exit();
}

$id_usuario = $_SESSION['usuario']['id'];
$id_livro = intval($_POST['id_livro']);

// Verifica se o livro já está favoritado (evita duplicatas)
$sql_check = "SELECT * FROM favoritos WHERE id_usuario = ? AND id_livro = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $id_usuario, $id_livro);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result) === 0) {
    // Insere favorito
    $sql_insert = "INSERT INTO favoritos (id_usuario, id_livro) VALUES (?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "ii", $id_usuario, $id_livro);
    mysqli_stmt_execute($stmt_insert);
}

header("Location: dashboard.php");
exit();
?>
