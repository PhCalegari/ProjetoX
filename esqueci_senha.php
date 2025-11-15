<?php
include("conexao.php");
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = ""; // variável para mensagens dinâmicas

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    // Verifica se o email existe
    $stmt = $conn->prepare("SELECT * FROM Professor WHERE Email = :email");
    $stmt->execute(['email' => $email]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prof) {
        // Gera token data de expiração (1 hora)
        $token = bin2hex(random_bytes(16));
        $expiracao = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Salva token na tabela RecuperacaoSenha
        $stmt = $conn->prepare("INSERT INTO RecuperacaoSenha (email, token, expiracao) VALUES (:email, :token, :expiracao)");
        $stmt->execute([
            'email' => $email,
            'token' => $token,
            'expiracao' => $expiracao
        ]);

        // Link de redefinição
        $link = "http://localhost/projetox/redefinir_senha.php?token=" . $token;

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        try {
            // Configuração SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sistemaxtcc@gmail.com';
            $mail->Password = 'kltt oraefdqhohlz';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Remetente e destinatário
            $mail->setFrom('sistemaxtcc@gmail.com', 'Sistema de Professores');
            $mail->addAddress($email, $prof['Nome']);

            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Redefinição de senha - Sistema de Professores';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Olá, {$prof['Nome']}!</h2>
                    <p>Recebemos uma solicitação para redefinir sua senha.</p>
                    <p>Clique abaixo para redefinir:</p>
                    <p>
                        <a href='$link' style='background:#007bff;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>
                            Redefinir Senha
                        </a>
                    </p>
                    <p>Ou copie este link no navegador:</p>
                    <p><a href='$link'>$link</a></p>
                    <p>⚠️ O link expira em 1 hora.</p>
                </div>
            ";

            $mail->send();
            $msg = "<div class='msg sucesso'>Um link de redefinição foi enviado para seu e-mail.</div>";

        } catch (Exception $e) {
            $msg = "<div class='msg erro'>Erro ao enviar e-mail: {$mail->ErrorInfo}</div>";
        }

    } else {
        $msg = "<div class='msg erro'>E-mail não encontrado no sistema.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - SistemaX</title>
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

        .box {
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

        input[type="email"] {
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
            .box {
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
            .box {
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
    <div class="topo">RECUPERAÇÃO DE SENHA</div>

    <div class="container">
        <div class="box">
            <img src="img/logoxtransparente.png" alt="Logo" class="logo-fundo">

            <div class="formulario">
                <h2>Esqueceu sua senha?</h2>
                <p>Informe o seu e-mail e enviaremos um link para redefinir sua senha.</p>
                <hr><br>

                <form method="POST">
                    <label for="email">E-mail cadastrado:</label><br>
                    <input type="email" name="email" required><br>

                    <button type="submit">Enviar link de recuperação</button>

                    <!-- Mensagem dinâmica -->
                    <?= $msg ?>
                </form>

                <div class="voltar-login">
                    <a href="login.php">Voltar ao login</a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y'); ?> - SistemaX
    </footer>
</body>
</html>
