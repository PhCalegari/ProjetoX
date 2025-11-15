<?php
session_start();
$mensagem = "";

// Se vier mensagem por GET (ex: ?msg=erro)
if (isset($_GET['msg'])) {
  if ($_GET['msg'] === 'erro') {
    $mensagem = "<p class='mensagem erro'>Usuário ou senha incorretos.</p>";
  } elseif ($_GET['msg'] === 'pendente') {
    $mensagem = "<p class='mensagem aviso'>Seu cadastro ainda não foi aprovado pelo administrador.</p>";
  } elseif ($_GET['msg'] === 'reprovado') {
    $mensagem = "<p class='mensagem erro'>Seu cadastro foi reprovado. Contate o administrador.</p>";
  } elseif ($_GET['msg'] === 'sucesso') {
    $mensagem = "<p class='mensagem sucesso'>Login realizado com sucesso!</p>";
  }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - SistemaX</title>
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

    .login-box {
      background-color: rgba(255, 255, 255, 0.95);
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

    .formulario p {
      color: #333;
      font-size: 15px;
      margin-bottom: 15px;
    }

    label {
      font-weight: bold;
      color: #333;
      font-size: 14px;
    }

    input[type="text"],
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

    .recuperar {
      font-size: 13px;
      margin-bottom: 15px;
      text-align: center;
    }

    .recuperar a {
      color: #032b73;
      text-decoration: underline;
    }

    .registrar {
      text-align: center;
      margin-top: 10px;
      font-size: 13.5px;
    }

    .registrar a {
      color: #032b73;
      font-weight: 600;
      text-decoration: none;
    }

    .registrar a:hover {
      text-decoration: underline;
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

    /* ======== Mensagem abaixo do botão ======== */
    .mensagem {
      text-align: center;
      margin-top: 15px;
      padding: 10px;
      border-radius: 8px;
      font-weight: 500;
    }

    .mensagem.sucesso {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .mensagem.erro {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .mensagem.aviso {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeeba;
    }

    @media (max-width: 900px) {
      .login-box {
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
      .login-box {
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
  <div class="topo">LOGIN</div>

  <div class="container">
    <div class="login-box">
      <img src="img/logoxtransparente.png" alt="Logo" class="logo-fundo">

      <div class="formulario">
        <h2>Bem-vindo</h2>
        <p>Se você já possui uma conta conosco, faça o login.</p>
        <hr><br>

        <form action="autenticar.php" method="POST">
          <label>E-mail / CPF:</label><br>
          <input type="text" name="login" required><br>

          <label>Senha:</label><br>
          <input type="password" name="senha" required><br>

          <div class="recuperar">
            <a href="esqueci_senha.php">Esqueceu a senha?</a>
          </div>

          <button type="submit">Entrar</button>

          <!-- Exibe a mensagem dinâmica -->
          <?php echo $mensagem; ?>
        </form>

        <div class="registrar">
          Ainda não tem uma conta? <a href="registrar.php">Crie uma agora</a>
        </div>
      </div>
    </div>
  </div>

  <footer>
    &copy; <?php echo date('Y'); ?> - SistemaX
  </footer>
</body>
</html>
