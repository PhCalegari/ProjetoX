<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "bancoquestoes";

try {
    $conn = new PDO("mysql:host=$host;dbname=$banco;charset=utf8mb4", $usuario, $senha);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erro na conexão: " . $e->getMessage());
    die("Erro ao conectar ao banco de dados. Tente novamente mais tarde.");
}
?>

