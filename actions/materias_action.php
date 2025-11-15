<?php
session_start();
include("../conexao.php");
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["is_admin"]) || intval($_SESSION["is_admin"]) !== 1) {
  echo json_encode(['ok'=>false,'msg'=>'forbidden']);
  exit;
}

$op = $_POST['op'] ?? 'list';

try {

  /* 🔍 Buscar por nome */
  if ($op === 'search') {
    $q = trim($_POST['q'] ?? '');
    $st = $conn->prepare("SELECT Id_Materia, Nome_Materia
                          FROM materias
                          WHERE :q='' OR Nome_Materia LIKE :lk
                          ORDER BY Nome_Materia LIMIT 50");
    $st->execute(['q'=>$q,'lk'=>"%$q%"]);
    echo json_encode(['ok'=>true,'rows'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
  }

  /* 📄 Obter uma matéria específica */
  if ($op === 'get') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
      echo json_encode(['ok'=>false,'msg'=>'ID inválido']);
      exit;
    }

    $st = $conn->prepare("SELECT 
                            m.Id_Materia, m.Nome_Materia,
                            m.Id_Curso, m.Id_Periodo,
                            c.Nome_Curso, p.NumeroPeriodo AS Nome_Periodo
                          FROM materias m
                          JOIN curso c ON c.Id_Curso = m.Id_Curso
                          JOIN periodo p ON p.Id_Periodo = m.Id_Periodo
                          WHERE m.Id_Materia = :id");
    $st->execute(['id'=>$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      echo json_encode(['ok'=>true,'row'=>$row]);
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Matéria não encontrada']);
    }
    exit;
  }

  /* ➕ Criar nova matéria */
  if ($op === 'create') {
    $nome = trim($_POST['nome'] ?? '');
    $idCurso = intval($_POST['curso'] ?? 0);
    $idPeriodo = intval($_POST['periodo'] ?? 0);

    if ($nome === '' || !$idCurso || !$idPeriodo) {
      echo json_encode(['ok'=>false,'msg'=>'Dados incompletos']);
      exit;
    }

    // Verifica se o período pertence ao curso
    $chk = $conn->prepare("SELECT COUNT(*) FROM periodo WHERE Id_Periodo=:p AND Id_Curso=:c");
    $chk->execute(['p'=>$idPeriodo,'c'=>$idCurso]);
    if ($chk->fetchColumn() == 0) {
      echo json_encode(['ok'=>false,'msg'=>'Período não pertence ao curso']);
      exit;
    }

    // Evita duplicação
    $du = $conn->prepare("SELECT COUNT(*) FROM materias 
                          WHERE LOWER(Nome_Materia)=LOWER(:n)
                          AND Id_Periodo=:p AND Id_Curso=:c");
    $du->execute(['n'=>$nome,'p'=>$idPeriodo,'c'=>$idCurso]);
    if ($du->fetchColumn()>0) {
      echo json_encode(['ok'=>false,'msg'=>'Já existe esta matéria neste período']);
      exit;
    }

    $ins = $conn->prepare("INSERT INTO materias (Nome_Materia, Id_Curso, Id_Periodo)
                           VALUES (:n,:c,:p)");
    $ins->execute(['n'=>$nome,'c'=>$idCurso,'p'=>$idPeriodo]);

    echo json_encode(['ok'=>true,'msg'=>'Matéria cadastrada com sucesso!']);
    exit;
  }

  /* 📋 Listar matérias */
  if ($op === 'list') {
    $idCurso   = intval($_POST['id_curso'] ?? 0);
    $idPeriodo = intval($_POST['id_periodo'] ?? 0);

    $sql = "SELECT 
              m.Id_Materia, m.Nome_Materia, 
              c.Nome_Curso, c.Sigla,
              p.NumeroPeriodo
            FROM materias m
            JOIN curso c   ON c.Id_Curso=m.Id_Curso
            JOIN periodo p ON p.Id_Periodo=m.Id_Periodo
            WHERE 1=1";
    $params = [];
    if ($idCurso)   { $sql.=" AND m.Id_Curso=:c";   $params['c']=$idCurso; }
    if ($idPeriodo) { $sql.=" AND m.Id_Periodo=:p"; $params['p']=$idPeriodo; }
    $sql.=" ORDER BY m.Nome_Materia";

    $st = $conn->prepare($sql);
    $st->execute($params);
    echo json_encode(['ok'=>true,'rows'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
  }

  /* ✏️ Atualizar matéria */
  if ($op === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $curso = intval($_POST['curso'] ?? 0);
    $periodo = intval($_POST['periodo'] ?? 0);

    if (!$id || !$curso || !$periodo || $nome === '') {
      echo json_encode(['ok'=>false,'msg'=>'Dados inválidos']);
      exit;
    }

    $st = $conn->prepare("UPDATE materias 
                          SET Nome_Materia=:n, Id_Curso=:c, Id_Periodo=:p
                          WHERE Id_Materia=:id");
    $st->execute(['n'=>$nome,'c'=>$curso,'p'=>$periodo,'id'=>$id]);
    echo json_encode(['ok'=>true,'msg'=>'Matéria atualizada com sucesso!']);
    exit;
  }

  /* ❌ Excluir matéria */
  if ($op === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['ok'=>false,'msg'=>'ID inválido']); exit; }

    // Impede exclusão se houver turmas vinculadas
    $chk = $conn->prepare("SELECT COUNT(*) FROM turma WHERE Id_Materia=:id");
    $chk->execute(['id'=>$id]);
    if ($chk->fetchColumn() > 0) {
      echo json_encode(['ok'=>false,'msg'=>'Não é possível excluir: há turmas vinculadas.']);
      exit;
    }

    $st = $conn->prepare("DELETE FROM materias WHERE Id_Materia=:id");
    $st->execute(['id'=>$id]);
    echo json_encode(['ok'=>true,'msg'=>'Matéria excluída com sucesso!']);
    exit;
  }

  echo json_encode(['ok'=>false,'msg'=>'Operação inválida']);
  exit;

} catch (Exception $e) {
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
  exit;
}
