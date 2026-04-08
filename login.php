<?php
// =============================================================
//  login.php - Tela de Login
//  Valida usuario e senha no banco PostgreSQL
// =============================================================

session_start();

// Se ja estiver logado, redireciona para a lista
if (isset($_SESSION['usuario_logado'])) {
    header('Location: lista.php');
    exit();
}

$erro = ''; // Mensagem de erro

// Processar formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === '' || $password === '') {
        $erro = 'Preencha o usuario e a senha.';
    } else {

        // Conectar ao banco
        $conn = pg_connect("host=localhost dbname=sitephp user=postgres password=123456");

        if (!$conn) {
            $erro = 'Erro ao conectar com o banco de dados.';
        } else {

            // Buscar usuario com pg_query_params (evita SQL Injection)
            $res = pg_query_params(
                $conn,
                "SELECT id, username FROM usuario WHERE username = $1 AND password = $2",
                array($username, $password)
            );

            if ($res && pg_num_rows($res) > 0) {
                // Login valido - salvar sessao
                $linha = pg_fetch_assoc($res);
                $_SESSION['usuario_logado'] = $linha['username'];
                $_SESSION['usuario_id']     = $linha['id'];
                pg_close($conn);
                header('Location: lista.php');
                exit();
            } else {
                $erro = 'Usuario ou senha invalidos. Tente novamente.';
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
    <title>Login - Cadastro de Funcionarios</title>
    <style>

        /* ── Reset ────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background-color: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* ── Caixa central ────────────────────────── */
        .login-box {
            background-color: #fff;
            width: 430px;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.13);
            padding: 44px 42px 36px;
            text-align: center;
        }

        /* Icone do boneco no topo */
        .icone-topo {
            margin-bottom: 12px;
        }

        .icone-topo .circulo {
            display: inline-block;
            width: 72px;
            height: 72px;
            background-color: #3a6ea8;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
        }

        /* Cabeca do boneco */
        .icone-topo .circulo::before {
            content: '';
            position: absolute;
            top: 13px;
            left: 50%;
            transform: translateX(-50%);
            width: 22px;
            height: 22px;
            background-color: #fff;
            border-radius: 50%;
        }

        /* Corpo / terno do boneco */
        .icone-topo .circulo::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 44px;
            height: 34px;
            background-color: #fff;
            border-radius: 50% 50% 0 0;
        }

        /* Gravata */
        .icone-topo .circulo .gravata {
            position: absolute;
            top: 38px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 12px solid #3a6ea8;
            z-index: 2;
        }

        .login-box h1 {
            font-size: 22px;
            font-weight: bold;
            color: #2d5a96;
            margin-bottom: 28px;
        }

        /* ── Mensagem de erro ─────────────────────── */
        .msg-erro {
            background-color: #fdecea;
            border: 1px solid #e57373;
            color: #c62828;
            padding: 9px 14px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 18px;
            text-align: left;
        }

        /* ── Campos com icone ─────────────────────── */
        .input-grupo {
            display: flex;
            align-items: stretch;
            border: 1.5px solid #cdd2da;
            border-radius: 5px;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .input-grupo:focus-within {
            border-color: #3a6ea8;
        }

        /* Coluna do icone */
        .input-grupo .icone {
            width: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f4f6f9;
            border-right: 1.5px solid #cdd2da;
            flex-shrink: 0;
        }

        /* Icone boneco (usuario) */
        .icone-user {
            display: inline-block;
            width: 20px;
            height: 20px;
            position: relative;
        }

        .icone-user::before {
            content: '';
            position: absolute;
            top: 0; left: 50%;
            transform: translateX(-50%);
            width: 10px; height: 10px;
            background: #8a9bb5;
            border-radius: 50%;
        }

        .icone-user::after {
            content: '';
            position: absolute;
            bottom: 0; left: 50%;
            transform: translateX(-50%);
            width: 18px; height: 10px;
            background: #8a9bb5;
            border-radius: 8px 8px 0 0;
        }

        /* Icone cadeado (senha) */
        .icone-lock {
            display: inline-block;
            width: 18px;
            height: 20px;
            position: relative;
        }

        .icone-lock::before {
            content: '';
            position: absolute;
            top: 0; left: 50%;
            transform: translateX(-50%);
            width: 12px; height: 9px;
            border: 2.5px solid #8a9bb5;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
        }

        .icone-lock::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 18px; height: 12px;
            background: #8a9bb5;
            border-radius: 3px;
        }

        /* Input sem borda propria */
        .input-grupo input {
            flex: 1;
            padding: 13px 14px;
            border: none;
            font-size: 15px;
            color: #444;
            background: #fff;
            outline: none;
        }

        .input-grupo input::placeholder {
            color: #aab0bb;
        }

        /* ── Botao Entrar ──────────────────────────── */
        .btn-entrar {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to bottom, #4a80c4, #3264a8);
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 6px;
            letter-spacing: 0.5px;
        }

        .btn-entrar:hover {
            background: linear-gradient(to bottom, #3a70b4, #254e90);
        }

        /* ── Separador ────────────────────────────── */
        .separador {
            border: none;
            border-top: 1px solid #e0e3e8;
            margin: 22px 0 18px;
        }

        /* ── Link esqueci senha ───────────────────── */
        .link-senha {
            font-size: 14px;
            color: #555;
            text-decoration: underline;
        }

        .link-senha:hover {
            color: #3a6ea8;
        }

    </style>
</head>
<body>

<div class="login-box">

    <!-- Icone boneco no topo -->
    <div class="icone-topo">
        <div class="circulo">
            <span class="gravata"></span>
        </div>
    </div>

    <h1>Cadastro de Funcionarios</h1>

    <!-- Mensagem de erro -->
    <?php if ($erro !== ''): ?>
        <div class="msg-erro"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <!-- Formulario -->
    <form action="login.php" method="post">

        <!-- Campo Usuario -->
        <div class="input-grupo">
            <div class="icone">
                <span class="icone-user"></span>
            </div>
            <input type="text"
                   name="username"
                   placeholder="Usuario"
                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                   required>
        </div>

        <!-- Campo Senha -->
        <div class="input-grupo">
            <div class="icone">
                <span class="icone-lock"></span>
            </div>
            <input type="password"
                   name="password"
                   placeholder="Senha"
                   required>
        </div>

        <!-- Botao -->
        <input type="submit" class="btn-entrar" value="Entrar">

    </form>

    <hr class="separador">

    <a href="#" class="link-senha">Esqueci minha senha</a>

</div>

</body>
</html>