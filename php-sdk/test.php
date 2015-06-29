<?php
$url='http://imgqn.koudaitong.com/upload_files/2015/06/25/143522841332031640.jpg';
$url='http://imgqn.koudaitong.com/upload_files/2015/06/25/143522837065941491.jpg';
$contents=file_get_contents($url);
$save_path=__DIR__ . '/../img/' .  '143522841332031640.jpg';
file_put_contents($save_path,$contents);
var_dump(filesize($save_path));

