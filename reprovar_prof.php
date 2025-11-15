<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["Id_Prof"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: home.php?msg=permissao");
    exit;
}

$id = intval($_GET["id"] ?? 0);
if ($id <= 0) {
    header("Location: home.php?msg=id_invalido");
    exit;
}

// excluir professor ou apenas marcar como reprovado?
// vamos marcar como reprovado (mais seguro)
$st = $conn->prepare("UPDATE professor SET Aprovado = 2 WHERE Id_Prof = ?");
$st->execute([$id]);

header("Location: home.php?msg=reprovado");
exit;
