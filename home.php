<?php
session_start();
include("conexao.php");

if (!isset($_SESSION["Id_Prof"])) {
  header("Location: login.php");
  exit;
}
$isAdmin = isset($_SESSION["is_admin"]) && intval($_SESSION["is_admin"]) === 1;

$profPend = [];
if ($isAdmin) {
  $stmt = $conn->query("SELECT Id_Prof, Nome, CPF, Email FROM professor WHERE Aprovado = 0 ORDER BY Nome ASC");
  $profPend = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Home - SistemaX</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<style>
/* ====== Base ====== */
*{box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#f4f6fc;margin:0}
a{color:inherit}

/* Header */
header.topo{
  background:#032b73;
  color:#fff;
  text-align:center;
  padding:20px 56px;
  font-size:22px;
  font-weight:700;
  position:relative
}
header.topo .logout{
  position:absolute;
  right:25px;
  top:15px;
  background:#b30000;
  padding:10px 20px;
  border-radius:8px;
  color:#fff;
  font-weight:700;
  text-decoration:none
}
header.topo .logout:hover{background:#e21818}

/* Layout */
main{
  padding:32px 0;
  display:flex;
  flex-direction:column;
  align-items:center
}

.card-row{
  display:flex;
  gap:22px;
  flex-wrap:wrap;
  justify-content:center;
  margin-bottom:24px
}

.card{
  width:170px;
  height:130px;
  background:#fff;
  border-radius:15px;
  box-shadow:0 0 12px rgba(0,0,0,.1);
  display:flex;
  align-items:center;
  justify-content:center;
  flex-direction:column;
  cursor:pointer;
  transition:.18s;
  font-weight:600;
  color:#032b73;
  text-align:center
}
.card:hover{transform:scale(1.04);background:#eef3ff}

/* Box */
.box{
  background:#fff;
  width:90%;
  max-width:1100px;
  border-radius:18px;
  padding:22px;
  box-shadow:0 0 12px rgba(0,0,0,.1);
  margin-top:22px
}
.box-title{font-size:20px;font-weight:700;color:#032b73;margin:0 0 14px;text-align:center}

.admin-cards{
  display:flex;
  gap:14px;
  flex-wrap:wrap;
  justify-content:center
}

.admin-card{
  width:150px;height:110px;
  background:#1c4789;
  color:#fff;
  border-radius:14px;
  display:flex;
  align-items:center;
  justify-content:center;
  text-align:center;
  font-weight:700;
  cursor:pointer;
  transition:.18s
}
.admin-card:hover{background:#225aa7;transform:scale(1.04)}

/* Table */
.table{width:100%;border-collapse:collapse}
.table th{
  background:#032b73;color:#fff;padding:10px;font-weight:600
}
.table td{
  padding:10px;
  border-bottom:1px solid #e7e7e7;
  text-align:center
}

.table-sm{width:100%;border-collapse:collapse}
.table-sm th,.table-sm td{
  border-bottom:1px solid #eee;
  padding:8px;
  text-align:left
}

/* Inputs */
input[type=text],select{
  width:100%;
  padding:10px;
  border-radius:8px;
  border:1px solid #ccc;
  background:#e3e9ff;
  font-size:15px;
  height:42px
}

.search{
  width:100%;
  padding:10px;
  border-radius:8px;
  border:1px solid #ccc;
  background:#fff;
  height:42px
}

.row{display:flex;gap:12px;flex-wrap:wrap}
.col{flex:1 1 220px}
.center{text-align:center}

/* Buttons */
.btn{
  height:42px;
  padding:0 16px;
  border:none;
  border-radius:8px;
  background:#032b73;
  color:#fff;
  font-weight:700;
  cursor:pointer;
  isplay:flex;d
  align-items:center;
  justify-content:center;
  min-width:140px;
  font-size:14px;
  text-align:center;
  transition:0.15s;
}
.btn:hover{
  background:#0a3fa5;
}
.table td:last-child {
    text-align: center;
}

.btn-danger{
  background:#c10000;
}
.btn-danger:hover{
  background:#e21818;
}


.btn.secondary{background:#666}
.btn-danger{background:#c10000}
.btn-ghost{background:transparent;border:1px solid #ccc;color:#333}

/* Modal base */
.modal-bg{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.5);
  display:none;
  justify-content:center;
  align-items:center;
  z-index:999;
  padding:10px
}

.modal{
  background:#fff;
  width:100%;
  max-width:1000px;
  border-radius:16px;
  box-shadow:0 10px 30px rgba(0,0,0,.25);
  overflow:hidden
}

.modal-header{
  background:#032b73;color:#fff;
  padding:14px 18px;
  display:flex;
  justify-content:space-between;
  align-items:center
}

.modal-title{font-weight:700}
.modal-close{cursor:pointer;font-size:22px}

.modal-body{
  padding:18px;
  display:flex;
  flex-direction:column;
  gap:14px
}

.icon-btn{
  background:none;
  border:none;
  cursor:pointer;
  font-size:18px;
  padding:6px;
  display:inline-flex;
  align-items:center
}
.icon-btn.edit{color:#0157ff}
.icon-btn.delete{color:#c10000}
.icon-btn:hover{opacity:.7;transform:scale(1.15);transition:.15s}

/* System modal */
.syslayer{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.45);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:2000;
  padding:10px
}

.sysdialog{
  width:100%;
  max-width:440px;
  background:#0b1a33;
  color:#fff;
  border-radius:14px;
  box-shadow:0 10px 30px rgba(0,0,0,.3);
  overflow:hidden
}

.syshead{background:#0f2b6a;padding:12px 16px;font-weight:700}
.sysbody{padding:16px;font-size:15px;line-height:1.35}
.sysfoot{display:flex;gap:10px;justify-content:flex-end;padding:12px 16px;background:#0e1f40}

/* Buttons sys */
.sysbtn{
  border:none;
  border-radius:8px;
  padding:9px 14px;
  font-weight:700;
  cursor:pointer;
  font-size:14px
}
.sysbtn.ok{background:#00a82d;color:#fff}
.sysbtn.warn{background:#b30000;color:#fff}
.sysbtn.ghost{background:transparent;border:1px solid #7aa2ff;color:#d7e3ff}

/* Footer */
footer{
  background:#032b73;
  color:#fff;
  text-align:center;
  padding:15px;
  margin-top:40px
}

/* ============================================================
   ⏬ RESPONSIVIDADE — MOBILE & TABLET ⏬
   ============================================================ */

@media (max-width: 900px){
  header.topo{padding:20px;font-size:19px}
  header.topo .logout{
    top:12px;right:12px;padding:8px 14px;font-size:14px
  }

  .card{width:140px;height:110px}
}

@media (max-width: 700px){

  .card-row{gap:16px}
  .card{
    width:48%;
    height:110px;
    font-size:15px;
  }

  .admin-card{width:48%;height:95px}

  .modal{max-width:95%}
  .row{flex-direction:column;gap:6px}
  .col{width:100%}
  #boxSigla, #boxQtdPeriodos, #boxTurmaNome{
    max-width:100%!important
  }

  table.table-sm th, table.table-sm td{
    font-size:13px;
    padding:6px
  }

  .btn{width:100%;min-width:auto;margin-top:6px}
}

@media (max-width: 500px){

  header.topo{
    padding:16px;
    font-size:18px;
  }

  .card{
    width:100%;
    height:100px;
    font-size:15px;
  }

  .admin-card{
    width:100%;
    height:90px
  }

  .box{padding:16px}
  .box-title{font-size:18px}

  table.table th, table.table td{
    font-size:12px;
    padding:6px
  }

  .modal-body{padding:12px}
}

html, body{
  height:100%;
}

body{
  display:flex;
  flex-direction:column;
}

main{
  flex:1;
  display:flex;
  flex-direction:column;
  align-items:center;
}

</style>
</head>
<body>

<header class="topo">
  Bem-vindo, <?= htmlspecialchars($_SESSION["nome"]) ?>!
  <a class="logout" href="logout.php">Sair</a>
</header>

<main>

<?php 
$msg = $_GET["msg"] ?? '';

$mensagensValidas = [
    "aprovado"  => "Professor aprovado com sucesso!",
    "reprovado" => "Professor reprovado!",
    "permissao" => "❌ Você não tem permissão."
];

if (!empty($msg) && isset($mensagensValidas[$msg])): ?>
    <div style="
        background:#d4edda;
        color:#155724;
        padding:10px 15px;
        border-radius:8px;
        margin-bottom:20px;
        font-weight:600;
        box-shadow:0 2px 6px rgba(0,0,0,0.15);
        text-align:center;
        max-width:500px;
        margin: 20px auto;
    ">
        <?= $mensagensValidas[$msg] ?>
    </div>
<?php endif; ?>




  <div class="card-row">
    <div class="card" onclick="window.location='inserir_questao.php'">Criar Questão</div>
    <div class="card" onclick="window.location='minhas_questoes.php'">Minhas Questões</div>
    <div class="card" onclick="window.location='criar_prova.php'">Criar Prova</div>
    <div class="card" onclick="window.location='perfil.php'">Meu Perfil</div>
  </div>

  <?php if ($isAdmin): ?>
  <div class="box">
    <h2 class="box-title">Administração</h2>
    <div class="admin-cards">
      <div class="admin-card" onclick="openModal('cursos')">Cursos</div>
      <div class="admin-card" onclick="openModal('materias')">Matérias</div>
      <div class="admin-card" onclick="openModal('turmas')">Turmas</div>
    </div>
  </div>

  <div class="box">
    <h2 class="box-title">Professores Pendentes</h2>
    <?php if (count($profPend) === 0): ?>
      <p class="center">Nenhum professor pendente 🎉</p>
    <?php else: ?>
      <table class="table">
        <thead><tr><th>ID</th><th>Nome</th><th>CPF</th><th>Email</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach($profPend as $p): ?>
          <tr>
            <td><?= $p['Id_Prof'] ?></td>
            <td><?= htmlspecialchars($p['Nome']) ?></td>
            <td><?= htmlspecialchars($p['CPF']) ?></td>
            <td><?= htmlspecialchars($p['Email']) ?></td>
            <td>
  <button class="btn" onclick="window.location='aprovar_prof.php?id=<?= $p['Id_Prof'] ?>'">
     Aprovar
  </button>

  <button class="btn btn-danger" onclick="window.location='reprovar_prof.php?id=<?= $p['Id_Prof'] ?>'">
     Reprovar
  </button>
</td>

          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</main>

<footer>© <?= date("Y") ?> - SistemaX</footer>

<!-- ================= MODAIS DE CRUD ================= -->

<!-- ============ CURSOS ============ -->
<div id="modal-cursos" class="modal-bg">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Cursos</span>
      <span class="modal-close" onclick="closeModal('cursos')">✖</span>
    </div>
    <div class="modal-body">

      <input type="hidden" id="curso_edit_id">

      <div class="row">
        <div class="col">
          <label>Nome do Curso</label>
          <input id="curso_input" type="text" class="search"
            placeholder="Digite para buscar ou cadastrar..."
            oninput="cursosSearch(this.value)">
        </div>

        <div id="boxQtdPeriodos" class="col">
          <label>Qtd. de Períodos</label>
          <select id="curso_qtd">
            <?php for($i=1;$i<=12;$i++): ?>
              <option value="<?=$i?>"><?=$i?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="col" id="boxSigla" style="display:none">
          <label>Sigla</label>
          <input id="curso_sigla" type="text" maxlength="6" class="search" placeholder="SIGLA">
        </div>

        <div class="col" style="align-self:flex-end">
          <button id="btnSalvarCurso" class="btn" onclick="cursosSalvar()">Cadastrar Curso</button>
          <button id="btnCancelarEditCurso" class="btn secondary" style="display:none"
            onclick="cursoCancelarEdicao()">Cancelar</button>
        </div>
      </div>

      <table class="table-sm" id="tbcursos">
        <thead>
          <tr><th>Curso</th><th>Sigla</th><th>Períodos</th><th style="width:120px">Ações</th></tr>
        </thead>
        <tbody></tbody>
      </table>

    </div>
  </div>
</div>

<!-- ============ MATÉRIAS ============ -->
<div id="modal-materias" class="modal-bg">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Matérias</span>
      <span class="modal-close" onclick="closeModal('materias')">✖</span>
    </div>
    <div class="modal-body">

      <input type="hidden" id="mat_edit_id">

      <div class="row">
        <div class="col">
          <label>Curso</label>
          <select id="mat_curso" onchange="loadPeriodosByCurso(this.value)"><option value="">Selecione</option></select>
        </div>
        <div class="col">
          <label>Período</label>
          <select id="mat_periodo"><option value="">Selecione</option></select>
        </div>
        <div class="col">
          <label>Nome da Matéria</label>
          <input id="mat_nome" type="text" class="search" placeholder="Digite para buscar ou cadastrar...">
        </div>
        <div class="col" style="align-self:flex-end">
          <button id="btnSalvarMateria" class="btn" onclick="materiasSalvar()">Cadastrar Matéria</button>
          <button id="btnCancelarEditMateria" class="btn secondary" style="display:none" onclick="materiaCancelarEdicao()">Cancelar</button>
        </div>
      </div>

      <table class="table-sm" id="tbmaterias">
        <thead><tr><th>Matéria</th><th>Curso</th><th>Período</th><th style="width:120px">Ações</th></tr></thead>
        <tbody></tbody>
      </table>

    </div>
  </div>
</div>

<!-- ============ TURMAS ============ -->
<div id="modal-turmas" class="modal-bg">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Turmas</span>
      <span class="modal-close" onclick="closeModal('turmas')">✖</span>
    </div>
    <div class="modal-body">

      <input type="hidden" id="turma_edit_id">

      <div class="row">
        <div class="col">
          <label>Curso</label>
          <select id="turma_curso" onchange="loadPeriodosSelect('turma_curso','turma_periodo', ()=>{ loadMateriasByCursoPeriodo(); turmasList(); })"></select>
        </div>
        <div class="col">
          <label>Período</label>
          <select id="turma_periodo" onchange="loadMateriasByCursoPeriodo(); turmasList();"></select>
        </div>
        <div class="col">
          <label>Matéria</label>
          <select id="turma_materia"></select>
        </div>
        <div class="col" id="boxTurmaNome">
          <label>Nome da Turma</label>
          <input id="turma_nome" type="text" required class="search" placeholder="Nome da turma">
        </div>
      </div>

      <div class="row" style="margin-top:0">
        <div class="col">
          <label>Professor (opcional)</label>
          <select id="turma_prof"></select>
        </div>
        <div class="col">
          <label>Turno</label>
          <select id="turma_turno">
            <option value="">Selecione</option>
            <option value="Matutino">Matutino</option>
            <option value="Vespertino">Vespertino</option>
            <option value="Noturno">Noturno</option>
            <option value="Integral">Integral</option>
          </select>
        </div>
        <div class="col" style="align-self:flex-end">
          <button id="btnTurmaAcao" class="btn" onclick="turmasCreate()">Criar Turma Automática</button>
          <button id="btnCancelarEditTurma" class="btn secondary" style="display:none" onclick="turmaCancelarEdicao()">Cancelar</button>
        </div>
      </div>

      <input type="text" class="search" placeholder="Pesquisar..." onkeyup="filterTable('tbturmas', this.value)">

      <table class="table-sm" id="tbturmas">
        <thead>
          <tr>
            <th>Turma</th>
            <th>Período</th>
            <th>Curso</th>
            <th>Matéria</th>
            <th>Professor</th>
            <th>Turno</th>
            <th style="width:120px">Ações</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

    </div>
  </div>
</div>

<!-- ================= MODAL DE SISTEMA (A+B) ================= -->
<div id="syslayer" class="syslayer">
  <div class="sysdialog">
    <div id="syshead" class="syshead">Sistema</div>
    <div id="sysbody" class="sysbody">...</div>
    <div id="sysfoot" class="sysfoot"></div>
  </div>
</div>

<script>
/* =============== Helpers de Modal de Sistema (A + B) =============== */
const $ = sel => document.querySelector(sel);

function sysShow({title='Sistema', message='', buttons=[{label:'OK',class:'ok',value:true}]}){
  $('#syshead').textContent = title;
  $('#sysbody').innerHTML = message;
  const foot = $('#sysfoot');
  foot.innerHTML = '';
  return new Promise(resolve=>{
    buttons.forEach(b=>{
      const btn = document.createElement('button');
      btn.className = `sysbtn ${b.class||'ok'}`;
      btn.textContent = b.label;
      btn.onclick = ()=>{ sysHide(); resolve(b.value); };
      foot.appendChild(btn);
    });
    $('#syslayer').style.display = 'flex';
  });
}
function sysHide(){ $('#syslayer').style.display = 'none'; }

function info(title, msg){ return sysShow({title, message: msg}); }
function confirmBox(title, msg){
  return sysShow({
    title, message: msg,
    buttons: [
      {label:'Cancelar', class:'ghost', value:false},
      {label:'Confirmar', class:'ok', value:true}
    ]
  });
}

/* =============== Infra básica =============== */
const modals = {
  cursos  : document.getElementById('modal-cursos'),
  materias: document.getElementById('modal-materias'),
  turmas  : document.getElementById('modal-turmas'),
};

function openModal(key){
  modals[key].style.display = 'flex';
  if(key==='cursos'){ cursosSearch(''); }
  if(key==='materias'){
    loadCursosSelect('mat_curso', ()=>loadPeriodosSelect('mat_curso','mat_periodo', ()=>loadMateriasTable($('#mat_curso').value||'')));
  }
  if(key==='turmas'){
    loadCursosSelect('turma_curso', ()=>loadPeriodosSelect('turma_curso','turma_periodo', ()=>{
      loadMateriasByCursoPeriodo();
      turmasList();
    }));
    loadProfessores('turma_prof');
    turmaCancelarEdicao(); // garantir estado de criação
  }
}
function closeModal(key){ modals[key].style.display='none'; }

function filterTable(tableId, query){
  query = (query||'').toLowerCase().trim();
  document.querySelectorAll(`#${tableId} tbody tr`).forEach(r=>{
    r.style.display = r.textContent.toLowerCase().includes(query) ? '' : 'none';
  });
}
function formData(obj){ const f=new FormData(); Object.entries(obj||{}).forEach(([k,v])=>f.append(k,v)); return f; }

/* =============== CURSOS =============== */
function cursosSearch(val){
  fetch('actions/cursos_action.php',{method:'POST', body:formData({op:'search',q:val})})
  .then(r=>r.json()).then(d=>{
    const tb = document.querySelector('#tbcursos tbody');
    tb.innerHTML = '';
    if(!d.ok || d.rows.length===0){
      tb.innerHTML = '<tr><td colspan="4" class="center">Nenhum curso encontrado</td></tr>';
      return;
    }
    d.rows.forEach(c=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${c.Nome_Curso}</td>
        <td>${c.Sigla||''}</td>
        <td>${c.Qtd_Periodos||'-'}</td>
        <td>
          <button class="icon-btn edit" title="Editar" onclick="cursoEditar(${c.Id_Curso})">✏️</button>
          <button class="icon-btn delete" title="Excluir" onclick="cursoExcluir(${c.Id_Curso})">🗑️</button>
        </td>`;
      tb.appendChild(tr);
    });
  });
}

function cursosSalvar(){
  const id = $('#curso_edit_id').value;
  const nome = $('#curso_input').value.trim();
  const sigla = $('#curso_sigla').value.trim();
  const qtd = $('#curso_qtd').value;

  if(!nome){ info('Cursos','Informe o nome do curso.'); return; }

  const payload = id
    ? {op:'update', id, nome, sigla}
    : {op:'create', nome, qtd};

  fetch('actions/cursos_action.php',{method:'POST', body:formData(payload)})
  .then(r=>r.json()).then(d=>{
    info('Cursos', d.msg || (d.ok ? 'Operação realizada!' : 'Não foi possível salvar.'));
    if(d.ok){
      cursosSearch('');
      cursoCancelarEdicao();
      loadCursosSelect('mat_curso');
      loadCursosSelect('turma_curso');
    }
  });
}

function cursoEditar(id){
  fetch('actions/cursos_action.php', {
    method:'POST',
    body:formData({op:'get', id})
  })
  .then(r=>r.json())
  .then(d=>{
    if(!d.ok || !d.rows[0]) return;
    const c = d.rows[0];

    // Preenche dados
    $('#curso_edit_id').value = c.Id_Curso;
    $('#curso_input').value = c.Nome_Curso || '';
    $('#curso_sigla').value = c.Sigla || '';

    // Mostra campo sigla e oculta campo de períodos
    $('#boxSigla').style.display = '';
    $('#boxQtdPeriodos').style.display = 'none';

    // Atualiza botões com o padrão do sistema
    $('#btnSalvarCurso').textContent = '💾 Salvar Alterações';
    $('#btnSalvarCurso').className = 'btn';
    $('#btnCancelarEditCurso').style.display = '';
    $('#btnCancelarEditCurso').className = 'btn secondary';
  });
}

function cursoCancelarEdicao(){
  $('#curso_edit_id').value = '';
  $('#curso_input').value = '';
  $('#curso_sigla').value = '';

  // Volta ao modo de criação
  $('#boxSigla').style.display = 'none';
  $('#boxQtdPeriodos').style.display = '';

  // Botões no padrão azul
  $('#btnSalvarCurso').textContent = '➕ Cadastrar Curso';
  $('#btnSalvarCurso').className = 'btn';
  $('#btnCancelarEditCurso').style.display = 'none';
}


async function cursoExcluir(id){
  const ok = await confirmBox('Excluir Curso','Tem certeza que deseja excluir este curso? <br>Se houver períodos/matérias/turmas vinculadas, a operação será <b>bloqueada</b>.');
  if(!ok) return;
  const res = await fetch('actions/cursos_action.php',{method:'POST', body:formData({op:'delete',id})}).then(r=>r.json());
  info('Cursos', res.msg || (res.ok ? 'Excluído!' : 'Não foi possível excluir.'));
  cursosSearch('');
}

/* =============== SELECTS DE APOIO =============== */
function loadCursosSelect(selectId, cb){
  fetch('actions/cursos_action.php',{method:'POST',body:formData({op:'list'})})
  .then(r=>r.json()).then(d=>{
    const sel = document.getElementById(selectId);
    if(!sel) return;
    sel.innerHTML = '<option value="">Selecione</option>';
    if(!d.ok) return;
    d.rows.forEach(c=> sel.innerHTML += `<option value="${c.Id_Curso}">${c.Nome_Curso} (${c.Sigla||''})</option>`);
    if(typeof cb==='function') cb();
  });
}
function loadPeriodosSelect(selectCursoId, selectPeriodoId, cb){
  const idCurso = document.getElementById(selectCursoId).value;
  const sel = document.getElementById(selectPeriodoId);
  sel.innerHTML = '<option value="">Selecione</option>';
  if(!idCurso){ if(typeof cb==='function') cb(); return; }
  fetch('actions/periodos_action.php',{method:'POST',body:formData({op:'bycurso',curso:idCurso})})
  .then(r=>r.json()).then(d=>{
    if(!d.ok) return;
    d.rows.forEach(p=> sel.innerHTML += `<option value="${p.Id_Periodo}">${p.Nome_Periodo}</option>`);
    if(typeof cb==='function') cb();
  });
}
function loadProfessores(selectId){
  fetch('actions/turmas_action.php',{method:'POST',body:formData({op:'listProf'})})
  .then(r=>r.json()).then(d=>{
    const sel = document.getElementById(selectId);
    sel.innerHTML = '<option value="">Selecione</option>';
    if(!d.ok) return;
    d.rows.forEach(p=> sel.innerHTML += `<option value="${p.Id_Prof}">${p.Nome}</option>`);
  });
}

/* =============== MATÉRIAS =============== */
function loadPeriodosByCurso(idCurso){
  if(!idCurso) return;
  fetch('actions/periodos_action.php',{method:'POST',body:formData({op:'bycurso',curso:idCurso})})
  .then(r=>r.json()).then(d=>{
    const sel = $('#mat_periodo');
    sel.innerHTML = '<option value="">Selecione</option>';
    if(!d.ok) return;
    d.rows.forEach(p=> sel.innerHTML += `<option value="${p.Id_Periodo}">${p.Nome_Periodo}</option>`);
  });
  loadMateriasTable(idCurso);
}

function loadMateriasTable(idCurso){
  fetch('actions/materias_action.php',{method:'POST',body:formData({op:'list',id_curso:idCurso})})
  .then(r=>r.json()).then(d=>{
    const tb = $('#tbmaterias tbody');
    tb.innerHTML = '';
    if(!d.ok || d.rows.length===0){
      tb.innerHTML = "<tr><td colspan='4' class='center'>Nenhuma matéria</td></tr>";
      return;
    }
    d.rows.forEach(m=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${m.Nome_Materia}</td>
        <td>${m.Nome_Curso}</td>
        <td>${m.Nome_Periodo || (m.NumeroPeriodo? (m.NumeroPeriodo+'º Período'):'-')}</td>
        <td>
          <button class="icon-btn edit" title="Editar" onclick="materiaEditar(${m.Id_Materia}, '${(m.Nome_Materia||'').replaceAll(`'`,`\\'`)}')">✏️</button>
          <button class="icon-btn delete" title="Excluir" onclick="materiaExcluir(${m.Id_Materia})">🗑️</button>
        </td>`;
      tb.appendChild(tr);
    });
  });
}

function materiasSalvar(){
  const id   = $('#mat_edit_id').value;
  const nome = ($('#mat_nome').value||'').trim();
  const curso  = $('#mat_curso').value;
  const periodo= $('#mat_periodo').value;

  if(!curso || !periodo || !nome){ info('Matérias','Preencha <b>Curso</b>, <b>Período</b> e <b>Nome</b>.'); return; }

  const payload = id
    ? {op:'update', id, nome}
    : {op:'create', nome, curso, periodo};

  fetch('actions/materias_action.php',{method:'POST', body:formData(payload)})
  .then(r=>r.json()).then(d=>{
    info('Matérias', d.msg || (d.ok ? 'Operação realizada!' : 'Não foi possível salvar.'));
    if(d.ok){
      loadMateriasTable(curso);
      if(!id) $('#mat_nome').value='';
      materiaCancelarEdicao();
    }
  });
}

/* ================== MATÉRIAS ================== */
// EDITAR (carrega dados, bloqueia curso e popula período)
function materiaEditar(id){
  fetch('actions/materias_action.php', {
    method: 'POST',
    body: formData({ op: 'get', id })
  })
  .then(r => r.json())
  .then(d => {
    if (!d.ok || !d.row) return;
    const m = d.row;

    // Preenche campos
    $('#mat_edit_id').value = m.Id_Materia;
    $('#mat_nome').value = m.Nome_Materia || '';

    // Preenche e bloqueia o curso
    $('#mat_curso').value = m.Id_Curso || '';
    $('#mat_curso').disabled = true;

    // Carrega os períodos correspondentes ao curso e seleciona o correto
    loadPeriodosSelect('mat_curso', 'mat_periodo', () => {
      $('#mat_periodo').value = m.Id_Periodo || '';
    });

    // Atualiza botões (mantendo padrão original)
    $('#btnSalvarMateria').textContent = 'Salvar Alterações';
    $('#btnCancelarEditMateria').style.display = '';
  });
}

function materiasSalvar(){
  const id   = $('#mat_edit_id').value;
  const nome = ($('#mat_nome').value || '').trim();
  const curso  = $('#mat_curso').value;
  const periodo = $('#mat_periodo').value;

  if(!curso || !periodo || !nome){ 
    info('Matérias','Preencha <b>Curso</b>, <b>Período</b> e <b>Nome</b>.'); 
    return; 
  }

  const payload = id
    ? { op:'update', id, nome, curso, periodo }
    : { op:'create', nome, curso, periodo };

  fetch('actions/materias_action.php', {
    method:'POST',
    body: formData(payload)
  })
  .then(r => r.json())
  .then(d => {
    info('Matérias', d.msg || (d.ok ? 'Operação realizada!' : 'Erro ao salvar.'));
    if (d.ok) {
      loadMateriasTable(curso);
      materiaCancelarEdicao();
    }
  });
}

function materiaCancelarEdicao(){
  $('#mat_edit_id').value = '';
  $('#mat_nome').value = '';
  $('#mat_curso').disabled = false;

  $('#btnSalvarMateria').textContent = 'Cadastrar Matéria';
  $('#btnCancelarEditMateria').style.display = 'none';
}





async function materiaExcluir(id){
  const ok = await confirmBox('Excluir Matéria','Tem certeza que deseja excluir?<br>Se houver turmas vinculadas, a operação será <b>bloqueada</b>.');
  if(!ok) return;
  const res = await fetch('actions/materias_action.php',{method:'POST',body:formData({op:'delete',id})}).then(r=>r.json());
  info('Matérias', res.msg || (res.ok?'Excluída!':'Não foi possível excluir.'));
  const c = $('#mat_curso').value || '';
  if(c) loadMateriasTable(c);
}

/* =============== TURMAS =============== */
function loadMateriasByCursoPeriodo(){
  const id_curso = $('#turma_curso').value || '';
  const id_periodo = $('#turma_periodo').value || '';
  const sel = $('#turma_materia');
  sel.innerHTML = '<option value="">Selecione</option>';
  if(!id_curso || !id_periodo) return;

  fetch('actions/turmas_action.php',{method:'POST',body:formData({op:'materiasByCursoPeriodo',id_curso,id_periodo})})
  .then(r=>r.json()).then(d=>{
    if(!d.ok) return;
    d.rows.forEach(m=> sel.innerHTML += `<option value="${m.Id_Materia}">${m.Nome_Materia}</option>`);
  });
}

function turmasList(){
  const id_curso = $('#turma_curso').value || '';
  const id_periodo = $('#turma_periodo').value || '';
  fetch('actions/turmas_action.php',{method:'POST',body:formData({op:'list',id_curso,id_periodo})})
  .then(r=>r.json()).then(d=>{
    const tb = $('#tbturmas tbody');
    tb.innerHTML = '';
    if(!d.ok || d.rows.length===0){
      tb.innerHTML = '<tr><td colspan="7" class="center">Nenhuma turma</td></tr>';
      return;
    }
    d.rows.forEach(t=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${t.Nome_Turma}</td>
        <td>${t.NumeroPeriodo}</td>
        <td>${t.Sigla||''}</td>
        <td>${t.Nome_Materia||'-'}</td>
        <td>${t.Nome_Prof||'-'}</td>
        <td>${t.Turno||'-'}</td>
        <td>
          <button class="icon-btn edit" title="Editar" onclick="turmasStartEdit(${t.Id_Turma})">✏️</button>
          <button class="icon-btn delete" title="Excluir" onclick="turmasDelete(${t.Id_Turma})">🗑️</button>
        </td>`;
      tb.appendChild(tr);
    });
  });
}

/* Criação (modo padrão) */
function turmasCreate(){
  const id_curso = $('#turma_curso').value;
  const id_periodo = $('#turma_periodo').value;
  const id_materia = $('#turma_materia').value;
  const id_prof = $('#turma_prof').value || '';
  const turno = $('#turma_turno').value;

  if(!id_curso || !id_periodo || !id_materia || !turno){
    info('Turmas','Selecione <b>Curso</b>, <b>Período</b>, <b>Matéria</b> e <b>Turno</b>.');
    return;
  }
  fetch('actions/turmas_action.php',{method:'POST',body:formData({op:'createAuto',id_curso,id_periodo,id_materia,id_prof,turno})})
  .then(r=>r.json()).then(d=>{
    info('Turmas', d.msg || (d.ok?'Turma criada!':'Não foi possível criar.'));
    if(d.ok) turmasList();
  });
}

/* Entrar no modo edição (nome/prof/turno) */
function turmasStartEdit(id){
  fetch('actions/turmas_action.php',{method:'POST',body:formData({op:'get',id})})
  .then(r=>r.json()).then(d=>{
    if(!d.ok || !d.row) return;
    const t = d.row;

    // Preenche campos existentes
    $('#turma_edit_id').value = t.Id_Turma;
    $('#turma_nome').value = t.Nome_Turma || '';
    $('#turma_turno').value = t.Turno || '';
    $('#turma_prof').value = t.Id_Prof || '';

    // Trava curso/periodo/materia conforme solicitado (não editam)
    $('#turma_curso').value = t.Id_Curso || $('#turma_curso').value;
    // Recarrega períodos para garantir consistência e depois seta
    loadPeriodosSelect('turma_curso','turma_periodo', ()=>{
      $('#turma_periodo').value = t.Id_Periodo || '';
      loadMateriasByCursoPeriodo();
      setTimeout(()=>{ $('#turma_materia').value = t.Id_Materia || ''; }, 150);
    });

    // Ajusta botões
    $('#btnTurmaAcao').textContent = 'Salvar Alterações';
    $('#btnTurmaAcao').onclick = turmasSalvarEdicao;
    $('#btnCancelarEditTurma').style.display = '';
  });
}

/* Salvar edição */
function turmasSalvarEdicao(){
  const id = $('#turma_edit_id').value;
  if(!id){ return; }

  const nome = ($('#turma_nome').value||'').trim();
  const id_prof = $('#turma_prof').value || '';
  const turno = $('#turma_turno').value || '';

  if(!nome){ info('Turmas','Preencha o <b>Nome</b> da turma.'); return; }

  fetch('actions/turmas_action.php',{method:'POST',body:formData({op:'update',id,nome,id_prof,turno})})
  .then(r=>r.json()).then(d=>{
    info('Turmas', d.msg || (d.ok?'Alterações salvas!':'Não foi possível salvar.'));
    if(d.ok){
      turmasList();
      turmaCancelarEdicao();
    }
  });
}

function turmaCancelarEdicao(){
  $('#turma_edit_id').value = '';
  $('#turma_nome').value = '';
  $('#turma_turno').value = '';
  $('#turma_prof').value = '';

  $('#btnTurmaAcao').textContent = 'Criar Turma Automática';
  $('#btnTurmaAcao').onclick = turmasCreate;
  $('#btnCancelarEditTurma').style.display = 'none';
}

async function turmasDelete(id){
  const ok = await confirmBox('Excluir Turma','Tem certeza que deseja excluir?<br>O vínculo com o professor será removido.');
  if(!ok) return;
  const res = await fetch('actions/turmas_action.php',{method:'POST',body:formData({op:'delete',id})}).then(r=>r.json());
  info('Turmas', res.msg || (res.ok?'Excluída!':'Não foi possível excluir.'));
  turmasList();
}
</script>

</body>
</html>
