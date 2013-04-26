<?php
include 'Framework.php';
include 'models/Widgettype.php';
$nano=new NanoFramework();
$dbnew = new PDO('mysql:host=localhost;dbname=stat',
    'root',
    'secrets');

$dbnew->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$wigettypes=function(){
    return Widgettype::findAll();
};

$nano->get("/widgettypes",$wigettypes);
$nano->run();