<?php
session_start();
session_unset();
session_destroy();
header("Location: /ProjetoSoftwareSeguro/autenticacao/html/autenticacao.html");
exit;
?>
