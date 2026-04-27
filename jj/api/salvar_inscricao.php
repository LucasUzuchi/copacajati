<?php
require_once 'db.php';

header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    // Check if CPF already exists
    $checkSql = "SELECT id FROM cop_inscricoes WHERE cpf = :cpf";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':cpf' => $data['cpf']]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este CPF já está inscrito no campeonato.']);
        exit;
    }

    $sql = "INSERT INTO cop_inscricoes (nome, cpf, faixa, equipe, plano, valor, status_pagamento) 
            VALUES (:nome, :cpf, :faixa, :equipe, :plano, :valor, 'pendente')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'   => $data['nome'],
        ':cpf'    => $data['cpf'],
        ':faixa'  => $data['faixa'],
        ':equipe' => $data['equipe'],
        ':plano'  => $data['plano'],
        ':valor'  => $data['valor']
    ]);

    $id = $pdo->lastInsertId();

    // INTEGRAÇÃO MERCADO PAGO (PIX + PREFERENCE PARA CARTÃO)
    require_once 'config.php';
    
    // 1. Dados para o PIX (Direto na página)
    $pix_data = [
        "transaction_amount" => (float)$data['valor'],
        "description" => "Inscrição Copa Jiu-Jitsu: " . $data['nome'],
        "payment_method_id" => "pix",
        "payer" => [
            "email" => "atleta@email.com",
            "first_name" => $data['nome'],
            "identification" => ["type" => "CPF", "number" => $data['cpf']]
        ],
        "notification_url" => BASE_URL . "/api/webhook.php",
        "external_reference" => (string)$id
    ];

    // 2. Dados para o Checkout Pro (Cartão, Boleto, etc)
    $preference_data = [
        "items" => [
            [
                "title" => "Inscrição Copa Jiu-Jitsu - " . $data['nome'],
                "quantity" => 1,
                "unit_price" => (float)$data['valor'],
                "currency_id" => "BRL"
            ]
        ],
        "external_reference" => (string)$id,
        "notification_url" => BASE_URL . "/api/webhook.php",
        "back_urls" => [
            "success" => BASE_URL . "/pagamento.html?status=success",
            "failure" => BASE_URL . "/pagamento.html?status=failure",
            "pending" => BASE_URL . "/pagamento.html?status=pending"
        ],
        "auto_return" => "approved"
    ];

    // Chamada para gerar o PIX
    $ch = curl_init("https://api.mercadopago.com/v1/payments");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pix_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . MP_ACCESS_TOKEN]);
    $pix_res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // Chamada para gerar o Link de Cartão (Preference)
    $ch = curl_init("https://api.mercadopago.com/v2/preferences");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . MP_ACCESS_TOKEN]);
    $pref_res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // Resposta Final
    $response = [
        'success' => true,
        'id' => $id,
        'message' => 'Inscrição salva!'
    ];

    if (isset($pix_res['point_of_interaction']['transaction_data'])) {
        $response['pix_qr_code'] = $pix_res['point_of_interaction']['transaction_data']['qr_code'];
        $response['pix_image'] = $pix_res['point_of_interaction']['transaction_data']['qr_code_base64'];
    }

    if (isset($pref_res['init_point'])) {
        $response['card_link'] = $pref_res['init_point'];
    }

    echo json_encode($response);

} catch (\PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao salvar no banco: ' . $e->getMessage()
    ]);
}
?>
