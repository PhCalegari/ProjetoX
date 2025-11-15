<?php
session_start();
require_once "conexao.php";

// Bloqueia acesso sem login
if (!isset($_SESSION["Id_Prof"])) {
  header("Location: login.php");
  exit;
}

$idProf = intval($_SESSION["Id_Prof"]);

// ====== CARREGA DADOS DO PROFESSOR ======
$stmt = $conn->prepare("SELECT Nome, CPF, Email, Endereco, Telefone FROM professor WHERE Id_Prof = ?");
$stmt->execute([$idProf]);
$prof = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$prof) {
  die("<h3 style='text-align:center;color:red'>Professor não encontrado.</h3>");
}

$msg = "";

// ====== SALVAR ALTERAÇÕES ======
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nome  = trim($_POST["Nome"] ?? '');
  $cpf   = trim($_POST["CPF"] ?? '');
  $email = trim($_POST["Email"] ?? '');
  $end   = trim($_POST["Endereco"] ?? '');
  $tel   = trim($_POST["Telefone"] ?? '');
  $senha = trim($_POST["Senha"] ?? '');

  if ($nome && $cpf && $email) {
    if ($senha !== '') {
      $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
      $sql = "UPDATE professor SET Nome=?, CPF=?, Email=?, Senha=?, Endereco=?, Telefone=? WHERE Id_Prof=?";
      $params = [$nome, $cpf, $email, $senhaHash, $end, $tel, $idProf];
    } else {
      $sql = "UPDATE professor SET Nome=?, CPF=?, Email=?, Endereco=?, Telefone=? WHERE Id_Prof=?";
      $params = [$nome, $cpf, $email, $end, $tel, $idProf];
    }

    $st = $conn->prepare($sql);
    $st->execute($params);

    $_SESSION["Nome"] = $nome;
    $msg = "<p class='mensagem sucesso'>Perfil atualizado com sucesso!</p>";
  } else {
    $msg = "<p class='mensagem erro'>Preencha todos os campos obrigatórios.</p>";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Meu Perfil - SistemaX</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* =======================
   BASE / LAYOUT PADRÃO
   ======================= */
html, body{
  margin:0;
  padding:0;
  height:100%;
}
body{
  font-family:'Segoe UI',sans-serif;
  background:#f4f6fc;
  display:flex;
  flex-direction:column;
}

/* =======================
   HEADER
   ======================= */
header{
  background:#032b73;
  color:#fff;
  text-align:center;
  padding:20px;
  font-size:22px;
  font-weight:700;
  position:relative;
}
header .logout{
  position:absolute;
  right:25px;
  top:15px;
  background:#b30000;
  padding:10px 20px;
  border-radius:8px;
  color:#fff;
  font-weight:700;
  text-decoration:none;
}
header .logout:hover{ background:#e21818; }

/* =======================
   MAIN
   ======================= */
main{
  flex:1;
  padding:32px;
  display:flex;
  flex-direction:column;
  align-items:center;
}

/* =======================
   BOX DO FORMULÁRIO
   ======================= */
.box{
  background:#fff;
  width:90%;
  max-width:600px;
  border-radius:18px;
  padding:26px;
  box-shadow:0 0 12px rgba(0,0,0,.1);
}
h2{
  color:#032b73;
  text-align:center;
  margin-bottom:20px;
}

label{
  font-weight:600;
  color:#333;
  display:block;
  margin-top:12px;
}

input[type="text"],
input[type="email"],
input[type="password"],
textarea{
  width:100%;
  padding:10px;
  border-radius:8px;
  border:1px solid #ccc;
  background:#e3e9ff;
  font-size:15px;
  margin-top:4px;
}

textarea{
  resize:vertical;
}

button{
  width:100%;
  margin-top:20px;
  padding:12px;
  border:none;
  border-radius:10px;
  background:#032b73;
  color:#fff;
  font-weight:700;
  cursor:pointer;
}
button:hover{ background:#0a3fa5; }

/* MENSAGENS */
.mensagem{
  margin-top:15px;
  padding:10px;
  text-align:center;
  border-radius:8px;
  font-weight:600;
}
.sucesso{
  background:#d4edda;
  color:#155724;
  border:1px solid #c3e6cb;
}
.erro{
  background:#f8d7da;
  color:#721c24;
  border:1px solid #f5c6cb;
}

/* FOTO */
.foto-generica{
  display:flex;
  flex-direction:column;
  align-items:center;
  margin-bottom:15px;
}
.foto-generica img{
  width:120px;
  height:120px;
  border-radius:50%;
  object-fit:cover;
  border:2px solid #032b73;
  margin-bottom:8px;
}

/* =======================
   FOOTER FIXO NO FINAL
   ======================= */
footer{
  background:#032b73;
  color:#fff;
  text-align:center;
  padding:15px;
  margin-top:auto;
  font-size:14px;
}

/* =======================
   BOTÃO VOLTAR PADRÃO
   ======================= */
.btn-voltar{
  position:fixed;
  bottom:20px;
  right:20px;
  background:#555;
  color:#fff;
  border:none;
  border-radius:8px;
  padding:10px 14px;
  font-size:14px;
  cursor:pointer;
  box-shadow:0 3px 6px rgba(0,0,0,0.25);
  z-index:9999;
  transition:background .3s;

  width:auto !important;
  min-width:90px;
  white-space:nowrap;
}
.btn-voltar:hover{
  background:#333;
}


/* =======================
   RESPONSIVIDADE
   ======================= */

/* Tablets */
@media (max-width:768px){
  header{
    padding:16px;
    font-size:20px;
  }
  header .logout{
    right:15px;
    top:12px;
    padding:8px 12px;
    font-size:13px;
  }
  main{
    padding:20px;
  }
  .box{
    padding:20px;
    width:95%;
  }
}

/* Celulares */
@media (max-width:480px){
  header{
    font-size:18px;
    padding:14px;
  }
  header .logout{
    right:10px;
    top:10px;
    padding:6px 10px;
    font-size:12px;
  }
  main{
    padding:14px;
  }
  .box{
    padding:16px;
    width:100%;
  }
  button{
    font-size:14px;
    padding:10px;
  }
  .btn-voltar{
    bottom:12px;
    right:12px;
    padding:8px 12px;
    font-size:13px;
  }
}

</style>
</head>
<body>
<header>
  Meu Perfil
  <a href="logout.php" class="logout">Sair</a>
</header>

<main>
  <div class="box">
    <h2>Atualize suas informações</h2>

    <div class="foto-generica">
      <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Foto genérica de perfil">

    </div>

    <form method="POST">
      <label>Nome:</label>
      <input type="text" name="Nome" value="<?= htmlspecialchars($prof['Nome']) ?>" required>

      <label>CPF:</label>
      <input type="text" name="CPF" value="<?= htmlspecialchars($prof['CPF']) ?>" maxlength="11" required>

      <label>E-mail:</label>
      <input type="email" name="Email" value="<?= htmlspecialchars($prof['Email']) ?>" required>

      <label>Endereço:</label>
      <textarea name="Endereco" rows="2"><?= htmlspecialchars($prof['Endereco']) ?></textarea>

      <label>Telefone:</label>
      <input type="text" name="Telefone" value="<?= htmlspecialchars($prof['Telefone']) ?>" placeholder="(00) 00000-0000">

      <button type="submit">💾 Salvar Alterações</button>
      <?= $msg ?>
    </form>
  </div>
</main>

<footer>© <?= date("Y") ?> - SistemaX</footer>

<!-- Botão global de voltar -->
<!-- Botão global de voltar -->
<script>
document.addEventListener('DOMContentLoaded', ()=>{
  if (document.querySelector('.btn-voltar')) return;

  const btnVoltar = document.createElement('button');
  btnVoltar.type = "button";
  btnVoltar.className = "btn-voltar";
  btnVoltar.textContent = "⬅️ Voltar";
  btnVoltar.style = `
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #555;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 15px;
    cursor: pointer;
    box-shadow: 0 3px 6px rgba(0,0,0,0.2);
    z-index: 9999;
    transition: background 0.3s;
  `;
  btnVoltar.onmouseover = ()=> btnVoltar.style.background = "#333";
  btnVoltar.onmouseout  = ()=> btnVoltar.style.background = "#555";

  // Função de voltar com fallback
  btnVoltar.onclick = ()=>{
    window.location.href = "home.php"; 
};


  document.body.appendChild(btnVoltar);
});
</script>

</body>
</html>
