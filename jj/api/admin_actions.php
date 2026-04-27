<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Faça login novamente.']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
    exit;
}

$action = $data['action'];
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido.']);
    exit;
}

try {
    if ($action === 'pay') {
        $sql = "UPDATE cop_inscricoes SET status_pagamento = 'pago' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        echo json_encode(['success' => true, 'message' => 'Pagamento confirmado.']);
    } 
    elseif ($action === 'delete') {
        $sql = "DELETE FROM cop_inscricoes WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        echo json_encode(['success' => true, 'message' => 'Inscrição excluída.']);
    } 
    elseif ($action === 'edit') {
        $nome = $data['nome'] ?? '';
        $equipe = $data['equipe'] ?? '';
        $faixa = $data['faixa'] ?? '';

        if (empty($nome) || empty($equipe)) {
            echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
            exit;
        }

        $sql = "UPDATE cop_inscricoes SET nome = :nome, equipe = :equipe, faixa = :faixa WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':equipe' => $equipe,
            ':faixa' => $faixa,
            ':id' => $id
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Dados atualizados com sucesso.']);
    } 
    else {
        echo json_encode(['success' => false, 'message' => 'Ação desconhecida.']);
    }

} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
}
?>
