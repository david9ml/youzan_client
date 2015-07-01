<?php
require_once __DIR__ . '/lib/KdtApiClient.php';
require_once __DIR__ . '/const.php';
require_once __DIR__ . '/appid.php';

function get_file_size(){
    $url='http://imgqn.koudaitong.com/upload_files/2015/06/25/143522841332031640.jpg';
    $url='http://imgqn.koudaitong.com/upload_files/2015/06/25/143522837065941491.jpg';
    $contents=file_get_contents($url);
    $save_path=__DIR__ . '/../img/' .  '143522841332031640.jpg';
    file_put_contents($save_path,$contents);
    var_dump(filesize($save_path));
}

function get_exactly_product($sku) {
    $client = new KdtApiClient(APPID, APPSECRET);
    $method = 'kdt.items.custom.get';
    //$method = 'kdt.skus.custom.get';
    $params = [
        'outer_id' => $sku,
   ];
	return $client->get($method, $params);
}

function get_itemcategories(){
    $client = new KdtApiClient(APPID, APPSECRET);
    $method = 'kdt.itemcategories.get';
	var_dump($client->get($method));
}

function get_tags_and_dump(){
    $client = new KdtApiClient(APPID, APPSECRET);
    $method = 'kdt.itemcategories.tags.get';
    $result = $client->get($method);
	//var_dump($result);
    file_put_contents(__DIR__ . '/tags.txt', json_encode($result));
    return True;
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

function download_product_image($brand,$model,$materail,$color){
    $url='http://img.yvogue.hk/pimg/pl/' . json_decode(BRAND_ID_DICT)->{$brand} . '/m' . strtolower($model) . '/m' . strtolower($material). '/c' . strtolower($color) . '.jpg';
    var_dump($url);
    $contents=file_get_contents($url);
    $save_path=__DIR__ . '/../img/' . json_decode(BRAND_ID_DICT)->{$brand} . '_m' . strtolower($model) . '_m' . strtolower($material). '_c' . strtolower($color) . '.jpg';
    file_put_contents($save_path,$contents);
    return $save_path;
}

function create_products(){
    get_tags_and_dump();
    $xml = simplexml_load_file(__DIR__ . '/morning.inventory.hk.xml');
    $tags_str_json = file_get_contents(__DIR__ .  '/tags.txt');
    $tags_array = json_decode($tags_str_json);
    //print_r($xml);
    foreach($xml->children()->children() as $ele){
        $ele_obj = (object)$ele;
        $brand_str = (string)($ele_obj->brand);
        $model_str = (string)($ele_obj->model);
        $material_str = (string)($ele_obj->material);
        $color_str = (string)($ele_obj->color);
        $cate_str = (string)($ele_obj->cate);
        $price_eu_str = (string)number_format(((float)($ele_obj->price_eu)*6.8073), 2, '.', '');
        $name_str = (string)($ele_obj->name);
        $quatity_str = (string)($ele_obj->quatity);
        $code_str = (string)($ele_obj->code);
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
        sleep(3);
    }
}

function test_create_one_product(){
    $param_array = array();
    $param_array['tag_ids'] = '26423415';
    $param_array['price'] = '8293.01';
    $param_array['title'] = 'ABC test';
    //$param_array['quantity'] = '5';
    $param_array['sku_outer_ids'] = '9200000255726';
    $param_array['image'] = __DIR__ . '/../img/' . 'test_image.jpg';
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
        //'quantity' => $param_array['quantity'],
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

function test_update_one_product(){
    $u_param_array = array();
    $u_param_array['sku_properties'] = '尺寸:38,尺寸:40,尺寸:41';
    $u_param_array['sku_quantities'] = '1,3,2';
    $u_param_array['sku_outer_ids'] = '9600000738821,960000073883,960000073884';
    $u_param_array['sku_prices'] = '8293.01,8293.01,8293.01';
    $client = new KdtApiClient(APPID, APPSECRET);
    $method = 'kdt.item.update';
    $params = [
        'num_iid' => '58355236' ,
        'sku_properties' => $u_param_array['sku_properties'],
        'sku_quantities' => $u_param_array['sku_quantities'],
        'sku_outer_ids' => $u_param_array['sku_outer_ids'],
        'sku_prices' => $u_param_array['sku_prices'],
   ];
    $result = $client->get($method, $params);
	var_dump($client->get($method, $params));
}

function test_update_one_product_2(){
    $client = new KdtApiClient(APPID, APPSECRET);
    //$method = 'kdt.item.update';
    $method = 'kdt.item.sku.update';
    $params = [
        'num_iid' => '58355236' ,
        'sku_id' => '35866822' ,
        'quantity' => '1',
   ];
    $result = $client->get($method, $params);
	var_dump($client->get($method, $params));
}


//test_create_one_product();
//var_dump(get_exactly_product('9200000255726'));
//test_update_one_product();
test_update_one_product_2();
//var_dump(get_exactly_product('9200000255726'));
