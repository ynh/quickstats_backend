<?php
include 'Framework.php';
include 'models/Widgettype.php';
include 'models/Dashboard.php';
include 'models/Datasource.php';
$nano=new NanoFramework();
$dbnew = new PDO('mysql:host=localhost;dbname=stat',
    'root',
    'secrets');

$dbnew->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$wigettypes=function(){
    return Widgettype::findAll();
};
$nano->get("/widgettypes",$wigettypes);



$datasources=function(){
    return Datasource::findAll();
};
$nano->get("/datasources",$datasources);



$dashboards=function(){
    return Dashboard::findAll();
};
$nano->get("/dashboards",$dashboards);


$nano->run();