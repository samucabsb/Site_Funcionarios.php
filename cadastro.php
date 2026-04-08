<?php
// =============================================================
//  cadastro.php - Cadastro de Funcionarios
//  Pagina protegida: somente usuarios logados
// =============================================================

session_start();

// Verificar login - redireciona se nao estiver logado
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: login.php');
    exit();
}

$mensagem = '';
$tipo_msg = ''; // 'sucesso' ou 'erro'

// Processar formulario de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Receber dados do formulario
    $nome     = trim($_POST['nome']);
    $cargo    = trim($_POST['cargo']);
    $email    = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $salario  = trim($_POST['salario']);
    $situacao = isset($_POST['situacao']) ? $_POST['situacao'] : 'Ativo';

    // Validacao basica
    if ($nome === '' || $cargo === '' || $email === '' || $salario === '') {
        $mensagem = 'Preencha todos os campos obrigatorios (Nome, Cargo, E-mail e Salario).';
        $tipo_msg = 'erro';
    } elseif (!is_numeric($salario) || floatval($salario) < 0) {
        $mensagem = 'O salario deve ser um numero valido.';
        $tipo_msg = 'erro';
    } else {

        // Conectar ao banco
        $conn = pg_connect("host=localhost dbname=sitephp user=postgres password=123456");

        if (!$conn) {
            $mensagem = 'Erro ao conectar com o banco de dados.';
            $tipo_msg = 'erro';
        } else {

            // Inserir usando pg_query_params (evita SQL Injection)
            $res = pg_query_params(
                $conn,
                "INSERT INTO funcionario (nome, email, cargo, salario, telefone, situacao)
                 VALUES ($1, $2, $3, $4, $5, $6)",
                array($nome, $email, $cargo, floatval($salario), $telefone, $situacao)
            );

            if ($res) {
                $mensagem = 'Funcionario cadastrado com sucesso!';
                $tipo_msg = 'sucesso';
                // Limpar variaveis apos sucesso
                $nome = $cargo = $email = $telefone = $salario = '';
                $situacao = 'Ativo';
            } else {
                $mensagem = 'Erro ao cadastrar. Tente novamente.';
                $tipo_msg = 'erro';
            }

            pg_close($conn);
        }
    }
}

// Manter valores no formulario em caso de erro
$v_nome     = isset($nome)     ? htmlspecialchars($nome)     : '';
$v_cargo    = isset($cargo)    ? htmlspecialchars($cargo)    : '';
$v_email    = isset($email)    ? htmlspecialchars($email)    : '';
$v_telefone = isset($telefone) ? htmlspecialchars($telefone) : '';
$v_salario  = isset($salario)  ? htmlspecialchars($salario)  : '';
$v_situacao = isset($situacao) ? $situacao : 'Ativo';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Funcionarios</title>
    <style>

        /* ── Reset ─────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background-color: #eaecf0;
            min-height: 100vh;
        }

        /* ── Navbar ──────────────────────────────────── */
/* Ajuste na Navbar original */
.navbar {
    background-color: #2d5fa0;
    height: 48px;
    width: 100%;
}

/* Nova classe para centralizar o conteúdo */
.navbar-container {
    max-width: 820px; /* Mesma largura da sua div .pagina */
    margin: 0 auto;
    padding: 0 18px;  /* Alinhamento lateral com o corpo */
    display: flex;
    align-items: center;
    height: 100%;
}

        /* Icone globo */
        .navbar .globo {
            display: inline-block;
            width: 26px; height: 26px;
            border: 2.5px solid #fff;
            border-radius: 50%;
            position: relative;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .navbar .globo::before {
            content: '';
            position: absolute;
            top: 50%; left: -2px; right: -2px;
            border-top: 2px solid #fff;
            transform: translateY(-50%);
        }

        .navbar .globo::after {
            content: '';
            position: absolute;
            top: -2px; bottom: -2px; left: 50%;
            transform: translateX(-50%);
            border-left: 2px solid #fff;
        }

        .navbar .marca {
            color: #fff;
            font-size: 15px;
            font-weight: bold;
            margin-right: auto;
        }

        .navbar a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 13px;
            padding: 0 14px;
            height: 48px;
            display: flex;
            align-items: center;
            border-bottom: 3px solid transparent;
        }

        .navbar a:hover {
            color: #fff;
            background-color: rgba(255,255,255,0.08);
        }

        .navbar a.ativo {
            color: #fff;
            border-bottom: 3px solid #fff;
            font-weight: bold;
        }

        .navbar .usuario {
            color: rgba(255,255,255,0.85);
            font-size: 13px;
            padding: 0 14px;
            height: 48px;
            display: flex;
            align-items: center;
            cursor: default;
        }

        /* ── Conteudo ─────────────────────────────────── */
        .pagina {
            max-width: 680px;
            margin: 30px auto;
            padding: 0 18px;
        }

        .titulo-pagina {
            font-size: 20px;
            font-weight: bold;
            color: #222;
            margin-bottom: 20px;
        }

        /* ── Painel (card) ─────────────────────────────── */
        .painel {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.10);
            overflow: hidden;
        }

        .painel-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e8;
            padding: 13px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            font-weight: bold;
            color: #333;
        }

        /* Icone boneco no header */
        .icone-header {
            width: 22px; height: 22px;
            background-color: #3a6ea8;
            border-radius: 50%;
            position: relative;
            flex-shrink: 0;
        }

        .icone-header::before {
            content: '';
            position: absolute;
            top: 3px; left: 50%;
            transform: translateX(-50%);
            width: 8px; height: 8px;
            background: #fff;
            border-radius: 50%;
        }

        .icone-header::after {
            content: '';
            position: absolute;
            bottom: 2px; left: 50%;
            transform: translateX(-50%);
            width: 14px; height: 8px;
            background: #fff;
            border-radius: 6px 6px 0 0;
        }

        .painel-corpo {
            padding: 22px 24px 18px;
        }

        /* ── Mensagens ─────────────────────────────────── */
        .msg-sucesso {
            background: #e8f5e9;
            border: 1px solid #81c784;
            color: #2e7d32;
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .msg-erro {
            background: #fdecea;
            border: 1px solid #e57373;
            color: #c62828;
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        /* ── Grid do formulario ────────────────────────── */
        .form-linha {
            display: flex;
            gap: 18px;
            margin-bottom: 14px;
        }

        .form-grupo {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .form-grupo label {
            font-size: 12px;
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }

        .form-grupo input[type="text"],
        .form-grupo input[type="email"],
        .form-grupo input[type="number"],
        .form-grupo select {
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 13px;
            color: #333;
            background: #fff;
            outline: none;
            height: 34px;
        }

        .form-grupo input:focus,
        .form-grupo select:focus {
            border-color: #3a6ea8;
        }

        .form-grupo input::placeholder {
            color: #aaa;
        }

        /* Campo ID (somente leitura) */
        .campo-id {
            background: #f8f8f8;
            color: #999;
            font-style: italic;
        }

        /* Situacao com radio */
        .situacao-grupo {
            display: flex;
            align-items: center;
            gap: 18px;
            padding-top: 4px;
        }

        .situacao-grupo label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #444;
            font-weight: normal;
            cursor: pointer;
        }

        .situacao-grupo input[type="radio"] {
            accent-color: #3a6ea8;
            width: 14px;
            height: 14px;
            cursor: pointer;
        }

        /* ── Botoes ─────────────────────────────────────── */
        .barra-botoes {
            display: flex;
            gap: 8px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #eee;
            justify-content: center;
        }

        .btn {
            padding: 9px 22px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-salvar {
            background-color: #3a6ea8;
            color: #fff;
        }

        .btn-salvar:hover { background-color: #2d5a8c; }

        .btn-limpar {
            background-color: #fff;
            color: #555;
            border: 1px solid #bbb;
        }

        .btn-limpar:hover { background-color: #f0f0f0; }

        .btn-voltar {
            background-color: #fff;
            color: #555;
            border: 1px solid #bbb;
        }

        .btn-voltar:hover { background-color: #f0f0f0; }

        .btn-fechar {
            background-color: #fff;
            color: #555;
            border: 1px solid #bbb;
        }

        .btn-fechar:hover { background-color: #f0f0f0; }

        /* ── Rodape ──────────────────────────────────────── */
        .rodape {
            text-align: center;
            padding: 18px;
            font-size: 11px;
            color: #aaa;
        }

    </style>
</head>
<body>

<!-- ── Navbar ────────────────────────────────── -->
<nav class="navbar">
    <div class="navbar-container">
        <div class="globo"></div>
        <span class="marca">Cadastro de Funcionários</span>
        <a href="lista.php">Início</a>
        <a href="lista.php" class="ativo">Listagem</a>
        <span class="usuario">
            Olá, <?php echo htmlspecialchars($_SESSION['usuario_logado']); ?> &#9660;
        </span>
    </div>
</nav>

<!-- ── Conteudo ───────────────────────────────── -->
<div class="pagina">

    <div class="titulo-pagina">Cadastro de Funcionarios</div>

    <div class="painel">

        <!-- Header do painel -->
        <div class="painel-header">
            <div class="icone-header"></div>
            Cadastro de Funcionarios
        </div>

        <!-- Corpo do painel -->
        <div class="painel-corpo">

            <!-- Mensagem de resultado -->
            <?php if ($mensagem !== ''): ?>
                <div class="msg-<?php echo $tipo_msg; ?>">
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form action="cadastro.php" method="post">

                <!-- Linha 1: ID + Nome/Cargo -->
                <div class="form-linha">
                    <div class="form-grupo">
                        <label>ID:</label>
                        <input type="text" class="campo-id" value="Automatico" readonly>
                    </div>
                    <div class="form-grupo">
                        <label>Nome</label>
                        <input type="text"
                               name="nome"
                               placeholder="Nome"
                               value="<?php echo $v_nome; ?>"
                               required>
                    </div>
                </div>

                <!-- Linha 1b: Cargo (select) -->
                <div class="form-linha">
                    <div class="form-grupo">
                        <label>Cargo</label>
                        <select name="cargo" required>
                            <option value="">Cargo</option>
                            <option value="Administrador" <?php echo ($v_cargo === 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="Gerente"       <?php echo ($v_cargo === 'Gerente')       ? 'selected' : ''; ?>>Gerente</option>
                            <option value="Assistente"    <?php echo ($v_cargo === 'Assistente')    ? 'selected' : ''; ?>>Assistente</option>
                            <option value="Analista"      <?php echo ($v_cargo === 'Analista')      ? 'selected' : ''; ?>>Analista</option>
                            <option value="Desenvolvedor" <?php echo ($v_cargo === 'Desenvolvedor') ? 'selected' : ''; ?>>Desenvolvedor</option>
                            <option value="Outro"         <?php echo ($v_cargo === 'Outro')         ? 'selected' : ''; ?>>Outro</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label>E-mail</label>
                        <input type="email"
                               name="email"
                               placeholder="E-mail"
                               value="<?php echo $v_email; ?>"
                               required>
                    </div>
                </div>

                <!-- Linha 2: Telefone + Situacao -->
                <div class="form-linha">
                    <div class="form-grupo">
                        <label>Telefone</label>
                        <input type="text"
                               name="telefone"
                               placeholder="Telefone"
                               value="<?php echo $v_telefone; ?>">
                    </div>
                    <div class="form-grupo">
                        <label>Situacao</label>
                        <div class="situacao-grupo">
                            <label>
                                <input type="radio" name="situacao" value="Ativo"
                                       <?php echo ($v_situacao !== 'Inativo') ? 'checked' : ''; ?>>
                                Ativo
                            </label>
                            <label>
                                <input type="radio" name="situacao" value="Inativo"
                                       <?php echo ($v_situacao === 'Inativo') ? 'checked' : ''; ?>>
                                Inativo
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Salario (oculto no layout mas obrigatorio) -->
                <div class="form-linha">
                    <div class="form-grupo">
                        <label>Salario (R$)</label>
                        <input type="number"
                               name="salario"
                               placeholder="0.00"
                               step="0.01"
                               min="0"
                               value="<?php echo $v_salario; ?>"
                               required>
                    </div>
                    <div class="form-grupo">
                        <!-- Espaco vazio para manter simetria -->
                    </div>
                </div>

                <!-- Botoes -->
                <div class="barra-botoes">
                    <input type="submit" class="btn btn-salvar" value="Salvar">
                    <input type="reset"  class="btn btn-limpar" value="Limpar">
                    <a href="lista.php"  class="btn btn-voltar">Voltar</a>
                    <a href="lista.php"  class="btn btn-fechar">Fechar</a>
                </div>

            </form>

        </div><!-- /painel-corpo -->
    </div><!-- /painel -->

    <div class="rodape">&copy; <?php echo date('Y'); ?> Sistema de Gestao de Funcionarios</div>

</div><!-- /pagina -->

</body>
</html>