<?php
// api/alugar_livro.php
require 'checar-sessao.php';
require '../includes/db.php';
require_once '../includes/verifica_sessao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_livro = $data['id'];
    $id_usuario = $_SESSION['usuario']['id'];

    // Lógica para alugar (INSERIR em 'alugueis' e talvez ATUALIZAR 'livros')
    $stmt = mysqli_prepare($conn, "INSERT INTO alugueis (id_livro, id_usuario, data_aluguel) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "ii", $id_livro, $id_usuario);
    $success = mysqli_stmt_execute($stmt);

    if ($success) {
        // Marcar livro como indisponível
        $stmt_update = mysqli_prepare($conn, "UPDATE livros SET disponibilidade = 0 WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "i", $id_livro);
        mysqli_stmt_execute($stmt_update);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível alugar o livro.']);
    }

    mysqli_stmt_close($stmt);
    if (isset($stmt_update)) {
        mysqli_stmt_close($stmt_update);
    }
}
?>
