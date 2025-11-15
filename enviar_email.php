<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

function enviarEmailRecuperacao($emailDestino, $nome, $link) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'seuemail@gmail.com';
        $mail->Password = 'senhadeaplicativo';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Remetente
        $mail->setFrom('seuemail@gmail.com', 'Sistema de Professores');
        // Destinatário
        $mail->addAddress($emailDestino, $nome);

        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = 'Redefinição de senha - Sistema de Professores';
        $mail->Body = "
            <p>Olá, <b>$nome</b>!</p>
            <p>Clique no link abaixo para redefinir sua senha:</p>
            <p><a href='$link'>$link</a></p>
            <p>Esse link expira em 1 hora.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
?>
