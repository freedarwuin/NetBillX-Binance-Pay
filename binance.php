<?php
/**
 * PHP Mikrotik Billing
 *
 * Payment Gateway Binance Pay
 *
 * Adaptado desde PayPal por @tu_nombre
 */

function binance_validate_config()
{
    global $config;
    if (empty($config['binance_api_key']) || empty($config['binance_secret_key'])) {
        sendTelegram("Pasarela Binance Pay no configurada");
        r2(U . 'order/package', 'w', "El administrador no ha configurado Binance Pay, por favor notifícale.");
    }
}

function binance_show_config()
{
    global $ui;
    $ui->assign('_title', 'Binance Pay - Pasarela de Pago');
    $ui->assign('currency', ['USDT', 'BUSD', 'BTC', 'ETH']); // Monedas soportadas
    $ui->display('binance.tpl');
}

function binance_save_config()
{
    global $admin;
    $api_key = _post('binance_api_key');
    $secret_key = _post('binance_secret_key');
    $currency = _post('binance_currency');

    $settings = [
        'binance_api_key' => $api_key,
        'binance_secret_key' => $secret_key,
        'binance_currency' => $currency
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
    _log('[' . $admin['username'] . ']: Binance Pay ' . Lang::T('Settings_Saved_Successfully'), 'Admin', $admin['id']);
    r2(U . 'paymentgateway/binance', 's', Lang::T('Settings_Saved_Successfully'));
}

function binance_create_transaction($trx, $user)
{
    global $config;

    $params = [
        'env' => ['terminalType' => 'WEB'],
        'merchantTradeNo' => $trx['id'],
        'orderAmount' => strval($trx['price']),
        'currency' => $config['binance_currency'],
        'returnUrl' => U . "order/view/" . $trx['id'] . '/check',
        'cancelUrl' => U . "order/view/" . $trx['id'],
        'goods' => [
            'goodsType' => '01',
            'goodsCategory' => '0000',
            'referenceGoodsId' => $trx['plan_id'],
            'goodsName' => 'Recarga de Plan'
        ]
    ];

    $url = "https://bpay.binanceapi.com/binancepay/openapi/v2/order";
    $timestamp = round(microtime(true) * 1000);
    $nonce = bin2hex(random_bytes(8));
    $payload = json_encode($params);

    $signature = strtoupper(hash_hmac('SHA512', $timestamp . "\n" . $nonce . "\n" . $payload . "\n", $config['binance_secret_key']));

    $headers = [
        'Content-Type: application/json',
        'BinancePay-Timestamp: ' . $timestamp,
        'BinancePay-Nonce: ' . $nonce,
        'BinancePay-Certificate-SN: ' . $config['binance_api_key'],
        'BinancePay-Signature: ' . $signature
    ];

    $result = json_decode(Http::postJsonData($url, $params, $headers), true);

    if ($result['status'] != 'SUCCESS') {
        sendTelegram("binance_create_transaction FAILED:\n\n" . json_encode($result, JSON_PRETTY_PRINT));
        r2(U . 'order/package', 'e', "Fallo al crear la transacción en Binance Pay.");
    }

    $d = ORM::for_table('tbl_payment_gateway')
        ->where('username', $user['username'])
        ->where('status', 1)
        ->find_one();
    $d->gateway_trx_id = $result['data']['prepayId'];
    $d->pg_url_payment = $result['data']['checkoutUrl'];
    $d->pg_request = json_encode($result);
    $d->expired_date = date('Y-m-d H:i:s', strtotime("+ 6 HOUR"));
    $d->save();

    header('Location: ' . $result['data']['checkoutUrl']);
    exit();
}
