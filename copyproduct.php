<?php

$ID_deal_rs = $_GET['IDrs'];
$ID_deal_tc = $_GET['IDtc'];

function executeREST($method, $params) {

    $queryUrl = 'https://sibus.bitrix24.ru/rest/167/dg8reko1691lrw80/'.$method.'.json';
    $queryData = http_build_query($params);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));

    $result = curl_exec($curl);
    curl_close($curl);

    return json_decode($result, true);
}


$product_get = executeREST('crm.deal.productrows.get', array("id" => $ID_deal_rs));
foreach ($product_get["result"] as $value)
{
    $product_list[] = [
        "PRODUCT_ID" => $value["PRODUCT_ID"],
        "PRODUCT_NAME" => $value["PRODUCT_NAME"],
        "PRICE" => $value["PRICE"],
        "QUANTITY" => $value["QUANTITY"]
    ];
}
$product_setter = executeREST('crm.deal.productrows.set', array("id" => $ID_deal_tc, "rows" => $product_list));