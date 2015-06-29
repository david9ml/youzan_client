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

while(1){
$all_products = get_all_online_products();
foreach($all_products['response']['items'] as $p){
    $url=$p['item_imgs'][0]['url'];
    var_dump($url);
    $contents=file_get_contents($url);
    $save_path=__DIR__ . '/../img/' .  'temp.jpg';
    file_put_contents($save_path,$contents);
    var_dump(filesize($save_path));
    if(filesize($save_path)<50){
        $result = delete_product($p['num_iid']);
        var_dump($result);
        print("deleted a product."."\n");
    }
    unlink($save_path);
}
print("I'm sleeping 60*40s..."."\n");
sleep(60*40);
}
