<?php
// =============================================================
//  resetar-senha.php - Definir nova senha
// =============================================================

session_start();

if (isset($_SESSION['usuario_logado'])) {
    header('Location: lista.php');
    exit();
}

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$mensagem = '';
$tipo = '';

if ($token === '') {
    $mensagem = 'Token inválido ou ausente.';
    $tipo = 'erro';
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = trim($_POST['nova_senha']);
    $confirma   = trim($_POST['confirma_senha']);

    if ($nova_senha === '' || strlen($nova_senha) < 4) {
        $mensagem = 'A senha deve ter pelo menos 4 caracteres.';
        $tipo = 'erro';
    } elseif ($nova_senha !== $confirma) {
        $mensagem = 'As senhas não coincidem.';
        $tipo = 'erro';
    } else {
        $conn = pg_connect("host=localhost dbname=sitephp user=postgres password=123456");

        if ($conn) {
            $res = pg_query_params($conn,
                "SELECT id FROM usuario 
                 WHERE reset_token = $1 
                 AND reset_expires > NOW()",
                array($token));

            if ($res && pg_num_rows($res) > 0) {
                // Atualiza a senha e limpa o token
                pg_query_params($conn,
                    "UPDATE usuario 
                     SET password = $1, 
                         reset_token = NULL, 
                         reset_expires = NULL 
                     WHERE reset_token = $2",
                    array($nova_senha, $token));

                $mensagem = 'Senha alterada com sucesso! Você já pode fazer login.';
                $tipo = 'sucesso';
            } else {
                $mensagem = 'Token inválido, expirado ou já utilizado.';
                $tipo = 'erro';
            }
            pg_close($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Definir Nova Senha</title>
    <style>
        body { font-family: Arial, sans-serif; background: #e9ecef; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; }
        .box { background:#fff; width:420px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.15); padding:40px 35px; }
        h1 { color:#2d5a96; text-align:center; margin-bottom:25px; }
        .msg-sucesso, .msg-erro { padding:12px; border-radius:4px; margin:15px 0; }
        .msg-sucesso { background:#e8f5e9; border:1px solid #81c784; color:#2e7d32; }
        .msg-erro { background:#fdecea; border:1px solid #e57373; color:#c62828; }
        input[type="password"] { width:100%; padding:12px; margin:10px 0; border:1px solid #ccc; border-radius:4px; font-size:15px; }
        button { width:100%; padding:14px; background:#3a6ea8; color:#fff; border:none; border-radius:4px; font-size:16px; cursor:pointer; margin-top:10px; }
        button:hover { background:#2d5a8c; }
    </style>
</head>
<body>
<div class="box">
    <h1>Nova Senha</h1>

    <?php if ($mensagem): ?>
        <div class="msg-<?php echo $tipo; ?>">
            <?php echo $mensagem; ?>
            <?php if ($tipo === 'sucesso'): ?>
                <br><br><a href="login.php" style="color:#2e7d32; font-weight:bold;">→ Ir para o Login</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <form method="post">
            <input type="password" name="nova_senha" placeholder="Nova senha" required>
            <input type="password" name="confirma_senha" placeholder="Confirme a nova senha" required>
            <button type="submit">Alterar Senha</button>
        </form>
    <?php endif; ?>

    <p style="text-align:center; margin-top:20px;">
        <a href="login.php">Voltar ao Login</a>
    </p>
</div>
</body>
</html>