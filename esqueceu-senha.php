<?php
// =============================================================
//  esqueceu-senha.php - Solicitar recuperação de senha
// =============================================================

session_start();

if (isset($_SESSION['usuario_logado'])) {
    header('Location: lista.php');
    exit();
}

$mensagem = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);

    if ($username === '') {
        $mensagem = 'Informe o nome de usuário.';
        $tipo = 'erro';
    } else {
        $conn = pg_connect("host=localhost dbname=sitephp user=postgres password=123456");

        if ($conn) {
            // Verifica se o usuário existe
            $res = pg_query_params($conn, 
                "SELECT id FROM usuario WHERE username = $1", 
                array($username));

            if ($res && pg_num_rows($res) > 0) {
                $token = bin2hex(random_bytes(32)); // Token seguro
                $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                pg_query_params($conn,
                    "UPDATE usuario 
                     SET reset_token = $1, reset_expires = $2 
                     WHERE username = $3",
                    array($token, $expires, $username));

                $link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/resetar-senha.php?token=" . $token;

                $mensagem = "Link de recuperação gerado com sucesso!<br><br>
                             <strong>Link:</strong> <a href='$link' target='_blank'>$link</a><br><br>
                             Este link expira em 30 minutos e pode ser usado apenas uma vez.";
                $tipo = 'sucesso';
            } else {
                $mensagem = 'Usuário não encontrado.';
                $tipo = 'erro';
            }
            pg_close($conn);
        } else {
            $mensagem = 'Erro ao conectar com o banco.';
            $tipo = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Esqueci minha senha</title>
    <style>
        body { font-family: Arial, sans-serif; background: #e9ecef; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin:0; }
        .box { background: #fff; width: 420px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); padding: 40px 35px; text-align: center; }
        h1 { color: #2d5a96; margin-bottom: 25px; }
        .msg-sucesso { background: #e8f5e9; border: 1px solid #81c784; color: #2e7d32; padding: 12px; border-radius: 4px; margin: 15px 0; text-align: left; }
        .msg-erro { background: #fdecea; border: 1px solid #e57373; color: #c62828; padding: 12px; border-radius: 4px; margin: 15px 0; text-align: left; }
        input[type="text"] { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; font-size: 15px; }
        button { width: 100%; padding: 14px; background: #3a6ea8; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; margin-top: 10px; }
        button:hover { background: #2d5a8c; }
        a { color: #3a6ea8; text-decoration: none; }
    </style>
</head>
<body>
<div class="box">
    <h1>Recuperar Senha</h1>
    
    <?php if ($mensagem): ?>
        <div class="msg-<?php echo $tipo; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Nome de usuário" required autofocus>
        <button type="submit">Gerar link de recuperação</button>
    </form>

    <p style="margin-top:20px;">
        <a href="login.php">← Voltar para o login</a>
    </p>
</div>
</body>
</html>