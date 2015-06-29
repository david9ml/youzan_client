<?php
require_once __DIR__ . '/lib/KdtApiClient.php';
require_once __DIR__ . '/const.php';
require_once __DIR__ . '/appid.php';

function get_exactly_product($sku) {
    $client = new KdtApiClient(APPID, APPSECRET);
    $method = 'kdt.items.custom.get';
    $params = [
        'outer_id' => $sku,
   ];

	return $client->get($method, $params);
}

function get_tags_and_dump(){
    $client = new KdtApiClient(APPID, APPSECRET);
    $method = 'kdt.itemcategories.tags.get';
    $result = $client->get($method);
	//var_dump($result);
    file_put_contents(__DIR__ . '/tags.txt', json_encode($result));
    return True;
}

function download_product_image($brand,$model,$materail,$color){
    $url='http://img.yvogue.hk/pimg/pl/' . json_decode(BRAND_ID_DICT)->{$brand} . '/m' . strtolower($model) . '/m' . strtolower($material). '/c' . strtolower($color) . '.jpg';
    var_dump($url);
    $contents=file_get_contents($url);
    $save_path=__DIR__ . '/../img/' . json_decode(BRAND_ID_DICT)->{$brand} . '_m' . strtolower($model) . '_m' . strtolower($material). '_c' . strtolower($color) . '.jpg';
    file_put_contents($save_path,$contents);
    return $save_path;
}

function create_one_product($param_array = array()){
    $client = new KdtApiClient(APPID, APPSECRET);

    $method = 'kdt.item.add';
    $params = [
        //'cid' => '1000000',
        'price' => $param_array['price'],
        'tag_ids' => $param_array['tag_ids'],
        'title' => $param_array['title'],
        'is_virtual' => '0',
        'desc' => '免运费至香港',
        'post_fee' => '0.00',
        'quantity' => $param_array['quantity'],
        'sku_properties' => '',
        'sku_quantities' => '',
        'sku_prices' => '',
        'sku_outer_ids' => $param_array['sku_outer_ids'],
        'outer_id' => $param_array['sku_outer_ids'],
   ];
    $files = [
        [
            'url' => $param_array['image'],
            'field' => 'images[]',
        ],
    ];
     var_dump(
        $client->post($method, $params, $files)
    );
}

function update_qty($num_iid, $qty){
    $client = new KdtApiClient(APPID, APPSECRET);
    $method = 'kdt.item.update';
    $params = [
        'num_iid' => $num_iid,
        'quantity' => $qty,
   ];
    $result = $client->get($method, $params);
	return $client->get($method, $params);
}

function update_products(){
    get_tags_and_dump();
    $tags_str_json = file_get_contents(__DIR__ .  '/tags.txt');
    $tags_array = json_decode($tags_str_json);
    $xml = simplexml_load_file(__DIR__ . '/morning.inventory.hk.xml');
    foreach($xml->children()->children() as $ele){
        $ele_obj = (object)$ele;
        $code_str = (string)($ele_obj->code);
        $the_product = get_exactly_product($code_str);
        if(empty($the_product['response']['items'])){
            print("empty, product not exist". "\n");
            $brand_str = (string)($ele_obj->brand);
            $model_str = (string)($ele_obj->model);
            $material_str = (string)($ele_obj->material);
            $color_str = (string)($ele_obj->color);
            $cate_str = (string)($ele_obj->cate);
            $price_eu_str = (string)number_format(((float)($ele_obj->price_eu)*6.8073), 2, '.', '');
            $name_str = (string)($ele_obj->name);
            $quatity_str = (string)($ele_obj->quatity);
            $param_array = array();
            $saved_path = download_product_image($brand_str,$model_str,$material_str,$color_str);
            foreach(($tags_array->response->tags) as $tag){
                if($tag->name == $cate_str){
                    $param_array['tag_ids'] = (string)$tag->id;
                    //var_dump($tag->id);
                }
            }
            $param_array['price'] = $price_eu_str;
            $param_array['title'] = $name_str;
            $param_array['quantity'] = $quatity_str;
            $param_array['sku_outer_ids'] = $code_str;
            $param_array['image'] = $saved_path;
            var_dump($param_array);
            if(filesize($param_array['image'])>500){
                //if(strpos($param_array['image'], 'verde') == True)
                create_one_product($param_array = $param_array);
            }
        }
        else{
            print("Not empty, product exist.". "\n");
            $quatity_str = (string)($ele_obj->quatity);
            $num_iid = $the_product['response']['items'][0]['num_iid'];
            //var_dump($quatity_str);
            //var_dump($num_iid);
            $update_result = update_qty($num_iid, $quatity_str);
            var_dump($update_result);
        }
    }
}

while(1){
update_products();
print("I'm sleeping 60*60s..."."\n");
sleep(60*60);
}
