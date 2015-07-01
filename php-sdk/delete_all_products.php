<?php
require_once __DIR__ . '/lib/KdtApiClient.php';
require_once __DIR__ . '/const.php';
require_once __DIR__ . '/appid.php';


function get_all_online_products() {
    $client = new KdtApiClient(APPID, APPSECRET);
    $method = 'kdt.items.onsale.get';
    $params = [
        'page_size' => '1000',
        //'outer_id' => $sku,
   ];
	return $client->get($method, $params);
}

function delete_product($num_iid){
    $client = new KdtApiClient(APPID, APPSECRET);
    $method = 'kdt.item.delete';
    $params = [
        'num_iid' => $num_iid,
   ];
	return $client->post($method, $params);
}

$all_products = get_all_online_products();
foreach($all_products['response']['items'] as $p){
    $result = delete_product($p['num_iid']);
    var_dump($result);
    print("deleted a product."."\n");
}
