<?php
session_start(); // Iniciar a sessão
require_once '../../includes/verifica_sessao.php'; // Garante que o usuário está logado
require_once '../../includes/db.php'; // Conexão com o banco (usa $conn com mysqli)

// Buscar dados atuais do usuário usando a variável de sessão correta
$user_id = $_SESSION['usuario']['id']; // Usar o ID do array 'usuario'

// Usar mysqli e $conn
$nome_atual = '';
$foto_atual = null;

$sql = "SELECT nome, foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();

    if ($usuario) {
        $nome_atual = htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8');
        $foto_atual = $usuario['foto_perfil'] ? htmlspecialchars($usuario['foto_perfil'], ENT_QUOTES, 'UTF-8') : null;
    } else {
        // Fallback para o nome da sessão se não encontrar no DB (pouco provável)
        $nome_atual = htmlspecialchars($_SESSION['usuario']['nome'], ENT_QUOTES, 'UTF-8');
    }
} else {
    // Tratar erro na preparação da query, se necessário
    // echo "Erro ao preparar a consulta: " . $conn->error;
    $nome_atual = htmlspecialchars($_SESSION['usuario']['nome'], ENT_QUOTES, 'UTF-8'); // Fallback
}


// Caminho base para exibir a foto (ajustar se necessário ou usar script intermediário)
$base_path_fotos = '../uploads/fotos_perfil/'; // Exemplo, pode precisar de ajuste ou script

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Sofseg</title>
    <!-- Adicionar links para CSS aqui, se necessário, para manter o estilo -->
    <link rel="stylesheet" href="../recursos/animate/animate.css"> <!-- Exemplo -->
    <style>
        /* Adicionar estilos básicos ou linkar CSS existente */
        body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type='text'], input[type='file'] { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        input[type='submit'] { background-color: #5cb85c; color: white; padding: 12px 25px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease; }
        input[type='submit']:hover { background-color: #4cae4c; }
        .foto-atual img { max-width: 150px; max-height: 150px; display: block; margin-bottom: 15px; border-radius: 50%; border: 3px solid #eee; }
        .foto-atual p { color: #777; font-style: italic; }
        .mensagem { padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; font-size: 16px; }
        .sucesso { background-color: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6; }
        .erro { background-color: #f2dede; color: #a94442; border: 1px solid #ebccd1; }
        a.voltar { display: inline-block; margin-top: 25px; text-decoration: none; color: #337ab7; font-size: 16px; }
        a.voltar:hover { text-decoration: underline; }
        small { color: #777; display: block; margin-top: -15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Perfil</h1>

        <?php 
        // Exibir mensagens de sucesso ou erro (se houver redirecionamento com parâmetros GET)
        if (isset($_GET['status'])) {
            $status = $_GET['status'];
            $msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8') : '';
            if ($status == 'sucesso') {
                echo '<p class="mensagem sucesso">Perfil atualizado com sucesso!</p>';
            } elseif ($status == 'erro') {
                echo '<p class="mensagem erro">Ocorreu um erro ao atualizar o perfil. Tente novamente.</p>';
            } elseif ($status == 'erro_upload') {
                echo '<p class="mensagem erro">Erro no upload da imagem: ' . $msg . '</p>';
            }
        }
        ?>

        <form action="processar_edicao_perfil.php" method="POST" enctype="multipart/form-data">
            
            <div class="foto-atual">
                <label>Foto Atual:</label>
                <?php 
                // Verifica se o arquivo existe antes de exibir - IMPORTANTE: basename() para segurança
                if ($foto_atual && file_exists($base_path_fotos . basename($foto_atual))):
                ?>
                    <img src="<?php echo $base_path_fotos . basename($foto_atual); ?>" alt="Foto de Perfil Atual">
                <?php else: ?>
                    <p>Nenhuma foto definida.</p>
                <?php endif; ?>
            </div>

            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?php echo $nome_atual; ?>" required>

            <label for="foto_perfil">Alterar Foto de Perfil (Opcional):</label>
            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg, image/png, image/gif">
            <small>Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB.</small>

            <input type="submit" value="Salvar Alterações">
        </form>

        <a href="dashboard.php" class="voltar">&laquo; Voltar para o Dashboard</a>
    </div>
</body>
</html>