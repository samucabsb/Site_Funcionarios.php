<?php
// =============================================================
//  lista.php - Listagem de Funcionarios
//  Pagina protegida: somente usuarios logados
// =============================================================

session_start();

// Verificar login
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: login.php');
    exit();
}

// ── Paginacao ─────────────────────────────────────────────────
$por_pagina  = 5;                                          // registros por pagina
$pagina_atual = isset($_GET['pagina']) && is_numeric($_GET['pagina'])
               ? (int)$_GET['pagina'] : 1;
if ($pagina_atual < 1) $pagina_atual = 1;
$offset = ($pagina_atual - 1) * $por_pagina;

// ── Busca ─────────────────────────────────────────────────────
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// ── Conectar ao banco ─────────────────────────────────────────
$conn = pg_connect("host=localhost dbname=sitephp user=postgres password=123456");
$erro_conn = '';
if (!$conn) {
    $erro_conn = 'Erro ao conectar com o banco de dados.';
}

// ── Buscar total de registros ─────────────────────────────────
$total_registros = 0;
if ($conn) {
    if ($busca !== '') {
        $res_total = pg_query_params(
            $conn,
            "SELECT COUNT(*) FROM funcionario
              WHERE nome ILIKE $1 OR cargo ILIKE $1 OR email ILIKE $1",
            array('%' . $busca . '%')
        );
    } else {
        $res_total = pg_query($conn, "SELECT COUNT(*) FROM funcionario");
    }
    if ($res_total) {
        $row = pg_fetch_row($res_total);
        $total_registros = (int)$row[0];
    }
}

$total_paginas = ($total_registros > 0) ? ceil($total_registros / $por_pagina) : 1;
if ($pagina_atual > $total_paginas) $pagina_atual = $total_paginas;

// ── Buscar funcionarios com paginacao ─────────────────────────
$funcionarios = array();
if ($conn) {
    if ($busca !== '') {
        $res = pg_query_params(
            $conn,
            "SELECT id, nome, cargo, email, situacao
               FROM funcionario
              WHERE nome ILIKE $1 OR cargo ILIKE $1 OR email ILIKE $1
              ORDER BY nome ASC
              LIMIT $2 OFFSET $3",
            array('%' . $busca . '%', $por_pagina, $offset)
        );
    } else {
        $res = pg_query_params(
            $conn,
            "SELECT id, nome, cargo, email, situacao
               FROM funcionario
              ORDER BY nome ASC
              LIMIT $1 OFFSET $2",
            array($por_pagina, $offset)
        );
    }
    if ($res) {
        while ($linha = pg_fetch_assoc($res)) {
            $funcionarios[] = $linha;
        }
    }
    pg_close($conn);
}

// Parametro de busca para os links de paginacao
$busca_param = ($busca !== '') ? '&busca=' . urlencode($busca) : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Listagem de Funcionarios</title>
    <style>

        /* ── Reset ────────────────────────────────── */
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
        }

        /* ── Conteudo ─────────────────────────────── */
        .pagina {
            max-width: 820px;
            margin: 30px auto;
            padding: 0 18px;
        }

        .titulo-pagina {
            font-size: 20px;
            font-weight: bold;
            color: #222;
            margin-bottom: 18px;
        }

        /* ── Painel ───────────────────────────────── */
        .painel {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.10);
            overflow: hidden;
        }

        /* ── Barra de busca + novo ────────────────── */
        .barra-topo {
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #e5e8ec;
            background: #fdfdfd;
        }

        /* Caixa de busca */
        .busca-wrap {
            display: flex;
            align-items: stretch;
            border: 1px solid #ccc;
            border-radius: 4px;
            overflow: hidden;
            flex: 1;
            max-width: 320px;
        }

        .busca-wrap .busca-icone {
            display: flex;
            align-items: center;
            padding: 0 9px;
            background: #fff;
            color: #aaa;
            font-size: 14px;
        }

        /* Icone lupa em CSS */
        .lupa {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 2px solid #aaa;
            border-radius: 50%;
            position: relative;
        }

        .lupa::after {
            content: '';
            position: absolute;
            right: -5px; bottom: -5px;
            width: 5px; height: 2px;
            background: #aaa;
            transform: rotate(45deg);
            transform-origin: left center;
        }

        .busca-wrap input[type="text"] {
            border: none;
            outline: none;
            padding: 8px 6px;
            font-size: 13px;
            color: #444;
            flex: 1;
        }

        .busca-wrap input::placeholder { color: #bbb; }

        /* Botoes da barra */
        .btn {
            padding: 8px 18px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-pesquisar {
            background-color: #5a8cbf;
            color: #fff;
        }

        .btn-pesquisar:hover { background-color: #4a7aad; }

        .btn-novo {
            background-color: #3a6ea8;
            color: #fff;
            margin-left: 4px;
        }

        .btn-novo:hover { background-color: #2d5a8c; }

        /* ── Tabela ───────────────────────────────── */
        .tabela-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e8;
            padding: 10px 14px;
            text-align: left;
            font-size: 12.5px;
            font-weight: bold;
            color: #555;
        }

        tbody tr {
            border-bottom: 1px solid #f0f2f5;
        }

        tbody tr:hover {
            background-color: #f5f8ff;
        }

        tbody td {
            padding: 10px 14px;
            font-size: 13px;
            color: #333;
            vertical-align: middle;
        }

        /* Coluna ID pequena */
        .col-id {
            width: 40px;
            color: #777;
            font-size: 12px;
        }

        /* Nome em azul clicavel */
        .nome-link {
            color: #3a6ea8;
            text-decoration: none;
            font-weight: 500;
        }

        .nome-link:hover { text-decoration: underline; }

        /* Email em italico */
        .email-cell {
            font-style: italic;
            color: #555;
        }

        /* ── Badges de situacao ───────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 11px;
            border-radius: 3px;
            font-size: 11.5px;
            font-weight: bold;
            color: #fff;
        }

        .badge-ativo   { background-color: #5cb85c; }
        .badge-inativo { background-color: #9aa5b1; }

        /* ── Botoes de acao ───────────────────────── */
        .acoes {
            display: flex;
            gap: 5px;
        }

        .btn-acao {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px; height: 26px;
            border-radius: 3px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            color: #fff;
        }

        .btn-editar  { background-color: #3a6ea8; }
        .btn-editar:hover { background-color: #2d5a8c; }

        .btn-email   { background-color: #5a8cbf; }
        .btn-email:hover  { background-color: #4a7aad; }

        .btn-excluir { background-color: #d9534f; }
        .btn-excluir:hover { background-color: #c9302c; }

        /* Icone lapiz */
        .ico-lapiz {
            display: inline-block;
            width: 10px; height: 10px;
            border: 2px solid #fff;
            position: relative;
            transform: rotate(-45deg);
        }

        /* Icone envelope */
        .ico-envelope {
            display: inline-block;
            width: 12px; height: 9px;
            border: 2px solid #fff;
            border-radius: 1px;
            position: relative;
        }

        .ico-envelope::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            border-top: 5px solid #fff;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
        }

        /* Icone lixeira */
        .ico-lixo {
            display: inline-block;
            font-size: 11px;
        }

        /* ── Paginacao ────────────────────────────── */
        .paginacao {
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            border-top: 1px solid #e5e8ec;
        }

        .paginacao a,
        .paginacao span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px; height: 30px;
            padding: 0 6px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 12.5px;
            color: #3a6ea8;
            text-decoration: none;
            background: #fff;
        }

        .paginacao a:hover {
            background: #e8f0fb;
            border-color: #3a6ea8;
        }

        .paginacao .atual {
            background: #3a6ea8;
            color: #fff;
            border-color: #3a6ea8;
            font-weight: bold;
        }

        .paginacao .desativado {
            color: #bbb;
            cursor: default;
            pointer-events: none;
        }

        /* ── Sem resultados ───────────────────────── */
        .sem-dados {
            text-align: center;
            padding: 36px;
            color: #aaa;
            font-size: 14px;
        }

        /* ── Mensagem de erro de conexao ──────────── */
        .msg-erro-conn {
            background: #fdecea;
            border: 1px solid #e57373;
            color: #c62828;
            padding: 12px 16px;
            margin: 16px;
            border-radius: 4px;
            font-size: 13px;
        }

        /* ── Rodape ───────────────────────────────── */
        .rodape {
            text-align: center;
            padding: 18px;
            font-size: 11px;
            color: #aaa;
        }

    </style>
</head>
<body>

<!-- ── Navbar ──────────────────────────────────── -->
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

<!-- ── Conteudo ────────────────────────────────── -->
<div class="pagina">

    <div class="titulo-pagina">Listagem de Funcionarios</div>

    <div class="painel">

        <!-- Erro de conexao -->
        <?php if ($erro_conn !== ''): ?>
            <div class="msg-erro-conn"><?php echo htmlspecialchars($erro_conn); ?></div>
        <?php endif; ?>

        <!-- Barra: busca + botoes -->
        <div class="barra-topo">
            <form action="lista.php" method="get" style="display:flex;align-items:center;gap:8px;flex:1">
                <div class="busca-wrap">
                    <div class="busca-icone"><span class="lupa"></span></div>
                    <input type="text"
                           name="busca"
                           placeholder="Buscar funcionario..."
                           value="<?php echo htmlspecialchars($busca); ?>">
                </div>
                <input type="submit" class="btn btn-pesquisar" value="Pesquisar">
            </form>
            <a href="cadastro.php" class="btn btn-novo">Novo Funcionario</a>
        </div>

        <!-- Tabela de funcionarios -->
        <div class="tabela-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th>Nome</th>
                        <th>Cargo</th>
                        <th>E-mail</th>
                        <th>Situacao</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($funcionarios) > 0): ?>
                    <?php foreach ($funcionarios as $i => $f): ?>
                    <tr>
                        <td class="col-id"><?php echo ($offset + $i + 1); ?>.</td>
                        <td>
                            <a href="#" class="nome-link">
                                <?php echo htmlspecialchars($f['nome']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($f['cargo']); ?></td>
                        <td class="email-cell"><?php echo htmlspecialchars($f['email']); ?></td>
                        <td>
                            <?php if ($f['situacao'] === 'Ativo'): ?>
                                <span class="badge badge-ativo">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-inativo">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="acoes">
                                <!-- Editar -->
                                <a href="cadastro.php?id=<?php echo $f['id']; ?>"
                                   class="btn-acao btn-editar"
                                   title="Editar">&#9998;</a>
                                <!-- Email -->
                                <a href="mailto:<?php echo htmlspecialchars($f['email']); ?>"
                                   class="btn-acao btn-email"
                                   title="Enviar e-mail">&#9993;</a>
                                <!-- Excluir -->
                                <a href="excluir.php?id=<?php echo $f['id']; ?>"
                                   class="btn-acao btn-excluir"
                                   title="Excluir"
                                   onclick="return confirm('Confirma a exclusao de <?php echo htmlspecialchars(addslashes($f['nome'])); ?>?')">&#128465;</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="sem-dados">
                                <?php echo ($busca !== '') ? 'Nenhum funcionario encontrado para "' . htmlspecialchars($busca) . '".' : 'Nenhum funcionario cadastrado ainda.'; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginacao -->
        <?php if ($total_paginas > 1 || $total_registros > 0): ?>
        <div class="paginacao">

            <!-- Pagina anterior -->
            <?php if ($pagina_atual > 1): ?>
                <a href="lista.php?pagina=<?php echo ($pagina_atual - 1); ?><?php echo $busca_param; ?>">&laquo;</a>
            <?php else: ?>
                <span class="desativado">&laquo;</span>
            <?php endif; ?>

            <!-- Numeros de pagina -->
            <?php for ($p = 1; $p <= $total_paginas; $p++): ?>
                <?php if ($p === $pagina_atual): ?>
                    <span class="atual"><?php echo $p; ?></span>
                <?php else: ?>
                    <a href="lista.php?pagina=<?php echo $p; ?><?php echo $busca_param; ?>"><?php echo $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- Proxima pagina -->
            <?php if ($pagina_atual < $total_paginas): ?>
                <a href="lista.php?pagina=<?php echo ($pagina_atual + 1); ?><?php echo $busca_param; ?>">Proximo &raquo;</a>
            <?php else: ?>
                <span class="desativado">Proximo &raquo;</span>
            <?php endif; ?>

        </div>
        <?php endif; ?>

    </div><!-- /painel -->

    <div class="rodape">&copy; <?php echo date('Y'); ?> Sistema de Gestao de Funcionarios</div>

</div><!-- /pagina -->

</body>
</html>