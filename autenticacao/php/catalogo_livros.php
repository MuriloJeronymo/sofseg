<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    $_SESSION['erro'] = "Você precisa estar logado para acessar o catálogo.";
    header("Location: /ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html?erro_acesso=1");
    exit();
}

include '../../includes/db.php';
$id_usuario = $_SESSION['usuario']['id'];
$nome_usuario = $_SESSION['usuario']['nome'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Catálogo de Livros</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2 class="mb-4">📚 Catálogo de Livros Disponíveis</h2>

  <div class="row">
    <?php
    $result = mysqli_query($conn, "SELECT * FROM livros WHERE disponibilidade = 1");

    if (mysqli_num_rows($result) === 0) {
        echo '<p class="text-muted">Nenhum livro disponível no momento.</p>';
    }

    while ($livro = mysqli_fetch_assoc($result)) {
      echo '
        <div class="col-md-4">
          <div class="card mb-3">
            <img src="' . $livro['capa'] . '" class="card-img-top" alt="Capa do Livro">
            <div class="card-body">
              <h5 class="card-title">' . htmlspecialchars($livro['titulo']) . '</h5>
              <p class="card-text">Autor: ' . htmlspecialchars($livro['autor']) . '</p>
              
              <form method="post" action="alugar_livro.php">
                <input type="hidden" name="id_livro" value="' . $livro['id'] . '">
                <button type="submit" class="btn btn-primary w-100 mb-2">Alugar</button>
              </form>

              <form method="post" action="favoritar_livro.php">
                <input type="hidden" name="id_livro" value="' . $livro['id'] . '">
                <button type="submit" class="btn btn-outline-warning w-100">Favoritar</button>
              </form>
            </div>
          </div>
        </div>
      ';
    }
    ?>
  </div>

  <a href="dashboard.php" class="btn btn-secondary mt-4">⬅️ Voltar ao Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
