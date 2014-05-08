<?php
$return_url = SITE_URL . '/payment/index/success-payment';
$cancel_url = SITE_URL . '/payment/index/cancel-payment';

$time_limit = date('d/m/Y, h:m:s', (time() + 24*60*60)); // 1 day
$receiver = "tvhoang1980@yahoo.com";// email of saler

return $gatewayConfigs = array(
    'nganluong' => array(
        'gateway' => 'NganLuongEc', //express checkout payment => 'NganLuongEc'
        'credentials' => array(
            'merchant_site_code'    => 22282, //sandbox => '370',
            'secure_pass'           => 'anhhoanganh123',
        ),
        'gateway_config' => array(
            'return_url'            => $return_url . "?gateway=nganluong",
            'cancel_url'            => $cancel_url . "?gateway=nganluong",
            'receiver'              => $receiver, //email of saler
            'request_confirm_shipping'        => 0,
            'language' => 'vn',
            'time_limit' => $time_limit,//'22/03/2012, 03:06:07', //TODO: token alive
            'currency' => 'vnd'
        )
    ),
    'baokim' => array(
        'gateway' => 'BaoKim',
        'credentials' => array(
            'merchant_id'    => '3302',
            'secure_pass'    => '0ea9e28b78111243'
        ),
        'gateway_config' => array(
            'url_success' => $return_url . "?gateway=baokim",
            'url_cancel' => $cancel_url . "?gateway=baokim",
            'url_detail' => '',
            'business'   => $receiver, //email of saller
            'currency' => 'vnd'
        )
    ),
    'vtc' => array(
        'gateway' => 'Vtc',
        'credentials' => array(
            'websiteid' => 238,
            'merchant_privateKey' => APPLICATION_PATH . '/configs/eekip/merchant_privateKey.pem',// get from merchant_privateKey.pem file
        ),
        'gateway_config' => array(
            'receiver'   => '01266294087', //receiver account
            'loginaccount' => '',// ebank account
            'patner_url' => $return_url . "?gateway=vtc",
            'currency' => 'vnd'
        ),
    ),
    'paypal' => array(
        'gateway' => 'PaypalWppEc',
        'credentials' => array(
            'USER'      => 'nvanh1_1331639235_biz_api1.gmail.com',
            'PWD'       => '1331639261',
            'SIGNATURE' => 'A8TC6N2wJ6FBb2-z-ArMrTScu8q0A.i0MpZOB0K9KqbYQU.jZnj.EePz'
        ),
        'gateway_config' => array(
            'return_url' => $return_url . "?gateway=paypal",
            'cancel_url' => $cancel_url . "?gateway=paypal",
            'currency'   => 'USD'
        )
    ),
);
