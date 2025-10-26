<?php
session_start();
include("conexao.php");

// Verifica login
if (!isset($_SESSION["Id_Prof"])) {
    header("Location: login.php");
    exit;
}

// Verifica se é admin
$isAdmin = isset($_SESSION["is_admin"]) && intval($_SESSION["is_admin"]) === 1;

// Ações do admin
if ($isAdmin && isset($_GET['acao']) && isset($_GET['Id_Prof'])) {
    $id = intval($_GET['Id_Prof']);
    $acao = ($_GET['acao'] === 'aprovar') ? 1 : 2;

    $stmt = $conn->prepare("UPDATE Professor SET Aprovado = :acao WHERE Id_Prof = :id");
    $stmt->execute(['acao' => $acao, 'id' => $id]);

    header("Location: home.php?msg=atualizado");
    exit;
}

$pendentes = [];
if ($isAdmin) {
    $stmt = $conn->query("SELECT * FROM Professor WHERE Aprovado = 0 ORDER BY Nome ASC");
    $pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mensagens 
$mensagem = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'sucesso') {
        $mensagem = "<p class='mensagem sucesso'>Login realizado com sucesso!</p>";
    } elseif ($_GET['msg'] === 'atualizado') {
        $mensagem = "<p class='mensagem sucesso'>Status atualizado com sucesso!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Home - SistemaX</title>
    <link rel="icon" type="image/png" href="img/logoxtransparente.png">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6fc;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background-color: #032b73;
            color: white;
            text-align: center;
            padding: 20px 0;
            font-size: 22px;
            font-weight: 600;
        }

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-direction: column;
            padding: 40px;
        }

        .logout {
            position: fixed;
            top: 20px;
            right: 30px;
            background-color: #b30000;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .logout:hover {
            background-color: #d00000;
        }

        .aprovar-box {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 1000px;
            margin-top: 30px;
            padding: 25px;
        }

        h2 {
            color: #032b73;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #032b73;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            display:inline-block;
            text-decoration:none;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            color: white;
            transition: 0.3s;
        }

        .btn-aprovar { background-color: #028a0f; }
        .btn-aprovar:hover { background-color: #05b21c; }

        .btn-reprovar { background-color: #b30000; }
        .btn-reprovar:hover { background-color: #d00000; }

        footer {
            background-color: #032b73;
            color: white;
            text-align: center;
            padding: 12px 0;
            font-size: 14px;
            margin-top: auto;
        }

        /* ======== Mensagem ======== */
        .mensagem {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            font-weight: 500;
            width: 90%;
            max-width: 1000px;
        }

        .mensagem.sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensagem.erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 900px) {
            .aprovar-box { width: 95%; padding: 20px; }
        }

        @media (max-width: 600px) {
            th, td { font-size: 13px; padding: 8px; }
            .btn { font-size: 12px; padding: 5px 8px; }
        }
    </style>
</head>
<body>

<header>
    Bem-vindo, <?= htmlspecialchars($_SESSION["nome"]); ?>
</header>

<a class="logout" href="logout.php">Sair</a>

<main>
    <?= $mensagem ?>

    <?php if ($isAdmin): ?>
        <div class="aprovar-box">
            <h2>Professores aguardando aprovação</h2>
            <?php if (count($pendentes) > 0): ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                    <?php foreach ($pendentes as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['Id_Prof']); ?></td>
                            <td><?= htmlspecialchars($p['Nome']); ?></td>
                            <td><?= htmlspecialchars($p['CPF']); ?></td>
                            <td><?= htmlspecialchars($p['Email']); ?></td>
                            <td>
                                <a href="?acao=aprovar&Id_Prof=<?= intval($p['Id_Prof']) ?>" class="btn btn-aprovar">Aprovar</a>
                                <a href="?acao=reprovar&Id_Prof=<?= intval($p['Id_Prof']) ?>" class="btn btn-reprovar">Reprovar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>Nenhum professor pendente no momento.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="aprovar-box">
            <p>Olá, <?= htmlspecialchars($_SESSION["nome"]); ?>! Seu login foi realizado com sucesso.</p>
        </div>
    <?php endif; ?>
</main>

<footer>
    &copy; <?= date("Y"); ?> - SistemaX
</footer>

</body>
</html>
