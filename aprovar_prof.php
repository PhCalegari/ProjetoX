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

$st = $conn->prepare("UPDATE professor SET Aprovado = 1 WHERE Id_Prof = ?");
$st->execute([$id]);

header("Location: home.php?msg=aprovado");
exit;
