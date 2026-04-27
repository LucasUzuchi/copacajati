<?php
// Configurações do Mercado Pago
// Obtenha seu Access Token em: https://www.mercadopago.com.br/developers/panel/credentials
define('MP_ACCESS_TOKEN', 'APP_USR-0000000000000000-000000-00000000000000000000000000000000-000000000'); // <--- COLOQUE SEU TOKEN AQUI

// URL do seu site (necessário para o Webhook e retornos)
// No Vercel, o ideal é configurar a variável APP_URL ou deixar detectar automaticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost/jj';
define('BASE_URL', getenv('APP_URL') ?: "$protocol://$domain"); 
?>
