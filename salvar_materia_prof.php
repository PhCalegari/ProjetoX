<?php
session_start();
include("conexao.php");

if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) exit("403");

$Id_Prof = intval($_POST["Id_Prof"]);
$Id_Materia = intval($_POST["Id_Materia"]);

$stmt = $conn->prepare("SELECT 1 FROM professor_materia WHERE Id_Prof = :p AND Id_Materia = :m");
$stmt->execute(['p'=>$Id_Prof,'m'=>$Id_Materia]);
if ($stmt->fetchColumn()) exit("existe");

$stmt = $conn->prepare("INSERT INTO professor_materia VALUES(:p, :m)");
$stmt->execute(['p'=>$Id_Prof,'m'=>$Id_Materia]);

echo "ok";
