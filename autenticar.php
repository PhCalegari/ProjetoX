<?php
session_start();
include("conexao.php");

// Verifica se form foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login = trim($_POST["login"]);
    $senha = trim($_POST["senha"]);

    // Busca por e-mail ou CPF
    $stmt = $conn->prepare("SELECT * FROM professor 
                            WHERE Email = :login OR CPF = :login LIMIT 1");
    $stmt->execute(['login' => $login]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['Senha'])) {

        // Verifica status
        if ($usuario["Aprovado"] == 0) {
            header("Location: login.php?msg=pendente");
            exit;
        }
        if ($usuario["Aprovado"] == 2) {
            header("Location: login.php?msg=reprovado");
            exit;
        }

        // LOGIN AUTORIZADO ✅
        $_SESSION["Id_Prof"] = $usuario["Id_Prof"];
        $_SESSION["nome"] = $usuario["Nome"];
        $_SESSION["is_admin"] = intval($usuario["IsAdmin"]);

        header("Location: home.php?msg=sucesso");
        exit;
    }

    header("Location: login.php?msg=erro");
    exit;
}

header("Location: login.php");
exit;
?>
