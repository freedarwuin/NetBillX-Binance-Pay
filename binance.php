<?php
/**
 * PHP Mikrotik Billing - NetBillX
 *
 * Binance Pay Payment Gateway
 * Integración oficial Binance Pay OpenAPI v2
 */

/* =========================
   VALIDACIONES GENERALES
========================= */

function binance_validate_config()
{
    global $config;

    if (empty($config['binance_api_key']) || empty($config['binance_secret_key'])) {
        sendTelegram("Binance Pay no está configurado");
        r2(U . 'order/package', 'w', 'Binance Pay no está configurado.');
    }
}

function binance_validate_currency($currency)
{
    $allowed = ['USDT','USDC','BUSD','BNB','BTC','ETH'];
    return in_array($currency, $allowed, true);
}

/* =========================
   CONFIGURACIÓN (ADMIN)
========================= */

function binance_show_config()
{
    global $ui;

    $ui->assign('_title', 'Binance Pay - Pasarela de Pago');

    $ui->assign('currency', [
        'USDT',
        'USDC',
        'BUSD',
        'BNB',
        'BTC',
        'ETH'
    ]);

    $ui->display('binance.tpl');
}

function binance_save_config()
{
    global $admin;

    $settings = [
        'binance_api_key'    => _post('binance_api_key'),
        'binance_secret_key' => _post('binance_secret_key'),
        'binance_currency'   => _post('binance_currency')
    ];

    foreach ($settings as $key => $value) {
        $d = ORM::for_table('tbl_appconfig')->where('setting', $key)->find_one();
        if ($d) {
            $d->value = $value;
            $d->save();
        } else {
            $d = ORM::for_table('tbl_appconfig')->create();
            $d->setting = $key;
            $d->value = $value;
            $d->save();
        }
    }

    _log('[Binance Pay] Configuración guardada', 'Admin', $admin['id']);
    r2(U . 'paymentgateway/binance', 's', Lang::T('Settings_Saved_Successfully'));
}

/* =========================
   CREAR TRANSACCIÓN
========================= */

function binance_create_transaction($trx, $user)
{
    global $config;

    binance_validate_config();

    if (!binance_validate_currency($config['binance_currency'])) {
        sendTelegram('Binance Pay: moneda inválida -> '.$config['binance_currency']);
        r2(U.'order/package','e','Moneda no válida para Binance Pay.');
    }

    $params = [
        'env' => [
            'terminalType' => 'WEB'
        ],
        'merchantTradeNo' => (string)$trx['id'],
        'orderAmount' => number_format($trx['price'], 2, '.', ''),
        'currency' => $config['binance_currency'],
        'returnUrl' => U . "order/view/" . $trx['id'] . "/check",
        'notifyUrl' => U . "paymentgateway/binance/notify",
        'goods' => [
            'goodsType' => '01',
            'goodsCategory' => '0000',
            'referenceGoodsId' => (string)$trx['plan_id'],
            'goodsName' => 'Recarga de Plan'
        ]
    ];

    $url = 'https://bpay.binanceapi.com/binancepay/openapi/v2/order';

    $timestamp = round(microtime(true) * 1000);
    $nonce = bin2hex(random_bytes(8));
    $payload = json_encode($params, JSON_UNESCAPED_SLASHES);

    $signature = strtoupper(
        hash_hmac(
            'SHA512',
            $timestamp . "\n" . $nonce . "\n" . $payload . "\n",
            $config['binance_secret_key']
        )
    );

    $headers = [
        'Content-Type: application/json',
        'BinancePay-Timestamp: ' . $timestamp,
        'BinancePay-Nonce: ' . $nonce,
        'BinancePay-Certificate-SN: ' . $config['binance_api_key'],
        'BinancePay-Signature: ' . $signature
    ];

    $response = json_decode(Http::postJsonData($url, $params, $headers), true);

    if (!isset($response['status']) || $response['status'] !== 'SUCCESS') {
        sendTelegram("Binance Pay ERROR:\n" . json_encode($response, JSON_PRETTY_PRINT));
        r2(U.'order/package','e','Error al crear la orden en Binance Pay.');
    }

    $d = ORM::for_table('tbl_payment_gateway')
        ->where('username', $user['username'])
        ->where('status', 1)
        ->find_one();

    $d->gateway_trx_id = $response['data']['prepayId'];
    $d->pg_url_payment = $response['data']['checkoutUrl'];
    $d->pg_request = json_encode($response);
    $d->expired_date = date('Y-m-d H:i:s', strtotime('+6 HOURS'));
    $d->save();

    header('Location: ' . $response['data']['checkoutUrl']);
    exit();
}
