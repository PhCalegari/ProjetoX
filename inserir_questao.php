<?php
session_start();
include("conexao.php");

$idQuest = isset($_GET['id']) ? intval($_GET['id']) : 0;
$editMode = isset($_GET['edit']) ? true : false;
$questao = [];

if ($idQuest > 0) {
    $st = $conn->prepare("SELECT * FROM questao WHERE Id_Quest = :id");
    $st->execute(['id'=>$idQuest]);
    $questao = $st->fetch(PDO::FETCH_ASSOC);
    if (!$questao) {
        die("<h3 style='color:red;text-align:center'>Questão não encontrada.</h3>");
    }
}
// ====== Carrega alternativas da questão (se houver) ======
$alternativas = [];
if ($idQuest > 0) {
    $stmtAlt = $conn->prepare("SELECT * FROM alternativa_questao WHERE Id_Quest = ?");
    $stmtAlt->execute([$idQuest]);
    $alternativas = $stmtAlt->fetchAll(PDO::FETCH_ASSOC);
}



include("conexao.php");

if (!isset($_SESSION["Id_Prof"])) {
    header("Location: login.php");
    exit;
}

$idProf = intval($_SESSION["Id_Prof"]);
$stmt = $conn->prepare("
    SELECT DISTINCT m.Id_Materia, m.Nome_Materia
    FROM professor_materia_turma pmt
    INNER JOIN materias m ON m.Id_Materia = pmt.Id_Materia
    WHERE pmt.Id_Prof = ?
    ORDER BY m.Nome_Materia ASC
");
$stmt->execute([$idProf]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'ok')   $mensagem = "<p class='mensagem sucesso'>Questão inserida com sucesso!</p>";
    if ($_GET['msg'] === 'erro') $mensagem = "<p class='mensagem erro'>Ocorreu um erro ao salvar a questão.</p>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Inserir Questão - SistemaX</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="img/logoxtransparente.png">
<style>
:root{--azul:#032b73;--azul2:#0a3fa5;--bg:#f4f6fc;--card:#ffffff;--muted:#666;--ok:#028a0f;--bad:#b30000;}
*{box-sizing:border-box}
body{font-family:'Segoe UI',Tahoma,sans-serif;background:var(--bg);margin:0;min-height:100vh;display:flex;flex-direction:column;}
.topo{background:var(--azul);color:#fff;padding:16px 10px;text-align:center;font-weight:700;letter-spacing:.6px;}
.container{flex:1;display:flex;justify-content:center;align-items:center;padding:20px;}
.box{display:flex;gap:28px;background:var(--card);width:100%;max-width:1200px;border-radius:18px;padding:24px;box-shadow:0 8px 26px rgba(0,0,0,.08);}
#preview{width:40%;min-height:560px;background:#fff;border-radius:12px;padding:16px 18px;box-shadow:0 8px 16px rgba(0,0,0,.05);overflow:auto;border:1px solid #eef1f9;}
#preview h3{margin:6px 0 10px;color:var(--azul);text-align:center}
#preview-content{color:#111;font-size:16px;line-height:1.55}
.formulario{width:60%}
.formulario h2{margin:0 0 8px;color:var(--azul)}
hr{border:none;border-top:1px solid #e7eaf3;margin:8px 0 16px}
label{font-weight:600;color:#333;font-size:14px}
.linha{display:grid;grid-template-columns:1fr 1fr;gap:12px}
select,input[type="text"],input[type="number"],textarea{width:100%;padding:10px 12px;border:1px solid #cfd7ee;border-radius:10px;background:#f3f6ff;font-size:15px;outline:none;}
textarea{min-height:110px;resize:vertical}
.muted{color:var(--muted);font-size:13px}
.mensagem{margin-top:10px;border-radius:8px;padding:10px;text-align:center}
.mensagem.sucesso{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
.mensagem.erro{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
button[type=submit]{width:100%;padding:12px;border:none;border-radius:10px;background:var(--azul);color:#fff;font-weight:700;cursor:pointer}
button[type=submit]:hover{background:var(--azul2)}
.btn-min{padding:8px 10px;border:none;border-radius:8px;background:#e6edff;font-weight:600;cursor:pointer}
.btn-add{background:#0aa03f;color:#fff}
.btn-del{background:#d64b4b;color:#fff}
.alt-item,.vf-item,.assoc-row{display:grid;grid-template-columns:auto 1fr auto;gap:8px;align-items:center;margin:6px 0}
.badge{display:inline-block;min-width:26px;text-align:center;font-weight:800;background:#eef2ff;color:#2f49af;border:1px solid #dfe6ff;border-radius:6px;padding:2px 6px}
ol{margin:6px 0 0 20px}
.ok{color:var(--ok);font-weight:700}
.small{font-size:12px}
#boxQtdLacuna{display:none}
footer{background:var(--azul);color:#fff;text-align:center;padding:12px 0;font-size:14px}
@media (max-width:980px){.box{flex-direction:column}#preview{width:100%;order:2}.formulario{width:100%}}

/* Preview decoração */
.lac-blank{display:inline-block;min-width:80px;border-bottom:2px solid #333;height:1.1em;vertical-align:baseline;position:relative;margin:0 3px}
.lac-blank .sup{position:absolute;top:-0.95em;left:0;font-size:.72em;color:#333}
.preview-title{font-weight:700;margin:6px 0}
.preview-opt{line-height:1.9}
.preview-divider{border:none;border-top:1px solid #ddd;margin:10px 0}
.preview-right{color:#0a8a1f;font-weight:700}
.level-pill{display:inline-block;padding:2px 8px;border-radius:12px;margin-left:6px;font-size:12px}
.level-easy{background:#e6f7ec;color:#177245;border:1px solid #c8eccf}
.level-mid{background:#fff7e6;color:#9a6b00;border:1px solid #ffe1a8}
.level-hard{background:#fdecec;color:#a31212;border:1px solid #f7b5b5}

/* ===== BLOCO ASSOCIAÇÃO ===== */
#lista-assoc {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

#lista-assoc .alt-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f9fbff;
  border: 1px solid #dbe4ff;
  border-radius: 10px;
  padding: 6px 10px;
}

#lista-assoc input[type="text"] {
  flex: 1;
  padding: 8px 10px;
  border: 1px solid #cfd7ee;
  border-radius: 8px;
  background: #f3f6ff;
  font-size: 14px;
}

#lista-assoc span {
  font-weight: bold;
  color: #2f49af;
  font-size: 18px;
}

#lista-assoc .btn-del {
  background: #ff4b4b;
  border: none;
  border-radius: 6px;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  padding: 6px 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

#lista-assoc .btn-del:hover {
  background: #cc0000;
}

/* Ícone de lixeira */
#lista-assoc .btn-del::before {
  content: "🗑️";
  font-size: 16px;
  margin-right: 0;
}

</style>
</head>
<body>
<div class="topo">INSERIR QUESTÃO</div>
<div class="container">
<div class="box">

<!-- PREVIEW -->
<div id="preview">
  <h3>Prévia da Questão</h3>
  <div id="preview-content">Digite a questão ao lado para visualizar aqui 👈</div>
</div>

<!-- FORM -->
<div class="formulario">
<h2>Nova questão <span id="nivel-badge" class="level-pill level-mid">Médio</span></h2>
<?= $mensagem ?>

<hr>

<form method="POST" action="salvar_questao.php" id="form-questao" autocomplete="off">
  <!-- cabeçalho -->
  <div class="linha">
    <div>
      <label>Matéria:</label>
      <select name="Id_Materia" required>
        <option value="">Selecione</option>
        <?php foreach ($materias as $m): ?>
          <option value="<?= intval($m['Id_Materia']) ?>"
            <?= isset($questao['Id_Materia']) && $questao['Id_Materia'] == $m['Id_Materia'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($m['Nome_Materia']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Nível:</label>
      <select name="Nivel_Dificuldade" id="nivel" required>
        <option value="Fácil"   <?= ($questao['Nivel_Dificuldade'] ?? '') === 'Fácil'   ? 'selected' : '' ?>>Fácil</option>
        <option value="Médio"   <?= ($questao['Nivel_Dificuldade'] ?? '') === 'Médio'   ? 'selected' : '' ?>>Médio</option>
        <option value="Difícil" <?= ($questao['Nivel_Dificuldade'] ?? '') === 'Difícil' ? 'selected' : '' ?>>Difícil</option>
      </select>
    </div>
  </div>

  <div class="linha">
    <div>
      <label>Tipo:</label>
      <select name="Tipo_Questao" id="tipo" required>
        <option value="ME"          <?= ($questao['Tipo_Questao'] ?? '') === 'ME'          ? 'selected' : '' ?>>Múltipla Escolha</option>
        <option value="VF"          <?= ($questao['Tipo_Questao'] ?? '') === 'VF'          ? 'selected' : '' ?>>Verdadeiro / Falso</option>
        <option value="LACUNA"      <?= ($questao['Tipo_Questao'] ?? '') === 'LACUNA'      ? 'selected' : '' ?>>Lacunas (ordem)</option>
        <option value="ASSOCIACAO"  <?= ($questao['Tipo_Questao'] ?? '') === 'ASSOCIACAO'  ? 'selected' : '' ?>>Associação (ordem)</option>
      </select>
    </div>
    <div id="boxQtdLacuna">
      <label>Qtd. Lacunas:</label>
      <input type="number" min="0" max="50" value="<?= intval($questao['Qtd_Lacunas'] ?? 0) ?>" id="qtd_lacunas" name="Qtd_Lacunas" readonly>
    </div>
  </div>

  <label>Enunciado:</label>
  <textarea name="Enunciado" id="enunciado" required placeholder="Digite o enunciado..."><?= htmlspecialchars($questao['Enunciado'] ?? '') ?></textarea>

  <!-- ME -->
  <div id="bloco-me">
    <label>Alternativas (A–E):</label>
    <div id="lista-alternativas"></div>
    <p class="small muted">Marque uma como correta.</p>
  </div>

  <!-- VF -->
  <div id="bloco-vf" style="display:none">
    <div class="linha">
      <div><label>Assertivas (marque as verdadeiras):</label></div>
      <div style="text-align:right"><button type="button" class="btn-min btn-add" id="btnAddVF">+ Adicionar assertiva</button></div>
    </div>
    <div id="lista-vf"></div>
  </div>

  <!-- LACUNA -->
  <div id="bloco-lacuna" style="display:none">
    <div class="linha" style="align-items:center">
      <div><span class="badge">[[n]]</span> <span class="muted">Use [[1]], [[2]], ... no enunciado. No preview vira ____¹, ____²...</span></div>
      <div style="text-align:right"><button type="button" class="btn-min" id="btnSyncLac">↻ Atualizar</button></div>
    </div>

    <label>Respostas das lacunas (por número):</label>
    <div id="lista-lacunas"></div>

    <hr>
    <label>Alternativas (ordem) — “Qual a ordem correta?”</label>
    <div id="lac-ordens"></div>
    <p class="small muted">Separe por vírgula (ex.: <b>1,4,3,2</b>). Marque a correta.</p>

    <!-- hidden JSONs p/ salvar no banco -->
    <input type="hidden" name="lac_json_A" id="lac_json_A">
    <input type="hidden" name="lac_json_B" id="lac_json_B">
    <input type="hidden" name="lac_json_C" id="lac_json_C">
    <input type="hidden" name="lac_json_D" id="lac_json_D">
    <input type="hidden" name="lac_json_E" id="lac_json_E">
  </div>

  <!-- ASSOC -->
  <div id="bloco-assoc" style="display:none">
    <div class="linha" style="align-items:center">
      <div><span class="badge">A↔B</span> <span class="muted">Informe pares A–B (ex.: termo ↔ definição). O preview mostra a lista numerada e as ordens A–E geradas automaticamente.</span></div>
      <div style="text-align:right"><button type="button" class="btn-min btn-add" id="btnAddAssoc">+ Adicionar par</button></div>
    </div>
    <div id="lista-assoc"></div>
  </div>
<script>
const questaoData = <?= json_encode($questao, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) ?>;
const alternativasData = <?= json_encode($alternativas, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) ?>;
</script>

<script>
/* ==================================================
   SISTEMAX - INSERIR QUESTÃO (versão estável final)
   ================================================== */

// ======== ELEMENTOS PRINCIPAIS ========
const tipo = document.getElementById('tipo');
const enun = document.getElementById('enunciado');
const qtdLac = document.getElementById('qtd_lacunas');
const preview = document.getElementById('preview-content');
const nivel = document.getElementById('nivel');
const nivelBadge = document.getElementById('nivel-badge');
let nivelTouched = false;

// ======== BLOCO VISUAL ========
const blocos = {
  ME: document.getElementById('bloco-me'),
  VF: document.getElementById('bloco-vf'),
  LACUNA: document.getElementById('bloco-lacuna'),
  ASSOCIACAO: document.getElementById('bloco-assoc')
};

function trocarTipo() {
  for (let b in blocos) blocos[b].style.display = 'none';
  blocos[tipo.value].style.display = 'block';
  document.getElementById('boxQtdLacuna').style.display = (tipo.value === 'LACUNA') ? 'block' : 'none';
  atualizarPreview();
}
tipo.addEventListener('change', trocarTipo);

// ======== DEBOUNCE PARA PREVIEW ========
const debounce = (fn, delay = 250) => {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), delay);
  };
};
const requestPreview = debounce(() => atualizarPreview(), 250);

// ======== UTILIDADES ========
function shuffle(arr) {
  const a = arr.slice();
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}
function arraysEqual(a, b) { return a.length === b.length && a.every((v, i) => v === b[i]); }

// ======== ENUNCIADO E GATILHOS ========
function enunciadoComLacunasFormatado() {
  const supMap = ['','¹','²','³','⁴','⁵','⁶','⁷','⁸','⁹','¹⁰','¹¹','¹²','¹³','¹⁴','¹⁵'];
  let txt = (enun.value || '').replace(/\[\[(\d+)\]\]/g, (m, n) => {
    const idx = parseInt(n, 10);
    const sup = (idx < supMap.length ? supMap[idx] : `<sup>${idx}</sup>`);
    return `<span class="lac-blank"><span class="sup">${sup}</span></span>`;
  });
  txt = destacarGatilhos(txt);
  return txt.replace(/\n/g, "<br>");
}

function destacarGatilhos(texto) {
  const gatilhos = {
    Difícil: /(analise|avalie|interprete|justifique|discuta|correlacione|prove|demonstre|argumente)/gi,
    Médio: /(explique|compare|relacione|classifique|descreva|aplique|resolva|projete)/gi,
    Fácil: /(o que é|defina|conceito de|enumere|indique|liste|cite)/gi
  };
  let result = texto;
  for (const nivel in gatilhos) {
    const cor = nivel === 'Difícil' ? '#b30000' : nivel === 'Médio' ? '#a66b00' : '#177245';
    result = result.replace(gatilhos[nivel], m => `<b style="color:${cor}">${m}</b>`);
  }
  return result;
}

function autoNivelByEnunciado() {
  if (nivelTouched) return;
  const t = (enun.value || '').toLowerCase();
  const hard = /(analise|avalie|interprete|justifique|discuta|correlacione|prove|demonstre|argumente)/i;
  const mid = /(explique|compare|relacione|classifique|descreva|aplique|resolva|projete)/i;
  const easy = /(o que é|defina|conceito de|enumere|indique|liste|cite)/i;
  let novo = 'Médio';
  if (hard.test(t)) novo = 'Difícil';
  else if (mid.test(t)) novo = 'Médio';
  else if (easy.test(t)) novo = 'Fácil';
  if (nivel.value !== novo) {
    nivel.value = novo;
    pintarBadgeNivel(novo);
  }
}

function pintarBadgeNivel(n) {
  nivelBadge.textContent = n;
  nivelBadge.className = 'level-pill ' + (n === 'Fácil' ? 'level-easy' : (n === 'Médio' ? 'level-mid' : 'level-hard'));
}
nivel.addEventListener('change', () => { nivelTouched = true; pintarBadgeNivel(nivel.value); });

// ======== ME ========
const listaAlt = document.getElementById('lista-alternativas');
function gerarAltME() {
  listaAlt.innerHTML = '';
  for (let i = 0; i < 5; i++) {
    const div = document.createElement('div');
    div.className = 'alt-item';
    div.innerHTML = `
      <span class='badge'>${String.fromCharCode(65 + i)}</span>
      <input type='text' name='alternativas[]' placeholder='Alternativa ${String.fromCharCode(65 + i)}'>
      <label><input type='radio' name='me_correta' value='${i}' ${i === 0 ? 'checked' : ''}> Correta</label>
    `;
    listaAlt.appendChild(div);
  }
}
gerarAltME();

function previewME() {
  const alternativas = document.querySelectorAll("#lista-alternativas input[type='text']");
  const radios = document.querySelectorAll("input[name='me_correta']");
  let html = `<div class="preview-title">Enunciado:</div>${enunciadoComLacunasFormatado()}<hr class="preview-divider"><div class="preview-opt">`;
  alternativas.forEach((alt, i) => {
    const letra = String.fromCharCode(65 + i);
    const texto = (alt.value || '').trim() || "(vazio)";
    const correta = radios[i].checked;
    html += `<div><b>${letra})</b> ${texto}${correta ? ` <span class="preview-right">✔ Correta</span>` : ''}</div>`;
  });
  html += `</div>`;
  return html;
}

// ======== VF (com correção de travamento) ========
const listaVF = document.getElementById('lista-vf');
document.getElementById('btnAddVF')?.addEventListener('click', () => {
  const div = document.createElement('div');
  div.className = 'alt-item';
  div.innerHTML = `
    <input type='text' name='vf_itens[]' placeholder='Texto da assertiva'>
    <label><input type='checkbox' name='vf_true[]'> Verdadeira</label>
    <button type="button" class="btn-min btn-del">Remover</button>
  `;
  listaVF.appendChild(div);
  div.querySelector('.btn-del').addEventListener('click', e => {
    e.preventDefault();
    div.remove();
    requestPreview();
  });
  requestPreview();
});

function gerarVFAlternativas() {
  const itens = document.querySelectorAll("#lista-vf .alt-item");
  const truth = [];
  itens.forEach(div => {
    const chk = div.querySelector("input[type='checkbox']").checked;
    truth.push(chk ? 'V' : 'F');
  });
  if (truth.length < 2) return { opcoes: [], idxCorreta: -1 };

  const correta = truth.slice();
  const opcoes = [correta.slice()];
  const geradas = new Set([correta.join('-')]);
  let tentativas = 0;

  while (opcoes.length < 5 && tentativas < 15) {
    const cand = correta.slice();
    const flips = Math.max(1, Math.floor(Math.random() * cand.length));
    for (let i = 0; i < flips; i++) {
      const pos = Math.floor(Math.random() * cand.length);
      cand[pos] = (cand[pos] === 'V' ? 'F' : 'V');
    }
    const key = cand.join('-');
    if (!geradas.has(key)) {
      geradas.add(key);
      opcoes.push(cand.slice());
    }
    tentativas++;
  }

  const final = shuffle(opcoes);
  const idxCorreta = final.findIndex(s => arraysEqual(s, correta));
  return { opcoes: final, idxCorreta };
}

function previewVF() {
  const itens = document.querySelectorAll("#lista-vf .alt-item");
  if (itens.length === 0) return `<i>Adicione assertivas e marque as verdadeiras.</i>`;
  let html = `<div class="preview-title">Enunciado:</div>${enunciadoComLacunasFormatado()}<hr>`;
  itens.forEach((div, i) => {
    const txt = (div.querySelector("input[type='text']").value || '').trim() || "(vazio)";
    html += `<div><b>(${i + 1})</b> ${txt}</div>`;
  });
  html += `<hr><div class="preview-title">Marque a sequência correta (V/F):</div>`;
  const { opcoes, idxCorreta } = gerarVFAlternativas();
  opcoes.forEach((seq, i) => {
    const s = seq.join('–');
    html += `<div><b>${String.fromCharCode(65 + i)})</b> ${s}${i === idxCorreta ? ` <span class="preview-right">✔ Correta</span>` : ''}</div>`;
  });
  return html;
}

// ======== LACUNAS ========
const listaLac = document.getElementById('lista-lacunas');
const btnSyncLac = document.getElementById('btnSyncLac');
btnSyncLac?.addEventListener('click', syncLacunas);

function syncLacunas() {
  const txt = enun.value || '';
  const matches = [...txt.matchAll(/\[\[(\d+)\]\]/g)].map(m => parseInt(m[1]));
  const nums = [...new Set(matches)].sort((a, b) => a - b);
  listaLac.innerHTML = '';
  nums.forEach(n => {
    const div = document.createElement('div');
    div.className = 'alt-item';
    div.innerHTML = `<span class='badge'>[[${n}]]</span><input type='text' name='lacunas[]' placeholder='Resposta ${n}'>`;
    listaLac.appendChild(div);
  });
  qtdLac.value = nums.length;
  requestPreview();
}

function previewLacuna() {
  let html = `<div class="preview-title">Enunciado:</div>${enunciadoComLacunasFormatado()}<hr>`;
  const lac = document.querySelectorAll("#lista-lacunas input[name='lacunas[]']");
  if (lac.length > 0) {
    html += `<div class="preview-title">Respostas das lacunas:</div>`;
    lac.forEach((inp, i) => {
      const val = (inp.value || '').trim() || "(sem resposta)";
      html += `<div><b>[${i + 1}]</b> → ${val}</div>`;
    });
  }
  return html;
}

// ======== ASSOCIAÇÃO (com tabela A↔B) ========
const listaAssoc = document.getElementById('lista-assoc');
document.getElementById('btnAddAssoc')?.addEventListener('click', () => {
  const div = document.createElement('div');
  div.className = 'alt-item';
  div.innerHTML = `
    <input type='text' name='assocA[]' placeholder='Item A (ex.: termo)'>
    <span>↔</span>
    <input type='text' name='assocB[]' placeholder='Item B (ex.: definição)'>
    <button type="button" class="btn-min btn-del">Remover</button>
  `;
  listaAssoc.appendChild(div);
  div.querySelector('.btn-del').addEventListener('click', e => {
    e.preventDefault();
    div.remove();
    requestPreview();
  });
  requestPreview();
});


// =============== ASSOC: cache simples para não mudar a cada keypress ===============
let assocCache = { key: '', data: null };
function getAssocState(As, Bs) {
  const key = JSON.stringify({A:As, B:Bs});
  if (assocCache.key !== key) {
    assocCache.key = key;
    assocCache.data = gerarAssocSequencias(As, Bs);
  }
  return assocCache.data;
}

// =============== NOVA GERAÇÃO: usa permutações reais das colunas A e B ===============
function gerarAssocSequencias(As, Bs) {
  const n = Math.min(As.length, Bs.length);
  if (n < 2) return { permA:[0], permB:[0], pacote:[{rot:'A',seq:['1→1']}], corretaRot:'A' };

  // índices 0..n-1
  const idx = Array.from({length:n}, (_,i)=>i);

  // embaralha A e B independentemente (ordem exibida na tabela)
  const permA = shuffle(idx);
  const permB = shuffle(idx);

  // jpos[i] = posição (0-based) da B correspondente ao A mostrado na linha i
  // (mesmo par original, agora nas linhas embaralhadas)
  const jpos = permA.map(origIdxA => permB.indexOf(origIdxA));

  // sequência correta no formato "1→3, 2→1, ..."
  const corretaPairs = jpos.map((j,i)=>`${i+1}→${j+1}`);

  // gera 4 variantes (distratores) mexendo em jpos mas sem repetir a correta
  const seen = new Set([corretaPairs.join('|')]);
  const variants = [corretaPairs];

  function perturbar(v){
    const c = v.slice();
    if (c.length > 1) {
      const a = Math.floor(Math.random()*c.length);
      let b = Math.floor(Math.random()*c.length);
      if (a === b) b = (b+1) % c.length;
      [c[a], c[b]] = [c[b], c[a]];
    }
    return c;
  }

  while (variants.length < 5) {
    const candJ = perturbar(jpos);
    const candPairs = candJ.map((j,i)=>`${i+1}→${j+1}`);
    const key = candPairs.join('|');
    if (!seen.has(key)) { seen.add(key); variants.push(candPairs); }
  }

  // monta pacote A–E embaralhado
  const letras = ['A','B','C','D','E'];
  const pacote = shuffle(letras.map((L,i)=>({rot:L, seq: variants[i]})));
  const corretaRot = pacote.find(p => p.seq.join('|') === corretaPairs.join('|')).rot;

  return { permA, permB, pacote, corretaRot };
}

function previewAssociacao(){
  const pares = document.querySelectorAll("#lista-assoc .alt-item");
  let html = `<div class="preview-title">Enunciado:</div>${enunciadoComLacunasFormatado()}<hr class="preview-divider">`;

  if (pares.length===0){
    html += `<i>Adicione pares A↔B para gerar as ordens.</i>`;
    return html;
  }

  const As = [...document.getElementsByName('assocA[]')].map(i=>i.value.trim());
  const Bs = [...document.getElementsByName('assocB[]')].map(i=>i.value.trim());
  const n = Math.min(As.length, Bs.length);

  if (n < 2 || As.includes('') || Bs.includes('')){
    html += `<p class="muted">Complete ao menos 2 pares A–B.</p>`;
    return html;
  }

  // gera/recupera estado
  const st = getAssocState(As, Bs);

  // tabela com A e B embaralhados independentemente
  html += `<table class="table-like">
    <thead><tr><th>Coluna A</th><th>Coluna B</th></tr></thead><tbody>`;
  for (let i=0;i<n;i++){
    html += `<tr>
      <td>(${i+1}) ${As[st.permA[i]]}</td>
      <td>(${i+1}) ${Bs[st.permB[i]]}</td>
    </tr>`;
  }
  html += `</tbody></table>`;

  // alternativas no formato "1→3, 2→1, ..."
  html += `<div class="preview-title">A ordem ficaria</div>`;
  st.pacote.forEach((opt,i)=>{
    html += `<div><b>${opt.rot})</b> ${opt.seq.join(', ')}${opt.rot===st.corretaRot?` <span class="preview-right">✔ Correta</span>`:''}</div>`;
  });

  return html;
}


// ======== ATUALIZAR PREVIEW ========
function atualizarPreview() {
  autoNivelByEnunciado();
  if (tipo.value === 'ME') preview.innerHTML = previewME();
  else if (tipo.value === 'VF') preview.innerHTML = previewVF();
  else if (tipo.value === 'LACUNA') preview.innerHTML = previewLacuna();
  else if (tipo.value === 'ASSOCIACAO') preview.innerHTML = previewAssociacao();
}

// ======== EVENTOS GERAIS ========
document.addEventListener('input', e => {
  if (e.target.matches('textarea, input, select')) {
    if (e.target === enun) syncLacunas();
    requestPreview();
  }
});
document.addEventListener('click', e => {
  if (e.target.matches("input[type='radio'],input[type='checkbox']")) requestPreview();
});

// ======== INICIALIZAÇÃO ========
function init() {
  trocarTipo();
  pintarBadgeNivel(nivel.value);

  // Se temos dados do banco, reconstruir
  if (questaoData && alternativasData && alternativasData.length > 0) {
    const tipoQuest = questaoData.Tipo_Questao;

    if (tipoQuest === 'ME') {
      listaAlt.innerHTML = '';
      const alts = alternativasData.filter(a => a.Tipo === 'ME');
      alts.forEach((alt, i) => {
        const div = document.createElement('div');
        div.className = 'alt-item';
        div.innerHTML = `
          <span class='badge'>${alt.Grupo}</span>
          <input type='text' name='alternativas[]' value="${alt.Texto}" placeholder='Alternativa ${alt.Grupo}'>
          <label><input type='radio' name='me_correta' value='${i}' ${alt.Correta == 1 ? 'checked' : ''}> Correta</label>
        `;
        listaAlt.appendChild(div);
      });
    }

    if (tipoQuest === 'VF') {
      listaVF.innerHTML = '';
      const vfs = alternativasData.filter(a => a.Tipo === 'VF');
      vfs.forEach(alt => {
        const div = document.createElement('div');
        div.className = 'alt-item';
        div.innerHTML = `
          <input type='text' name='vf_itens[]' value="${alt.Texto}" placeholder='Texto da assertiva'>
          <label><input type='checkbox' name='vf_true[]' ${alt.Correta == 1 ? 'checked' : ''}> Verdadeira</label>
          <button type="button" class="btn-min btn-del">Remover</button>
        `;
        listaVF.appendChild(div);
      });
    }

    if (tipoQuest === 'LACUNA') {
      listaLac.innerHTML = '';
      const lac = alternativasData.filter(a => a.Tipo === 'LACUNA' && a.Grupo !== 'VARIANTES');
      lac.forEach((alt, i) => {
        const div = document.createElement('div');
        div.className = 'alt-item';
        div.innerHTML = `<span class='badge'>[[${i + 1}]]</span><input type='text' name='lacunas[]' value="${alt.Texto}">`;
        listaLac.appendChild(div);
      });
      qtdLac.value = lac.length;
    }

    if (tipoQuest === 'ASSOCIACAO') {
      listaAssoc.innerHTML = '';
      const assoc = alternativasData.filter(a => a.Tipo === 'ASSOCIACAO');
      assoc.forEach(alt => {
        try {
          const data = JSON.parse(alt.Extra);
          const div = document.createElement('div');
          div.className = 'alt-item';
          div.innerHTML = `
            <input type='text' name='assocA[]' value="${data.A}" placeholder='Item A'>
            <span>↔</span>
            <input type='text' name='assocB[]' value="${data.B}" placeholder='Item B'>
            <button type="button" class="btn-min btn-del">Remover</button>
          `;
          listaAssoc.appendChild(div);
        } catch(e){}
      });
    }
  } else {
    // nova questão — apenas gerar padrão
    gerarAltME();
  }

  syncLacunas();
  atualizarPreview();
}

init();

</script>
<?php if ($idQuest > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const isEditMode = <?= $editMode ? 'true' : 'false' ?>;
  const form = document.querySelector('form');

  if (!isEditMode) {
    // 👁️ MODO VISUALIZAR — tudo bloqueado
    const inputs = form.querySelectorAll('input, textarea, select, button:not(.btn-edit)');
    inputs.forEach(el => el.disabled = true);

    // botão voltar
    const btnVoltar = document.createElement('button');
    btnVoltar.type = "button";
    btnVoltar.textContent = "⬅️ Voltar";
    btnVoltar.classList.add("btn-edit");
    btnVoltar.style = "margin-top:10px;background:#555;color:#fff;padding:10px 16px;border:none;border-radius:6px;cursor:pointer;";
    btnVoltar.onclick = ()=>{
    window.location.href = "home.php"; 
};

    form.appendChild(btnVoltar);

    // 🔁 Regera blocos e chama o preview completo
    setTimeout(()=>{
      trocarTipo();        // ativa o bloco correto (ME, VF, etc.)
      syncLacunas();       // se for LACUNA
      atualizarPreview();  // gera a prévia visual
    }, 200);
  } 
  else {
    // ✏️ MODO EDITAR — habilita edição e botão de atualizar
    const btnUpdate = document.createElement('button');
    btnUpdate.type = "submit";
    btnUpdate.textContent = "💾 Atualizar Questão";
    btnUpdate.classList.add("btn-edit");
    btnUpdate.style = "margin-top:10px;background:#0157ff;color:#fff;padding:10px 16px;border:none;border-radius:6px;cursor:pointer;";
    form.appendChild(btnUpdate);

    setTimeout(()=>{
      trocarTipo();
      syncLacunas();
      atualizarPreview();
    }, 200);
  }
});
</script>
<?php else: ?>
<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const form = document.querySelector('form');
  // 🆕 NOVA QUESTÃO
  const btnSave = document.createElement('button');
  btnSave.type = "submit";
  btnSave.textContent = "💾 Salvar Questão";
  btnSave.classList.add("btn-save");
  btnSave.style = "margin-top:10px;background:#032b73;color:#fff;padding:10px 16px;border:none;border-radius:6px;cursor:pointer;";
  form.appendChild(btnSave);

  setTimeout(()=>{
    trocarTipo();
    syncLacunas();
    atualizarPreview();
  }, 200);
});
</script>
<?php endif; ?>

<!-- botão flutuante padrão -->
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
  `;
  btnVoltar.onclick = ()=> {
    if (document.referrer) history.back();
    else window.location.href = "home.php";
  };
  document.body.appendChild(btnVoltar);
});
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
  btnVoltar.onclick = ()=> {
    // volta 1 página no histórico ou vai pro dashboard padrão
    if (document.referrer) history.back();
    else window.location.href = "home.php";
  };

  document.body.appendChild(btnVoltar);
});
</script>



</body>
</html>
