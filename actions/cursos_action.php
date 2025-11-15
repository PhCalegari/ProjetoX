<?php
require_once "../conexao.php";
header("Content-Type: application/json; charset=utf-8");

$op = $_POST['op'] ?? '';

function resposta($ok, $msg = "", $rows = []) {
    echo json_encode(["ok" => $ok, "msg" => $msg, "rows" => $rows]);
    exit;
}

function gerarSigla($nome){
    $txt = strtoupper(trim($nome));
    $pal = preg_split('/\s+/', $txt);

    $sigla = "";
    foreach ($pal as $p) {
        if (strlen($p) > 2) $sigla .= $p[0];
    }

    if ($sigla === "") {
        $sigla = substr($txt, 0, 3);
    }
    return $sigla;
}

/* SEARCH */
if ($op === 'search') {
    $q = trim($_POST['q'] ?? '');
    $sql = "SELECT Id_Curso, Nome_Curso, Sigla,
            (SELECT COUNT(*) FROM periodo WHERE Id_Curso = curso.Id_Curso) AS Qtd_Periodos
            FROM curso";

    $params = [];
    if ($q !== '') {
        $sql .= " WHERE Nome_Curso LIKE :q";
        $params['q'] = "%$q%";
    }

    $sql .= " ORDER BY Nome_Curso";
    $st = $conn->prepare($sql);
    $st->execute($params);

    resposta(true, "", $st->fetchAll(PDO::FETCH_ASSOC));
}

/* LIST */
if ($op === 'list') {
    $st = $conn->query("SELECT Id_Curso, Nome_Curso, Sigla FROM curso ORDER BY Nome_Curso");
    resposta(true, "", $st->fetchAll(PDO::FETCH_ASSOC));
}

/* CREATE */
if ($op === 'create') {
    $nome = trim($_POST['nome'] ?? '');
    $qtd  = intval($_POST['qtd'] ?? 0);

    if (!$nome || $qtd < 1) resposta(false, "Dados inválidos");

    $sigla = gerarSigla($nome);

    $st = $conn->prepare("INSERT INTO curso (Nome_Curso, Sigla) VALUES (:n, :s)");
    $st->execute(['n'=>$nome, 's'=>$sigla]);

    $idCurso = $conn->lastInsertId();

    for ($i = 1; $i <= $qtd; $i++) {
        $p = $conn->prepare("INSERT INTO periodo (Id_Curso, NumeroPeriodo) VALUES (:c, :n)");
        $p->execute(['c'=>$idCurso, 'n'=>$i]);
    }

    resposta(true, "Curso cadastrado com sucesso!");
}

/* GET (carregar no form de edição) */
if ($op === 'get') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) resposta(false, "ID inválido");

    $st = $conn->prepare("SELECT Id_Curso, Nome_Curso, Sigla FROM curso WHERE Id_Curso=:id");
    $st->execute(['id'=>$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if (!$row) resposta(false, "Curso não encontrado");

    resposta(true, "", [$row]);
}

/* ✅ UPDATE — nome + sigla */
if ($op === 'update') {
    $id   = intval($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $sigla = strtoupper(trim($_POST['sigla'] ?? ''));

    if (!$id || !$nome || !$sigla) resposta(false, "Dados inválidos");

    $st = $conn->prepare("UPDATE curso SET Nome_Curso=:n, Sigla=:s WHERE Id_Curso=:id");
    $st->execute(['n'=>$nome, 's'=>$sigla, 'id'=>$id]);

    resposta(true, "Curso atualizado com sucesso!");
}

resposta(false, "Operação inválida");
