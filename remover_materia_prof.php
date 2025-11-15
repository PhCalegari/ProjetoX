<?php
session_start();
include("conexao.php");

if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) exit("403");

$stmt = $conn->prepare("DELETE FROM professor_materia WHERE Id_Prof = :p AND Id_Materia = :m");
$stmt->execute(['p'=>$_POST["Id_Prof"], 'm'=>$_POST["Id_Materia"]]);

echo "ok";
