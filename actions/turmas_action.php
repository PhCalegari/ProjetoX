<?php
session_start();
require_once "../conexao.php";
header("Content-Type: application/json; charset=utf-8");

$op = $_POST['op'] ?? '';

function ok($data = [], $msg = '') {
  echo json_encode(['ok' => true] + $data + ['msg' => $msg]);
  exit;
}
function err($msg) {
  echo json_encode(['ok' => false, 'msg' => $msg]);
  exit;
}

try {

  /* ============== LISTAR PROFESSORES (aprovados) ============== */
  if ($op === 'listProf') {
    $st = $conn->query("SELECT Id_Prof, Nome FROM professor WHERE Aprovado=1 ORDER BY Nome");
    ok(['rows' => $st->fetchAll(PDO::FETCH_ASSOC)]);
  }

  /* ============== LISTAR TURMAS ============== */
  if ($op === 'list') {
    $id_curso   = intval($_POST['id_curso'] ?? 0);
    $id_periodo = intval($_POST['id_periodo'] ?? 0);

    $sql = "SELECT
              t.Id_Turma, t.Nome_Turma, t.Turno,
              t.Id_Curso, t.Id_Periodo, t.Id_Materia, t.Id_Prof,
              p.NumeroPeriodo,
              c.Nome_Curso, c.Sigla,
              m.Nome_Materia,
              pr.Nome AS Nome_Prof
            FROM turma t
            JOIN curso c   ON c.Id_Curso = t.Id_Curso
            JOIN periodo p ON p.Id_Periodo = t.Id_Periodo
            LEFT JOIN materias m ON m.Id_Materia = t.Id_Materia
            LEFT JOIN professor pr ON pr.Id_Prof = t.Id_Prof
            WHERE 1=1";

    $params = [];
    if ($id_curso)   { $sql .= " AND t.Id_Curso = :c";   $params['c'] = $id_curso; }
    if ($id_periodo) { $sql .= " AND t.Id_Periodo = :p"; $params['p'] = $id_periodo; }

    $sql .= " ORDER BY p.NumeroPeriodo, t.Nome_Turma";

    $st = $conn->prepare($sql);
    $st->execute($params);
    ok(['rows' => $st->fetchAll(PDO::FETCH_ASSOC)]);
  }

  /* ============== OBTÉM UMA TURMA (para edição) ============== */
  if ($op === 'get') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) err('ID inválido');

    $st = $conn->prepare("SELECT
                            t.Id_Turma, t.Nome_Turma, t.Turno,
                            t.Id_Curso, t.Id_Periodo, t.Id_Materia, t.Id_Prof
                          FROM turma t
                          WHERE t.Id_Turma = :id");
    $st->execute(['id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      ok(['row' => $row]);
    } else {
      err('Turma não encontrada');
    }
  }

  /* ============== MATÉRIAS POR CURSO + PERÍODO ============== */
  if ($op === 'materiasByCursoPeriodo') {
    $id_curso   = intval($_POST['id_curso'] ?? 0);
    $id_periodo = intval($_POST['id_periodo'] ?? 0);
    if (!$id_curso || !$id_periodo) ok(['rows' => []]);

    $st = $conn->prepare("SELECT Id_Materia, Nome_Materia
                          FROM materias
                          WHERE Id_Curso = :c AND Id_Periodo = :p
                          ORDER BY Nome_Materia");
    $st->execute(['c' => $id_curso, 'p' => $id_periodo]);
    ok(['rows' => $st->fetchAll(PDO::FETCH_ASSOC)]);
  }

  /* ============== CRIAR TURMA AUTOMÁTICA ============== */
  if ($op === 'createAuto') {
    $id_curso   = intval($_POST['id_curso'] ?? 0);
    $id_periodo = intval($_POST['id_periodo'] ?? 0);
    $id_materia = intval($_POST['id_materia'] ?? 0);
    $id_prof    = intval($_POST['id_prof'] ?? 0);
    $turno      = trim($_POST['turno'] ?? '');

    if (!$id_curso || !$id_periodo || !$id_materia || $turno === '') {
      err('Dados incompletos');
    }

    $pp = $conn->prepare("SELECT NumeroPeriodo FROM periodo WHERE Id_Periodo=:p AND Id_Curso=:c");
    $pp->execute(['p'=>$id_periodo,'c'=>$id_curso]);
    $rowP = $pp->fetch(PDO::FETCH_ASSOC);
    if (!$rowP) err('Período inválido para o curso');

    $numeroPeriodo = intval($rowP['NumeroPeriodo']);
    $cc = $conn->prepare("SELECT COUNT(*) FROM turma WHERE Id_Curso=:c AND Id_Periodo=:p AND Turno=:t");
    $cc->execute(['c'=>$id_curso,'p'=>$id_periodo,'t'=>$turno]);
    $seq = intval($cc->fetchColumn()) + 1;
    $seqStr = str_pad($seq, 2, '0', STR_PAD_LEFT);
    $nomeTurma = $numeroPeriodo . "º Período - Turma " . $seqStr;

    $ins = $conn->prepare("INSERT INTO turma (Nome_Turma, Id_Curso, Id_Periodo, Id_Materia, Id_Prof, Turno)
                           VALUES (:n,:c,:p,:m,:pr,:t)");
    $ins->execute([
      'n'=>$nomeTurma,
      'c'=>$id_curso,
      'p'=>$id_periodo,
      'm'=>$id_materia,
      'pr'=>($id_prof ?: null),
      't'=>$turno
    ]);
    $idTurma = $conn->lastInsertId();

    if ($id_prof) {
      $vk = $conn->prepare("INSERT INTO professor_materia_turma (Id_Prof, Id_Materia, Id_Turma)
                            VALUES (:pr,:m,:t)");
      $vk->execute(['pr'=>$id_prof,'m'=>$id_materia,'t'=>$idTurma]);
    }

    ok([], 'Turma criada: '.$nomeTurma);
  }

  /* ============== ATUALIZAR (nome/turno/prof) ============== */
  if ($op === 'update') {
    $id_turma = intval($_POST['id'] ?? 0);
    $nome     = trim($_POST['nome'] ?? '');
    $id_prof  = intval($_POST['id_prof'] ?? 0);
    $turno    = trim($_POST['turno'] ?? '');

    if(!$id_turma) err('ID inválido');
    if($nome === '') err('Informe o nome da turma');

    $up = $conn->prepare("UPDATE turma SET Nome_Turma=:n, Id_Prof=:pr, Turno=:t WHERE Id_Turma=:id");
    $up->execute([
      'n'=>$nome,
      'pr'=>($id_prof ?: null),
      't'=>$turno,
      'id'=>$id_turma
    ]);

    $conn->prepare("DELETE FROM professor_materia_turma WHERE Id_Turma=:t")->execute(['t'=>$id_turma]);
    if ($id_prof) {
      $vk = $conn->prepare("INSERT INTO professor_materia_turma (Id_Prof, Id_Materia, Id_Turma)
                            SELECT :pr, Id_Materia, Id_Turma FROM turma WHERE Id_Turma=:t");
      $vk->execute(['pr'=>$id_prof,'t'=>$id_turma]);
    }

    ok([], 'Turma atualizada!');
  }

  /* ============== EXCLUIR TURMA ============== */
  if ($op === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if(!$id) err('ID inválido');

    $conn->prepare("DELETE FROM professor_materia_turma WHERE Id_Turma=:id")->execute(['id'=>$id]);
    $st = $conn->prepare("DELETE FROM turma WHERE Id_Turma=:id");
    $st->execute(['id'=>$id]);
    ok([], 'Turma excluída!');
  }

  err('Operação inválida');

} catch (Exception $e) {
  err($e->getMessage());
}
