<?php
include("conexao.php");

$msg = ""; // variável para mensagem dinâmica

/**
 * Função para validar CPF localmente
 */
function validarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $cpf = trim($_POST["cpf"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    // Remove pontos e traço do CPF
    $cpfLimpo = preg_replace('/\D/', '', $cpf);

    // Valida CPF localmente
    if (!validarCPF($cpfLimpo)) {
        $msg = "<div class='msg erro'>CPF inválido! Verifique e tente novamente.</div>";
    } else {
        // Verifica se já existe email ou CPF cadastrado
        $stmt = $conn->prepare("SELECT * FROM Professor WHERE CPF = :cpf OR Email = :email");
        $stmt->execute(['cpf' => $cpfLimpo, 'email' => $email]);

        if ($stmt->rowCount() > 0) {
            $msg = "<div class='msg erro'>CPF ou Email já cadastrados!</div>";
        } else {
            // Gera o hash seguro da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            // Insere o novo professor no banco
            $stmt = $conn->prepare("INSERT INTO Professor (Nome, CPF, Email, Senha, Aprovado)
                                    VALUES (:nome, :cpf, :email, :senha, 0)");
            $stmt->execute([
                'nome' => $nome,
                'cpf' => $cpfLimpo,
                'email' => $email,
                'senha' => $senha_hash
            ]);

            $msg = "<div class='msg sucesso'>Cadastro realizado com sucesso! Aguarde a aprovação do administrador.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro - SistemaX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="img/logoxtransparente.png">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f6fc;
      margin: 0;
      padding: 0;
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
      position: relative;
      overflow: hidden;
      padding: 20px;
    }
    .cadastro-box {
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
      backdrop-filter: blur(3px);
    }
    .formulario {
      width: 55%;
      z-index: 2;
      position: relative;
    }
    .formulario h2 {
      color: #032b73;
      margin-bottom: 20px;
      font-weight: 600;
    }
    label {
      font-weight: bold;
      color: #333;
      font-size: 14px;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-top: 6px;
      margin-bottom: 18px;
      border-radius: 8px;
      border: 1px solid #ccc;
      background-color: #e3e9ff;
      outline: none;
      font-size: 15px;
      box-sizing: border-box;
    }
    input:focus {
      border-color: #032b73;
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
      transition: 0.3s;
    }
    button:hover {
      background-color: #0a3fa5;
    }
    .voltar-login {
      text-align: center;
      margin-top: 10px;
      font-size: 13.5px;
    }
    .voltar-login a {
      color: #032b73;
      font-weight: 600;
      text-decoration: none;
    }
    .voltar-login a:hover {
      text-decoration: underline;
    }
    .msg {
      text-align: center;
      margin-top: 15px;
      padding: 10px;
      border-radius: 8px;
      font-weight: 500;
      width: 100%;
      box-sizing: border-box;
    }
    .msg.sucesso {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .msg.erro {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .msg.aviso {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeeba;
    }
    .logo-fundo {
      position: absolute;
      right: 40px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 1;
    }
    footer {
      background-color: #032b73;
      color: white;
      text-align: center;
      padding: 12px 0;
      font-size: 14px;
      flex-shrink: 0;
    }
    @media (max-width: 900px) {
      .cadastro-box {
        flex-direction: column;
        text-align: center;
        padding: 40px;
        gap: 30px;
        width: 95%;
      }
      .formulario {
        width: 100%;
      }
      .logo-fundo {
        position: relative;
        transform: none;
        top: 0;
        right: 0;
        width: 200px;
        margin: 0 auto 10px;
      }
    }
    @media (max-width: 480px) {
      .cadastro-box {
        padding: 30px 25px;
      }
      .formulario h2 {
        font-size: 20px;
      }
      button {
        font-size: 14px;
      }
    }
  </style>
</head>

<body>
  <div class="topo">CADASTRO DE PROFESSOR</div>
  <div class="container">
    <div class="cadastro-box">
      <img src="img/logoxtransparente.png" alt="Logo" class="logo-fundo">

      <div class="formulario">
        <h2>Crie sua conta</h2>
        <p>Preencha os campos abaixo para se cadastrar.</p>
        <hr><br>

        <form method="POST">
          <label>Nome:</label><br>
          <input type="text" name="nome" required><br>

          <label>CPF:</label><br>
          <input type="text" name="cpf" id="cpf" maxlength="14" required placeholder="000.000.000-00"><br>

          <label>Email:</label><br>
          <input type="email" name="email" required><br>

          <label>Senha:</label><br>
          <input type="password" name="senha" required><br>

          <button type="submit">Cadastrar</button>

          <!-- Mensagem -->
          <?= $msg ?>
        </form>

        <div class="voltar-login">
          Já tem uma conta? <a href="login.php">Faça login</a>
        </div>
      </div>
    </div>
  </div>

  <footer>
    &copy; <?= date('Y'); ?> - SistemaX
  </footer>

  <script>
    // Máscara CPF
    document.getElementById('cpf').addEventListener('input', function (e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 3 && value.length <= 6)
        value = value.replace(/(\d{3})(\d+)/, '$1.$2');
      else if (value.length > 6 && value.length <= 9)
        value = value.replace(/(\d{3})(\d{3})(\d+)/, '$1.$2.$3');
      else if (value.length > 9)
        value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
      e.target.value = value;
    });
  </script>
</body>
</html>
