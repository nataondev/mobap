<?php

include "config.php";
date_default_timezone_set('Asia/Jakarta');

function request($url, $data = null, $headers = null, $patch = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    if ($data) :
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    endif;
    if ($patch) :
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $patch);
    endif;
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($headers) :
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    endif;
    curl_setopt($ch, CURLOPT_HEADER, 0);

    curl_setopt($ch, CURLOPT_ENCODING, "GZIP,DEFLATE");
    return curl_exec($ch);
}


function color($color, $text)
{
    $arrayColor = array(
        'grey' => '1;30',
        'red' => '1;31',
        'green' => '1;32',
        'yellow' => '1;33',
        'blue' => '1;34',
        'purple' => '1;35',
        'nevy' => '1;36',
        'white' => '1;0',
    );
    return "\033[" . $arrayColor[$color] . "m" . $text . "\033[0m";
}

function getStr($string, $start, $end)
{
    $string = " " . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) {
        return "";
    }
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

$headersx = array();
$headersx[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0';
$headersx[] = 'X-Token: ' . $mobaToken . '';
$urlinfo = "https://api.mobapay.com/account/info";
$info = request($urlinfo, null, $headersx);
$namanya = getStr($info, '"name":"', '"');
$emailnya = getStr($info, '"email":"', '"');

echo "Nama Mobapay: ";
echo color("green", "$namanya\n");
echo "Email Mobapay: ";
echo color("green", "$emailnya\n");

$urlcekvoc = "https://api.mobapay.com/account/gift_code_list?country=ID&language=id";
$cekvoc = request($urlcekvoc, null, $headersx);

$hasil = json_decode($cekvoc);
$voucher = $hasil->data->unused;

echo "Total Voucher: ";
$now = date("H:i:s");
echo color("green", count($voucher) . "\n");

if (count($voucher) == 0) {
    echo color("red", "Tidak ada voucher yang tersedia\n");
    exit;
}


echo "Memulai Redeem Voucher\n";
$urlRedeem = "https://api.mobapay.com/account/gift_code_exchange";

foreach ($voucher as $voc) {
    $idVoc = $voc->id;
    $cencorIdVoc = substr($idVoc, 0, 4) . "****" . substr($idVoc, -4);
    $dataRedeem = "{\"user_id\":$mobaGameId,\"server_id\":$mobaServerId,\"id\":\"$idVoc\",\"country\":\"ID\"}";
    $redeem = request($urlRedeem, $dataRedeem, $headersx);
    $redeem = json_decode($redeem);

    if ($redeem->code == 0) {
        echo "Redeem: $cencorIdVoc " . color("green", "{$redeem->message}\n");
    } else {
        echo "Redeem: $cencorIdVoc";
        echo color("red", "{$redeem->message}\n");
    }
    sleep(1);
}
