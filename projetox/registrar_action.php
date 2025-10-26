<?php
include("conexao.php");

$nome = $_POST['nome'];
$cpf = $_POST['cpf'];
$email = $_POST['email'];
$senha = $_POST['senha'];

// Criptografa senha
$senha_hash = hash('sha256', $senha);

// Verifica duplicidade de CPF ou e-mail
$check = $conn->prepare("SELECT * FROM Professor WHERE CPF = ? OR Email = ?");
$check->bind_param("ss", $cpf, $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "<script>alert('CPF ou e-mail já cadastrados!'); window.location='registrar.php';</script>";
    exit;
}

// Insere no banco
$stmt = $conn->prepare("INSERT INTO Professor (Nome, CPF, Email, Senha) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nome, $cpf, $email, $senha_hash);

if ($stmt->execute()) {
    echo "<script>alert('Cadastro realizado com sucesso!'); window.location='login.php';</script>";
} else {
    echo "<script>alert('Erro ao cadastrar.'); window.location='registrar.php';</script>";
}
?>
