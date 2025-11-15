<?php
session_start();
include("conexao.php");

// Apenas Admin pode cadastrar matéria
if (!isset($_SESSION["is_admin"]) || intval($_SESSION["is_admin"]) !== 1) {
    echo "403";
    exit;
}

$nome = isset($_POST["nome"]) ? trim($_POST["nome"]) : "";

if ($nome === "") {
    echo "vazio";
    exit;
}

// Verifica duplicidade (case-insensitive)
$stmt = $conn->prepare("SELECT 1 FROM materias WHERE LOWER(Nome_Materia) = LOWER(:n) LIMIT 1");
$stmt->execute(['n' => $nome]);
if ($stmt->fetchColumn()) {
    echo "existe";
    exit;
}

// Insere
$stmt = $conn->prepare("INSERT INTO materias (Nome_Materia) VALUES (:n)");
$ok = $stmt->execute(['n' => $nome]);

echo $ok ? "ok" : "erro";
exit;
