<?php
require_once 'db.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT status_pagamento FROM cop_inscricoes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch();

    if ($result) {
        echo json_encode([
            'success' => true,
            'status' => $result['status_pagamento']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Inscrição não encontrada.']);
    }

} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
}
?>
