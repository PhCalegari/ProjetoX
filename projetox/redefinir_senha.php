<?php
include("conexao.php");

if (isset($_GET["token"])) {
    $token = $_GET["token"];

    // Verifica se o token é válido e não expirou
    $stmt = $conn->prepare("SELECT * FROM RecuperacaoSenha WHERE token = :token AND expiracao > NOW()");
    $stmt->execute(['token' => $token]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tokenData) {
        // Exibe o formulário de redefinição
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $novaSenha = $_POST["senha"];
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

            // Atualiza senha do professor
            $stmt = $conn->prepare("UPDATE Professor SET Senha = :senha WHERE Email = :email");
            $stmt->execute(['senha' => $senhaHash, 'email' => $tokenData["email"]]);

            // Exclui token após uso
            $stmt = $conn->prepare("DELETE FROM RecuperacaoSenha WHERE token = :token");
            $stmt->execute(['token' => $token]);

            echo "<script>alert('Senha redefinida com sucesso!'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Link inválido ou expirado.'); window.location='esqueci_senha.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Token ausente.'); window.location='esqueci_senha.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
</head>
<body>
    <h2>Redefinir Senha</h2>
    <form method="POST">
        <label>Nova Senha:</label><br>
        <input type="password" name="senha" required><br><br>
        <button type="submit">Salvar Nova Senha</button>
    </form>
</body>
</html>
