<?php
session_start();
require_once "conexao.php";
header("Content-Type: text/html; charset=utf-8");

if (!isset($_SESSION["Id_Prof"])) {
  header("Location: login.php");
  exit;
}

$idProf = intval($_SESSION["Id_Prof"]);
$isAdmin = isset($_SESSION["is_admin"]) && intval($_SESSION["is_admin"]) === 1;

/* ===========================================================
   BACKEND (respostas AJAX)
   =========================================================== */
if (isset($_POST['ajax'])) {
  header("Content-Type: application/json; charset=utf-8");
  $op = $_POST['op'] ?? '';

  function ok($rows = [], $msg = '', $extra = []) {
    echo json_encode(['ok'=>true, 'rows'=>$rows, 'msg'=>$msg] + $extra); exit;
  }
  function err($msg) { echo json_encode(['ok'=>false, 'msg'=>$msg]); exit; }

  try {
    // ==================== MATÉRIAS ====================
    if ($op === 'materias') {
      $idCurso = intval($_POST['idCurso'] ?? 0);
      if (!$idCurso) err('Curso inválido.');

      if ($isAdmin) {
        $st = $conn->prepare("
          SELECT Id_Materia, Nome_Materia 
          FROM materias 
          WHERE Id_Curso = :c 
          ORDER BY Nome_Materia
        ");
        $st->execute(['c'=>$idCurso]);
      } else {
        $st = $conn->prepare("
          SELECT DISTINCT m.Id_Materia, m.Nome_Materia
          FROM materias m
          INNER JOIN curso c ON c.Id_Curso = m.Id_Curso
          INNER JOIN professor_materia_turma pmt ON pmt.Id_Materia = m.Id_Materia
          WHERE pmt.Id_Prof = :p AND c.Id_Curso = :c
          ORDER BY m.Nome_Materia
        ");
        $st->execute(['p'=>$idProf, 'c'=>$idCurso]);
      }
      ok($st->fetchAll(PDO::FETCH_ASSOC));
    }

    // ==================== QUESTÕES ====================
    if ($op === 'listQuestoes') {
      $idMateria = intval($_POST['idMateria'] ?? 0);
      if (!$idMateria) err('Matéria inválida.');

      $sql = "
        SELECT q.Id_Quest AS Id_Questao, 
               q.Enunciado, 
               q.Tipo_Questao, 
               q.Nivel_Dificuldade, 
               m.Nome_Materia
        FROM questao q
        INNER JOIN materias m ON m.Id_Materia = q.Id_Materia
        WHERE q.Id_Materia = :m
      ";
      $params = ['m'=>$idMateria];

      if (!$isAdmin) {
        $sql .= " AND q.Id_Prof = :p";
        $params['p'] = $idProf;
      }

      $sql .= " ORDER BY q.Id_Quest DESC";

      $st = $conn->prepare($sql);
      $st->execute($params);
      ok($st->fetchAll(PDO::FETCH_ASSOC));
    }

    // ==================== EXCLUIR ====================
    if ($op === 'deleteQuestao') {
      $id = intval($_POST['id'] ?? 0);
      if (!$id) err('ID inválido.');

      if ($isAdmin) {
        $st = $conn->prepare("DELETE FROM questao WHERE Id_Quest = :id");
        $st->execute(['id'=>$id]);
      } else {
        $st = $conn->prepare("DELETE FROM questao WHERE Id_Quest = :id AND Id_Prof = :p");
        $st->execute(['id'=>$id, 'p'=>$idProf]);
      }
      ok([], 'Questão excluída!');
    }

    err('Operação inválida.');

  } catch (Exception $e) {
    err($e->getMessage());
  }
}

/* ===========================================================
   FRONTEND (HTML + JS)
   =========================================================== */

/* Cursos que o professor tem acesso */
if ($isAdmin) {
  $st = $conn->query("SELECT Id_Curso, Nome_Curso, Sigla FROM curso ORDER BY Nome_Curso");
} else {
  $st = $conn->prepare("SELECT DISTINCT c.Id_Curso, c.Nome_Curso, c.Sigla
                        FROM curso c
                        JOIN materias m ON m.Id_Curso = c.Id_Curso
                        JOIN professor_materia_turma pmt ON pmt.Id_Materia = m.Id_Materia
                        WHERE pmt.Id_Prof = :p
                        ORDER BY c.Nome_Curso");
  $st->execute(['p'=>$idProf]);
}
$cursos = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Minhas Questões</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* =======================
   BASE
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
  padding:20px 56px;
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
   BOX
   ======================= */
.box{
  background:#fff;
  width:90%;
  max-width:1100px;
  border-radius:18px;
  padding:22px;
  box-shadow:0 0 12px rgba(0,0,0,.1);
  margin-bottom:22px;
}
.box-title{
  font-size:20px;
  font-weight:700;
  color:#032b73;
  margin-bottom:14px;
  text-align:center;
}

/* =======================
   FORM SELECTS
   ======================= */
select{
  width:100%;
  padding:10px;
  border-radius:8px;
  border:1px solid #ccc;
  background:#e3e9ff;
  font-size:15px;
  height:42px;
}

/* =======================
   TABLE
   ======================= */
.table{
  width:100%;
  border-collapse:collapse;
  margin-top:14px;
}
.table th{
  background:#032b73;
  color:#fff;
  padding:10px;
}
.table td{
  padding:10px;
  border-bottom:1px solid #e7e7e7;
  text-align:center;
}
.table tr:hover{
  background:#eef3ff;
}

/* ÍCONES */
.icon-btn{
  background:none;
  border:none;
  cursor:pointer;
  font-size:18px;
  padding:6px;
}
.icon-btn.view{ color:#008e0c; }
.icon-btn.edit{ color:#0157ff; }
.icon-btn.delete{ color:#c10000; }
.icon-btn:hover{
  transform:scale(1.2);
  transition:.15s;
}

/* =======================
   FOOTER FIXADO NO FINAL
   ======================= */
footer{
  background:#032b73;
  color:white;
  text-align:center;
  padding:15px;
  font-size:14px;
  margin-top:auto;
}

/* =======================
   BOTÃO VOLTAR RESPONSIVO
   ======================= */
.btn-voltar{
  position:fixed;
  bottom:20px;
  right:20px;
  background:#555;
  color:#fff;
  border:none;
  border-radius:8px;
  padding:10px 16px;
  font-size:15px;
  cursor:pointer;
  box-shadow:0 3px 6px rgba(0,0,0,0.2);
  z-index:9999;
}

/* =======================
   RESPONSIVIDADE
   ======================= */

/* Tablets (até 992px) */
@media (max-width:992px){
  header{
    padding:18px 20px;
    font-size:20px;
  }
  header .logout{
    right:15px;
    top:12px;
    padding:8px 12px;
    font-size:14px;
  }
  .box{ padding:18px; }
}

/* Celulares grandes (até 768px) */
@media (max-width:768px){
  main{ padding:20px; }

  .box{
    width:100%;
    padding:16px;
  }

  .table th, .table td{
    font-size:14px;
    padding:8px;
  }

  .btn-voltar{
    bottom:15px;
    right:15px;
    padding:8px 12px;
    font-size:14px;
  }
}

/* Celulares pequenos (até 480px) */
@media (max-width:480px){

  header{
    padding:14px;
    font-size:18px;
  }
  header .logout{
    right:10px;
    top:10px;
    padding:6px 10px;
    font-size:12px;
  }

  main{ padding:12px; }

  .box{
    padding:14px;
  }

  select{
    height:40px;
    font-size:14px;
  }

  .table th, .table td{
    font-size:12px;
    padding:6px;
  }

  /* deixa a tabela rolável horizontalmente em telas muito pequenas */
  .table{
    display:block;
    overflow-x:auto;
    white-space:nowrap;
  }

  .btn-voltar{
    bottom:10px;
    right:10px;
    padding:6px 10px;
    font-size:12px;
  }
}

</style>
</head>
<body>
<header>
  Minhas Questões
</header>

<main>
  <div class="box">
    <h2 class="box-title">Filtro</h2>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <div style="flex:1 1 300px">
        <label>Curso:</label>
        <select id="curso" onchange="loadMaterias()">
          <option value="">Selecione</option>
          <?php foreach($cursos as $c): ?>
            <option value="<?= $c['Id_Curso'] ?>"><?= htmlspecialchars($c['Nome_Curso']) ?> (<?= $c['Sigla'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="flex:1 1 300px">
        <label>Matéria:</label>
        <select id="materia" onchange="carregarQuestoes()">
          <option value="">Selecione</option>
        </select>
      </div>
    </div>
  </div>

  <div class="box">
    <h2 class="box-title">Questões</h2>
    <table class="table" id="tbQuestoes">
      <thead><tr><th>ID</th><th>Enunciado</th><th>Tipo</th><th>Dificuldade</th><th>Ações</th></tr></thead>
      <tbody><tr><td colspan="5">Selecione uma matéria</td></tr></tbody>
    </table>
  </div>
</main>

<footer>© <?= date("Y") ?> - SistemaX</footer>

<script>
const $ = s=>document.querySelector(s);

function loadMaterias(){
  const idCurso = $('#curso').value;
  const sel = $('#materia');
  sel.innerHTML = '<option>Carregando...</option>';
  fetch('minhas_questoes.php', {
    method:'POST',
    body:new URLSearchParams({ajax:1, op:'materias', idCurso})
  }).then(r=>r.json()).then(d=>{
    sel.innerHTML = '<option value="">Selecione</option>';
    if(!d.ok) return;
    d.rows.forEach(m=>{
      sel.innerHTML += `<option value="${m.Id_Materia}">${m.Nome_Materia}</option>`;
    });
  });
}

function carregarQuestoes(){
  const idMateria = $('#materia').value;
  if(!idMateria) return;
  fetch('minhas_questoes.php', {
    method:'POST',
    body:new URLSearchParams({ajax:1, op:'listQuestoes', idMateria})
  }).then(r=>r.json()).then(d=>{
    const tb = $('#tbQuestoes tbody');
    tb.innerHTML = '';
    if(!d.ok || d.rows.length===0){
      tb.innerHTML = '<tr><td colspan="5">Nenhuma questão encontrada</td></tr>';
      return;
    }
    d.rows.forEach(q=>{
      tb.innerHTML += `
        <tr>
          <td>${q.Id_Questao}</td>
          <td>${q.Enunciado}</td>
          <td>${q.Tipo_Questao}</td>
          <td>${q.Nivel_Dificuldade}</td>
          <td>
            <button class="icon-btn view" onclick="window.location='inserir_questao.php?id=${q.Id_Questao}'">👁️</button>
            <button class="icon-btn edit" onclick="window.location='inserir_questao.php?id=${q.Id_Questao}&edit=1'">✏️</button>
            <button class="icon-btn delete" onclick="deleteQuestao(${q.Id_Questao})">🗑️</button>
          </td>
        </tr>`;
    });
  });
}

function deleteQuestao(id){
  if(!confirm('Deseja realmente excluir esta questão?')) return;
  fetch('minhas_questoes.php', {
    method:'POST',
    body:new URLSearchParams({ajax:1, op:'deleteQuestao', id})
  }).then(r=>r.json()).then(d=>{
    alert(d.msg);
    if(d.ok) carregarQuestoes();
  });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', ()=>{
  // Evita duplicar o botão se já existir um personalizado
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
  `;
  btnVoltar.onclick = ()=>{
    window.location.href = "home.php"; 
};


  document.body.appendChild(btnVoltar);
});
</script>

</body>
</html>
