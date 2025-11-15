<?php
session_start();
include("conexao.php");

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'ok':
            $mensagem = "<p class='mensagem sucesso'>✅ Questão salva com sucesso!</p>";
            break;
        case 'erro':
            $mensagem = "<p class='mensagem erro'>❌ Ocorreu um erro ao salvar a questão.</p>";
            break;
        case 'update':
            $mensagem = "<p class='mensagem sucesso'>✅ Questão atualizada com sucesso!</p>";
            break;
    }
}


// ===== Segurança básica =====
if (!isset($_SESSION["Id_Prof"])) go('erro');
if ($_SERVER["REQUEST_METHOD"] !== "POST") go('erro');

// ===== Coleta segura =====
$Id_Prof    = intval($_SESSION["Id_Prof"]);
$Id_Quest   = intval($_POST["Id_Quest"] ?? 0);
$Id_Materia = intval($_POST["Id_Materia"] ?? 0);
$tipo       = trim($_POST["Tipo_Questao"] ?? '');
$nivel      = trim($_POST["Nivel_Dificuldade"] ?? '');
$enunciado  = trim($_POST["Enunciado"] ?? '');
$qtdLac     = intval($_POST["Qtd_Lacunas"] ?? 0);

if ($Id_Materia<=0 || $tipo==='' || $enunciado==='') go('erro');
if (!in_array($tipo, ['ME','VF','LACUNA','ASSOCIACAO'])) go('erro');

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

try {
    $conn->beginTransaction();

    // ==========================================================
    //  INSERIR ou ATUALIZAR QUESTÃO PRINCIPAL
    // ==========================================================
    if ($Id_Quest > 0) {
        // --- UPDATE ---
        $stmtQ = $conn->prepare("
            UPDATE questao
               SET Enunciado = ?, Tipo_Questao = ?, Nivel_Dificuldade = ?, Qtd_Lacunas = ?, Id_Materia = ?
             WHERE Id_Quest = ? AND Id_Prof = ?
        ");
        $stmtQ->execute([$enunciado, $tipo, $nivel, $qtdLac, $Id_Materia, $Id_Quest, $Id_Prof]);

        // Apaga alternativas antigas
        $del = $conn->prepare("DELETE FROM alternativa_questao WHERE Id_Quest = ?");
        $del->execute([$Id_Quest]);
    } else {
        // --- INSERT ---
        $stmtQ = $conn->prepare("
            INSERT INTO questao (Enunciado, Tipo_Questao, Nivel_Dificuldade, Qtd_Lacunas, Id_Materia, Id_Prof)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtQ->execute([$enunciado, $tipo, $nivel, $qtdLac, $Id_Materia, $Id_Prof]);
        $Id_Quest = $conn->lastInsertId();
    }

    // ==========================================================
    //  INSERIR ALTERNATIVAS CONFORME TIPO
    // ==========================================================
    $stmtAlt = $conn->prepare("
        INSERT INTO alternativa_questao (Id_Quest, Tipo, Grupo, Texto, Correta, Extra)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // ----- Múltipla Escolha -----
    if ($tipo === 'ME') {
        $alts = $_POST["alternativas"] ?? [];
        $idxCor = intval($_POST["me_correta"] ?? 0);
        foreach ($alts as $i=>$txt) {
            $letra = chr(65 + $i);
            $stmtAlt->execute([
                $Id_Quest, $tipo, $letra, trim($txt),
                ($i==$idxCor?1:0), null
            ]);
        }
    }

    // ----- Verdadeiro/Falso -----
    elseif ($tipo === 'VF') {
        $itens = $_POST["vf_itens"] ?? [];
        $verdadeiros = $_POST["vf_true"] ?? [];
        foreach ($itens as $i=>$texto) {
            $ok = isset($verdadeiros[$i]) ? 1 : 0;
            $stmtAlt->execute([
                $Id_Quest, $tipo, 'A'.$i, trim($texto), $ok, null
            ]);
        }
    }

    // ----- Lacunas -----
    elseif ($tipo === 'LACUNA') {
        $respostas = $_POST["lacunas"] ?? [];
        for ($i=0; $i<count($respostas); $i++) {
            $grupo = '[['.($i+1).']]';
            $stmtAlt->execute([
                $Id_Quest, $tipo, $grupo, trim($respostas[$i]), 1, null
            ]);
        }

        // variantes opcionais A–E (JSON)
        $L = [
            'A'=>json_decode($_POST['lac_json_A']??'[]',true),
            'B'=>json_decode($_POST['lac_json_B']??'[]',true),
            'C'=>json_decode($_POST['lac_json_C']??'[]',true),
            'D'=>json_decode($_POST['lac_json_D']??'[]',true),
            'E'=>json_decode($_POST['lac_json_E']??'[]',true),
        ];
        $jsonVar = json_encode($L, JSON_UNESCAPED_UNICODE);
        $stmtAlt->execute([$Id_Quest,$tipo,'VARIANTES','',0,$jsonVar]);
    }

    // ----- Associação -----
    elseif ($tipo === 'ASSOCIACAO') {
        $A = $_POST["assocA"] ?? [];
        $B = $_POST["assocB"] ?? [];
        for ($i=0; $i<count($A); $i++) {
            $a = trim($A[$i] ?? '');
            $b = trim($B[$i] ?? '');
            if ($a!=='' && $b!=='') {
                $json = json_encode(['A'=>$a,'B'=>$b], JSON_UNESCAPED_UNICODE);
                $stmtAlt->execute([$Id_Quest,$tipo,'par_'.$i,$a.' ↔ '.$b,1,$json]);
            }
        }
    }

    $conn->commit();

// Após salvar ou atualizar, vai direto pra tela de inserção limpa com mensagem
header("Location: inserir_questao.php?msg=ok");
exit;

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo "<pre>Erro: ".$e->getMessage()."</pre>";
    go('erro');
}
?>
