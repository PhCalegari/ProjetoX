<?php
include("conexao.php");

if (!isset($_GET["token"])) {
    header("Location: esqueci_senha.php?msg=token_ausente");
    exit;
}

$token = $_GET["token"];

// Verifica token e validade
$stmt = $conn->prepare("SELECT email FROM RecuperacaoSenha WHERE token = :token AND expiracao > NOW()");
$stmt->execute(['token' => $token]);
$tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tokenData) {
    header("Location: esqueci_senha.php?msg=token_invalido");
    exit;
}

$mensagem = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'senha_curta') {
        $mensagem = "<p class='mensagem erro'>A senha deve ter no mínimo 6 caracteres.</p>";
    } elseif ($_GET['msg'] === 'nao_confere') {
        $mensagem = "<p class='mensagem erro'>As senhas não coincidem!</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $senha = trim($_POST["senha"]);
    $confirmar = trim($_POST["confirmar"]);

    if (strlen($senha) < 6) {
        header("Location: redefinir_senha.php?token=$token&msg=senha_curta");
        exit;
    }

    if ($senha !== $confirmar) {
        header("Location: redefinir_senha.php?token=$token&msg=nao_confere");
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE Professor SET Senha = :senha WHERE Email = :email");
    $stmt->execute(['senha' => $senhaHash, 'email' => $tokenData["email"]]);

    $stmt = $conn->prepare("DELETE FROM RecuperacaoSenha WHERE token = :token");
    $stmt->execute(['token' => $token]);

    echo "<script>alert('Senha redefinida com sucesso!'); window.location='login.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Redefinir Senha - SistemaX</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f4f6fc;
    margin: 0;
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.topo {
    background-color: #032b73;
    color: white;
    text-align: center;
    padding: 20px 0;
    font-size: 22px;
    letter-spacing: 1px;
    flex-shrink: 0;
}

.container {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.login-box {
    background-color: rgba(255,255,255,0.95);
    border-radius: 20px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    padding: 60px 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    max-width: 800px;
    width: 90%;
    position: relative;
    z-index: 2;
}

.formulario {
    width: 55%;
}

.formulario h2 {
    color: #032b73;
    margin-bottom: 20px;
    font-weight: 600;
}

.input-area,
.input-group {
    position: relative;
}

input[type=password],
input[type=text] {
    width: 100%;
    padding: 12px 50px 12px 12px;
    margin-top: 6px;
    margin-bottom: 18px;
    border-radius: 8px;
    border: 1px solid #ccc;
    background-color: #e3e9ff;
    font-size: 15px;
    box-sizing: border-box;
}

.toggle {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 23px;
    user-select: none;
    color: #032b73;
}

#status-senha {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 10px;
}

.sucesso {
    color: #0a7b00;
}

.erro {
    color: #c10000;
}

button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background-color: #032b73;
    color: white;
    font-weight: bold;
    font-size: 15px;
    cursor: pointer;
}

button:hover {
    background-color: #0a3fa5;
}

.logo-fundo {
    width: 250px;
    opacity: 0.9;
}

footer {
    background-color: #032b73;
    color: white;
    text-align: center;
    padding: 12px 0;
    font-size: 14px;
}

@media (max-width: 900px) {
    .login-box {
        flex-direction: column;
        text-align: center;
        padding: 35px;
        width: 95%;
        gap: 20px;
    }
    .formulario { width: 100%; }
    .logo-fundo {
        width: 180px;
        margin-top: 15px;
    }
}
</style>

</head>

<body>

<div class="topo">REDEFINIR SENHA</div>

<div class="container">
    <div class="login-box">
        <div class="formulario">
            <h2>Criar Nova Senha</h2>
            <hr><br>

            <form method="POST">
                
                <label>Nova Senha:</label>
                <div class="input-group">
                    <input type="password" id="senha" name="senha" required>
                    <span class="toggle" onclick="toggleSenhas(this)">🔒</span>
                </div>

                <label>Confirmar Senha:</label>
                <div class="input-area">
                    <input type="password" id="confirmar" name="confirmar" required>
                </div>

                <p id="status-senha"></p>

                <button type="submit">Salvar Nova Senha</button>
                <?= $mensagem ?>
            </form>
        </div>

        <img src="img/logoxtransparente.png" class="logo-fundo" alt="Logo">
    </div>
</div>

<footer>
    &copy; <?= date('Y'); ?> - SistemaX
</footer>

<script>
// ✅ Mostrar/Ocultar ambas as senhas
function toggleSenhas(el) {
    const campos = ['senha', 'confirmar'];
    let aberto = el.textContent === '🔓';

    campos.forEach(id => {
        let campo = document.getElementById(id);
        campo.type = aberto ? "password" : "text";
    });

    el.textContent = aberto ? '🔒' : '🔓';
}

// ✅ Verifica se as senhas são iguais em tempo real
const senha = document.getElementById("senha");
const confirmar = document.getElementById("confirmar");
const status = document.getElementById("status-senha");

function validarSenhas() {
    if (!senha.value || !confirmar.value) {
        status.textContent = "";
        return;
    }

    if (senha.value === confirmar.value) {
        status.textContent = "✅ As senhas estão iguais!";
        status.className = "sucesso";
    } else {
        status.textContent = "❌ As senhas não coincidem!";
        status.className = "erro";
    }
}

senha.addEventListener("keyup", validarSenhas);
confirmar.addEventListener("keyup", validarSenhas);
</script>

</body>
</html>
