<?php
require_once "../conexao.php";
header("Content-Type: application/json; charset=utf-8");

$op = $_POST["op"] ?? "";

/**
 * Retorna períodos de um curso, gerando o Nome_Periodo a partir de NumeroPeriodo.
 * Aceita:
 *  - op = bycurso  com parâmetro  curso
 *  - op = listByCurso  com parâmetro  id_curso   (compatibilidade com JS antigo)
 */
if ($op === "bycurso" || $op === "listByCurso") {
    $curso = $op === "bycurso"
        ? intval($_POST["curso"] ?? 0)
        : intval($_POST["id_curso"] ?? 0);

    if (!$curso) {
        echo json_encode(["ok"=>false,"msg"=>"curso inválido"]); exit;
    }

    $sql = "SELECT Id_Periodo,
                   CONCAT(NumeroPeriodo, 'º Período') AS Nome_Periodo
            FROM periodo
            WHERE Id_Curso = :c
            ORDER BY NumeroPeriodo ASC";

    $st = $conn->prepare($sql);
    $st->execute(["c" => $curso]);

    echo json_encode(["ok"=>true, "rows"=>$st->fetchAll(PDO::FETCH_ASSOC)]); exit;
}

echo json_encode(["ok"=>false, "msg"=>"op inválida"]); exit;
