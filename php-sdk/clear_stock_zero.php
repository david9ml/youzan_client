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
        if($p['outer_id']==""){
            print("no outer_id sku, skip."."\n");
        }
        else{
            print("found outer_id sku,try to  clear stock zero product..."."\n");
            $xml = simplexml_load_file(__DIR__ . '/morning.inventory.hk.xml');
            $found = False;
            foreach($xml->children()->children() as $ele){
                $ele_obj = (object)$ele;
                $code_str = (string)($ele_obj->code);
                if($code_str==$p['outer_id']){
                    $found = True;
                    break;
                }
            }
            if($found==False){
                print("stock zero, delete product."."\n");
                $result = delete_product($p['num_iid']);
                var_dump($result);
                print("deleted a product."."\n");
            }
            else{
                print("stock not zero, skip."."\n");
            }
        }
    }

print("I'm sleeping 60*30s..."."\n");
sleep(60*30);
}
