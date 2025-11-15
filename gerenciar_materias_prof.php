<?php
session_start();
include("conexao.php");

// Apenas admin
if (!isset($_SESSION["is_admin"]) || intval($_SESSION["is_admin"]) !== 1) {
    header("Location: home.php");
    exit;
}

$profId = intval($_GET["Id_Prof"] ?? 0);
if ($profId <= 0) {
    header("Location: home.php");
    exit;
}

// Carregar professor
$stmt = $conn->prepare("SELECT Nome FROM professor WHERE Id_Prof = :id");
$stmt->execute(['id' => $profId]);
$prof = $stmt->fetch(PDO::FETCH_ASSOC);

// Matérias cadastradas
$stmt = $conn->query("SELECT * FROM materias ORDER BY Nome_Materia");
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Matérias já atribuídas ao professor
$stmt = $conn->prepare("SELECT Id_Mat FROM professor_materia WHERE Id_Prof = :id");
$stmt->execute(['id' => $profId]);
$atuais = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), "Id_Mat");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selecionadas = $_POST["materias"] ?? [];

    // Remove tudo
    $conn->prepare("DELETE FROM professor_materia WHERE Id_Prof = :id")->execute(['id' => $profId]);

    // Insere selecionadas
    $stmtIns = $conn->prepare("INSERT INTO professor_materia (Id_Prof, Id_Mat) VALUES (:p, :m)");
    foreach ($selecionadas as $m) {
        $stmtIns->execute(['p' => $profId, 'm' => $m]);
    }

    header("Location: gerenciar_materias_prof.php?Id_Prof=$profId&msg=ok");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Matérias - SistemaX</title>
<style>
body { font-family:'Segoe UI'; background:#f4f6fc; padding:20px; }
.box {
    max-width:600px; margin:auto; background:#fff;
    padding:30px; border-radius:15px; box-shadow:0 0 15px rgba(0,0,0,0.1);
}
h2 { color:#032b73; text-align:center; }
label { font-weight:600; }
button {
    background:#032b73; color:#fff; border:none;
    padding:12px; width:100%; border-radius:8px; cursor:pointer;
    font-size:16px; font-weight:600;
}
button:hover { background:#0a3fa5; }
.mensagem { text-align:center;margin-bottom:10px;color:#0a7b00;font-weight:bold; }
</style>
</head>
<body>

<div class="box">
    <h2>Matérias do Professor: <?= htmlspecialchars($prof["Nome"]); ?></h2>

    <?php if(isset($_GET['msg'])) echo "<p class='mensagem'>Atualizado ✅</p>"; ?>

    <form method="POST">
        <?php foreach ($materias as $m): ?>
            <div>
                <label>
                    <input type="checkbox"
                        name="materias[]"
                        value="<?= $m['Id_Mat']; ?>"
                        <?= in_array($m['Id_Mat'], $atuais) ? 'checked' : ''; ?>>
                    <?= htmlspecialchars($m['Nome_Materia']); ?>
                </label>
            </div>
        <?php endforeach; ?>

        <br>
        <button type="submit">Salvar</button>
    </form>

</div>

</body>
</html>
