<?php
session_start();
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login"] ?? '');
    $senha = $_POST["senha"] ?? '';
    $cpfLimpo = preg_replace('/\D/', '', $login);

    $stmt = $conn->prepare("SELECT * FROM Professor WHERE Email = :login OR CPF = :cpf");
    $stmt->execute(['login' => $login, 'cpf' => $cpfLimpo]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prof) {

        if (password_verify($senha, $prof['Senha'])) {
            if ($prof['Aprovado'] == 0) {
                header("Location: login.php?msg=pendente");
                exit;
            } elseif ($prof['Aprovado'] == 2) {
                header("Location: login.php?msg=reprovado");
                exit;
            }

            $_SESSION["Id_Prof"] = $prof["Id_Prof"];
            $_SESSION["nome"] = $prof["Nome"];
            $_SESSION["is_admin"] = $prof["IsAdmin"];

            header("Location: home.php?msg=sucesso");
            exit;
        } else {
            header("Location: login.php?msg=erro");
            exit;
        }
    } else {
        header("Location: login.php?msg=erro");
        exit;
    }
}
?>
