<?php
// =============================================================
//  excluir.php - Excluir funcionario
//  Pagina protegida, redireciona para lista apos exclusao
// =============================================================

session_start();

// Verificar login
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $conn = pg_connect("host=localhost dbname=sitephp user=postgres password=123456");

    if ($conn) {
        // Excluir com pg_query_params (evita SQL Injection)
        pg_query_params($conn, "DELETE FROM funcionario WHERE id = $1", array($id));
        pg_close($conn);
    }
}

// Voltar para a lista
header('Location: lista.php');
exit();
?>