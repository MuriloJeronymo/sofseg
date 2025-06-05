<?php
session_start(); // Iniciar a sessão
require_once '../../includes/verifica_sessao.php';
require_once '../../includes/db.php'; // Usa $conn com mysqli

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["usuario"])) { // Verificar a sessão correta
    $user_id = $_SESSION["usuario"]["id"]; // Usar o ID da sessão correta
    $nome = trim($_POST["nome"] ?? '');
    $foto_perfil_nova_db_path = null;
    $upload_error_msg = null;

    // 1. Validação do Nome
    if (empty($nome)) {
        header("Location: editar_perfil.php?status=erro&msg=" . urlencode("Nome não pode ser vazio."));
        exit;
    }

    // 2. Processamento do Upload da Imagem (se um arquivo foi enviado)
    if (isset($_FILES["foto_perfil"]) && $_FILES["foto_perfil"]["error"] == UPLOAD_ERR_OK && $_FILES["foto_perfil"]["size"] > 0) {
        
        $file = $_FILES["foto_perfil"];
        // Diretório de upload relativo à raiz do projeto (ajustar se necessário)
        // Importante: O caminho usado em move_uploaded_file deve ser relativo ao script atual ou absoluto.
        // O caminho armazenado no DB pode ser relativo à raiz ou absoluto, dependendo de como será exibido.
        $upload_dir_relative_to_script = "../uploads/fotos_perfil/"; 
        $upload_dir_for_db = "uploads/fotos_perfil/"; // Exemplo de caminho relativo à raiz para DB
        
        // Certificar que o diretório existe (embora já tenhamos criado antes)
        if (!is_dir($upload_dir_relative_to_script)) {
            mkdir($upload_dir_relative_to_script, 0755, true);
        }

        $max_file_size = 2 * 1024 * 1024; // 2MB
        $allowed_mime_types = ["image/jpeg", "image/png", "image/gif"];

        // a. Verificar tamanho
        if ($file["size"] > $max_file_size) {
            $upload_error_msg = "Arquivo muito grande (max 2MB).";
        } else {
            // b. Verificar tipo MIME real
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file["tmp_name"]);
            finfo_close($finfo);

            if (!in_array($mime_type, $allowed_mime_types)) {
                $upload_error_msg = "Tipo de arquivo inválido (permitido: JPG, PNG, GIF).";
            } else {
                // c. Gerar nome de arquivo seguro e único
                $original_filename = basename($file["name"]); // Previne LFI/Path Traversal
                $extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                
                // Validar extensão após verificação MIME
                $valid_extension = false;
                if ($mime_type == 'image/jpeg' && ($extension == 'jpg' || $extension == 'jpeg')) $valid_extension = true;
                elseif ($mime_type == 'image/png' && $extension == 'png') $valid_extension = true;
                elseif ($mime_type == 'image/gif' && $extension == 'gif') $valid_extension = true;
                
                if (!$valid_extension) {
                     $upload_error_msg = "Extensão de arquivo não corresponde ao tipo MIME.";
                     goto upload_error; 
                }

                $unique_filename = uniqid("user_" . $user_id . "_", true) . "." . $extension;
                $destination_path = $upload_dir_relative_to_script . $unique_filename;

                // d. Mover o arquivo
                if (move_uploaded_file($file["tmp_name"], $destination_path)) {
                    // Caminho a ser salvo no banco (relativo à raiz, por exemplo)
                    $foto_perfil_nova_db_path = $upload_dir_for_db . $unique_filename; 
                    
                    // Opcional: Remover foto antiga se existir
                    $sql_old_pic = "SELECT foto_perfil FROM usuarios WHERE id = ?";
                    $stmt_old = $conn->prepare($sql_old_pic);
                    if($stmt_old) {
                        $stmt_old->bind_param("i", $user_id);
                        $stmt_old->execute();
                        $result_old = $stmt_old->get_result();
                        $old_pic_path = $result_old->fetch_assoc()["foto_perfil"];
                        $stmt_old->close();

                        // Construir caminho completo para unlink (relativo ao script)
                        if ($old_pic_path) {
                             $old_pic_full_path = "../" . $old_pic_path; // Ajustar se $old_pic_path for absoluto
                             if (file_exists($old_pic_full_path)) {
                                 unlink($old_pic_full_path);
                             }
                        }
                    }

                } else {
                    $upload_error_msg = "Falha ao mover o arquivo enviado. Verifique permissões.";
                }
            }
        }
    }
    upload_error: // Label para goto em caso de erro

    // Se houve erro no upload, redireciona antes de tentar atualizar o DB
    if ($upload_error_msg) {
        header("Location: editar_perfil.php?status=erro_upload&msg=" . urlencode($upload_error_msg));
        exit;
    }

    // 3. Atualização no Banco de Dados usando mysqli
    $update_success = false;
    if ($foto_perfil_nova_db_path) {
        // Atualiza nome e foto
        $sql = "UPDATE usuarios SET nome = ?, foto_perfil = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssi", $nome, $foto_perfil_nova_db_path, $user_id);
            if ($stmt->execute()) {
                $update_success = true;
            }
            $stmt->close();
        }
    } else {
        // Atualiza apenas o nome
        $sql = "UPDATE usuarios SET nome = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $nome, $user_id);
            if ($stmt->execute()) {
                $update_success = true;
            }
            $stmt->close();
        }
    }

    if ($update_success) {
        // Atualizar nome na sessão também
        $_SESSION["usuario"]["nome"] = $nome; 

        // Redirecionar com sucesso
        header("Location: editar_perfil.php?status=sucesso");
        exit;
    } else {
        // Lidar com erro de banco de dados
        // Logar o erro $conn->error;
        header("Location: editar_perfil.php?status=erro");
        exit;
    }

} else {
    // Se não for POST ou não estiver logado, redirecionar para login
    // Ajustar o caminho do redirecionamento se necessário
    header("Location: ../../autenticacao/html/autenticacao.html"); 
    exit;
}

$conn->close(); // Fechar conexão mysqli
?>
