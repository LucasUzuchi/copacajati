<?php
require_once 'db.php';
require_once 'config.php';

// Recebe a notificação do Mercado Pago
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Verifica se é uma notificação de pagamento
if (isset($data['type']) && $data['type'] == 'payment') {
    $payment_id = $data['data']['id'];

    // Consulta os detalhes do pagamento no Mercado Pago
    $ch = curl_init("https://api.mercadopago.com/v1/payments/" . $payment_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . MP_ACCESS_TOKEN
    ]);
    
    $response = curl_exec($ch);
    $payment = json_decode($response, true);
    curl_close($ch);

    if (isset($payment['status'])) {
        $status = $payment['status'];
        $external_id = $payment['external_reference']; // Este é o ID da nossa tabela cop_inscricoes

        if ($status == 'approved') {
            // Atualiza o status no nosso banco de dados
            $sql = "UPDATE cop_inscricoes SET status_pagamento = 'pago' WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $external_id]);
        }
    }
}

// Responde ao Mercado Pago com 200 OK para confirmar o recebimento
http_response_code(200);
?>
