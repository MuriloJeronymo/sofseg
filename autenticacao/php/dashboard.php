<?php
session_start();

// Verifica se o usuário está autenticado corretamente
if (!isset($_SESSION['usuario'])) {
  //$_SESSION['mensagem_erro'] = "Você precisa estar logado para acessar o dashboard.";

  // Redireciona para a página de login com o parâmetro ?erro_acesso=1
  header("Location: /ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html?erro_acesso=1");

  exit();
}

//Verifica tempo de inatividade
include '../../includes/verifica_sessao.php';

// Dados do usuário autenticado
$id_usuario = $_SESSION['usuario']['id'];
$nome_usuario = $_SESSION['usuario']['nome'];

include '../../includes/db.php';
?>

<!-- http://softwareseguro.test/ProjetoSoftwareSeguro/autenticacao/php/dashboard.php -->

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard do Usuário</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ProjetoSoftwareSeguro/CSS/dashboard.css">

</head>

<body>
  <!-- Navbar com botão do offcanvas -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark d-md-none">
    <div class="container-fluid">
      <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral">
        ☰ Menu
      </button>
      <span class="navbar-text ms-auto">Olá, <?= htmlspecialchars($nome_usuario) ?>!</span>
    </div>
  </nav>

  <!-- Menu lateral Offcanvas para mobile -->
  <div class="offcanvas offcanvas-start d-md-none text-bg-dark sidebar" tabindex="-1" id="menuLateral">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offcanvasDarkLabel">Menu</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <hr>
    <div class="offcanvas-body">
      <a href="../../autenticado.php" class="d-block mb-2">⬅️ Voltar</a>
      <a href="editar_perfil.php" class="d-block mb-2">✏️ Editar Perfil</a>
      <a href="#catalogo" class="d-block mb-2">📚 Catálogo</a>
      <a href="#alugueis" class="d-block mb-2">📖 Meus Aluguéis</a>
      <a href="#favoritos" class="d-block mb-2">❤️ Favoritos</a>
      <a href="logout.php" class="d-block">🚪 Sair</a>
    </div>
  </div>

  <!-- Sidebar para desktop -->
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3 sidebar d-none d-md-block">
        <h4>Olá, <?= htmlspecialchars($nome_usuario) ?>!</h4>
        <a href="../../autenticado.php">⬅️ Voltar</a>
        <a href="editar_perfil.php">✏️ Editar Perfil</a>
        <a href="#catalogo">📚 Catálogo</a>
        <a href="#alugueis">📖 Meus Aluguéis</a>
        <a href="#favoritos">❤️ Favoritos</a>
        <a href="logout.php">🚪 Sair</a>
      </div>

      <div class="col-md-9 p-4">
        <!-- Catálogo -->
        <section class="row mx-0" id="catalogo">
          <h2>📚 Catálogo de Livros</h2>
          <?php
          $result = mysqli_query($conn, "SELECT * FROM livros WHERE disponibilidade = 1");
          echo '<div class="row">';
          while ($livro = mysqli_fetch_assoc($result)) {
            echo '<div class="col-12 col-md-6 col-lg-6">
                  <div class="card mb-3">
                    <img src="' . $livro['capa'] . '" class="card-img-top capa-livro" alt="Capa do Livro">
                    <div class="card-body">
                      <h5 class="card-title">' . $livro['titulo'] . '</h5>
                      <p class="card-text">' . $livro['autor'] . '</p>
                      <form method="post" action="alugar_livro.php">
                        <input type="hidden" name="id_livro" value="' . $livro['id'] . '">
                        <button class="btn btn-success px-3 my-2">Alugar</button>
                      </form>
                      <form method="post" action="favoritar_livro.php" class="mt-1">
                        <input type="hidden" name="id_livro" value="' . $livro['id'] . '">
                        <button class="btn btn-outline-warning">Favoritar</button>
                      </form>
                    </div>
                  </div>
                </div>';
          }
          echo '</div>';
          ?>
        </section>

        <hr>

        <!-- Aluguéis -->
        <section id="alugueis">
          <h2>📖 Meus Aluguéis</h2>
          <?php
          $query = "SELECT alugueis.id AS aluguel_id, livros.titulo, livros.autor
                  FROM alugueis
                  JOIN livros ON alugueis.id_livro = livros.id
                  WHERE alugueis.id_usuario = $id_usuario AND alugueis.devolvido = 0";
          $result = mysqli_query($conn, $query);
          echo '<table class="table"><thead><tr><th>Título</th><th>Autor</th><th>Ação</th></tr></thead><tbody>';
          while ($aluguel = mysqli_fetch_assoc($result)) {
            echo '<tr>
                  <td>' . $aluguel['titulo'] . '</td>
                  <td>' . $aluguel['autor'] . '</td>
                  <td>
                    <form method="post" action="devolver_livro.php">
                      <input type="hidden" name="aluguel_id" value="' . $aluguel['aluguel_id'] . '">
                      <button class="btn btn-primary">Confirmar Devolução</button>
                    </form>
                  </td>
                </tr>';
          }
          echo '</tbody></table>';
          ?>
        </section>

        <hr>

        <!-- Favoritos -->
        <section id="favoritos">
          <h2>❤️ Meus Favoritos</h2>
          <?php
          $query = "SELECT livros.* FROM favoritos
            JOIN livros ON favoritos.id_livro = livros.id
            WHERE favoritos.id_usuario = $id_usuario";
          $result = mysqli_query($conn, $query);
          echo '<div class="row">';
          while ($livro = mysqli_fetch_assoc($result)) {
            echo '<div class="col-md-3">
            <div class="card mb-3">
              <img src="' . $livro['capa'] . '" class="card-img-top capa-livro" alt="Capa">
              <div class="card-body">
                <h5 class="card-title">' . $livro['titulo'] . '</h5>
                <p class="card-text">' . $livro['autor'] . '</p>
                <form method="post" action="remover_favorito.php" class="mt-2">
                  <input type="hidden" name="id_livro" value="' . $livro['id'] . '">
                  <button class="btn btn-outline-danger btn-sm">Remover dos Favoritos</button>
                </form>
              </div>
            </div>
          </div>';
          }
          echo '</div>';
          ?>
        </section>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>